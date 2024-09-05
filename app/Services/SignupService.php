<?php

namespace App\Services;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiTrait;
use App\Traits\FileUploadTrait;
use App\Events\RegisterEvent;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SignupService
{
    use FileUploadTrait, ApiTrait;

    private $profilePhotoDirectory = 'profile_photos';
    private $certificateDirectory = 'certificates';
    private $fileUploadVisibility = 'public';
    private $verificationCodeLength = 6;
    private $verificationCodeExpiryMinutes = 10;
    private $cacheTTL = 600;
    private $cachePrefix = 'register_user_';

    public function signupUser(RegisterRequest $request)
    {
        $validation = $request->validated();

        $ipAddress = $request->ip();
        $cacheKey = $this->getCacheKey($ipAddress);

        if ($this->isRateLimited($cacheKey)) {
            return $this->ErrorResponse('Too many registration requests. Please try again later.', 429);
        }

        $profilePhotoPath = $this->uploadFile($request, 'profile_photo', $this->profilePhotoDirectory, $this->fileUploadVisibility);
        $certificatePath = $this->uploadFile($request, 'certificate', $this->certificateDirectory, $this->fileUploadVisibility);

        if (!$profilePhotoPath || !$certificatePath) {
            return $this->ErrorResponse('Failed to upload files. Please try again.', 500);
        }

        $user = $this->createUser($validation, $profilePhotoPath, $certificatePath);

        RegisterEvent::dispatch($user);

        $this->sendVerificationEmail($user);

        $this->storeRequestInCache($cacheKey);

        return $this->SuccessResponse($user, 'User registered successfully. Please check your email for verification.', 201);
    }

    private function createUser(array $data, string $profilePhotoPath, string $certificatePath): User
    {
        return User::create([
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'username' => $data['username'],
            'profile_photo' => $profilePhotoPath,
            'certificate' => $certificatePath,
            'password' => Hash::make($data['password']),
            'verification_code' => Str::random($this->verificationCodeLength),
            'verification_code_expires_at' => now()->addMinutes($this->verificationCodeExpiryMinutes),
        ]);
    }

    private function sendVerificationEmail(User $user)
    {
        Mail::to($user->email)->send(new VerificationMail($user));
    }

    private function isRateLimited(string $cacheKey): bool
    {
        return Cache::has($cacheKey);
    }

    private function storeRequestInCache(string $cacheKey): void
    {
        Cache::put($cacheKey, true, $this->cacheTTL);
    }

    private function getCacheKey(string $ipAddress): string
    {
        return $this->cachePrefix . $ipAddress;
    }
}
