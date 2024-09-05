<?php

namespace App\Services;

use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Traits\ApiTrait;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Re_sendVFCodeService
{
    use ApiTrait;

    private $cacheTTL = 600;
    private $cachePrefix = 'resend_verification_code_';

    public function resendVFCodeUser(Request $request)
    {
        $email = $request->input('email');
        $ipAddress = $request->ip();
        $cacheKey = $this->getCacheKey($ipAddress);



        $user = $this->getUserByEmail($email);
        if (!$user) {
            return $this->ErrorResponse('User not found.', 404);
        }

        if ($user->email_verified) {
            return $this->ErrorResponse('Email already verified.', 400);
        }
        if ($this->isRateLimited($cacheKey)) {
            return $this->ErrorResponse('Please wait before requesting another verification code.', 429);
        }

        $this->generateAndSendVerificationCode($user);
        $this->storeRequestInCache($cacheKey);

        return $this->SuccessResponse($user, 'Verification code resent successfully.', 200);
    }

    private function getCacheKey($ipAddress): string
    {
        return $this->cachePrefix . $ipAddress;
    }

    private function isRateLimited($cacheKey): bool
    {
        return Cache::has($cacheKey);
    }

    private function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    private function generateAndSendVerificationCode($user): void
    {
        $newCode = Str::random(6);
        $user->verification_code = $newCode;
        $user->verification_code_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new VerificationMail($user));
    }

    private function storeRequestInCache($cacheKey): void
    {
        Cache::put($cacheKey, true, $this->cacheTTL);
    }
}
