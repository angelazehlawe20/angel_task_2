<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorMail;
use App\Traits\ApiTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Resend2FAcodeService
{
    use ApiTrait;

    private $cacheTTL = 600;

    public function resend2FAcodeUser(Request $request)
    {
        $email = $request->input('email');
        $ipAddress = $request->ip();
        $cacheKey = $this->getCacheKey($ipAddress);


        $user = $this->getUserByEmail($email);
        if (!$user) {
            return $this->ErrorResponse('User not found.', 404);
        }

        if ($this->isRateLimited($cacheKey)) {
            return $this->ErrorResponse('Please wait before requesting another 2FA code.', 429);
        }

        $newCode = $this->generate2FACode();
        $this->updateUser2FA($user, $newCode);
        $this->send2FACodeByEmail($user, $newCode);

        Cache::put($cacheKey, true, $this->cacheTTL);

        return $this->SuccessResponse($user, '2FA code resent successfully. Please verify.', 200);
    }

    private function getCacheKey($ipAddress): string
    {
        return 'resend_2fa_code_' . $ipAddress;
    }

    private function isRateLimited(string $cacheKey): bool
    {
        return Cache::has($cacheKey);
    }

    private function getUserByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    private function generate2FACode(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    private function updateUser2FA($user, string $newCode): void
    {
        $user->two_factor_code = $newCode;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();
    }

    private function send2FACodeByEmail($user, string $newCode): void
    {
        Mail::to($user->email)->send(new TwoFactorMail($newCode));
    }
}
