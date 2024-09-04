<?php
namespace App\Services;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiTrait;
use App\Traits\FileUploadTrait;
use App\Events\RegisterEvent;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;



class SignupService
{
    use FileUploadTrait;
    use ApiTrait;

    public function signupUser(RegisterRequest $request)
    {
        $validation = $request->validated();

        $profilePhotoPath = $this->handleFileUpload($request, 'profile_photo', 'profile_photos');
        $certificatePath = $this->handleFileUpload($request, 'certificate', 'certificates');

        if (!$profilePhotoPath || !$certificatePath) {
            return $this->ErrorResponse('Failed to upload files. Please try again.', 500);
        }

        $user = User::create([
            'email' => $validation['email'],
            'phone_number' => $validation['phone_number'],
            'username' => $validation['username'],
            'profile_photo' => $profilePhotoPath,
            'certificate' => $certificatePath,
            'password' => Hash::make($validation['password']),
            'verification_code' => Str::random(6),
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        if (!$this->sendVerificationEmail($user)) {
            return $this->ErrorResponse('Failed to send verification email. Please try again.', 500);
        }

        RegisterEvent::dispatch($user);

        return $this->SuccessResponse($user, 'User registered successfully. Please check your email for verification.', 201);
    }

    private function handleFileUpload($request, $fieldName, $directory)
    {
        if ($request->hasFile($fieldName)) {
            return $this->uploadFile($request, $fieldName, $directory, 'public');
        }

        return null;
    }

    private function sendVerificationEmail($user)
    {

        Mail::to($user->email)->send(new VerificationMail($user));
    }
}
