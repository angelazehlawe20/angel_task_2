<?php
namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorMail;
use App\Traits\ApiTrait;
use App\Models\User;
use Illuminate\Http\Request;



class Resend2FAcodeService
{
    use ApiTrait;
    public function resend2FAcodeUser(Request $request)
    {
        $email = $request->input('email');

        // التحقق من صحة البريد الإلكتروني
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->ErrorResponse('Invalid email address.', 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->ErrorResponse('User not found.', 404);
        }

        if ($this->hasActive2FACode($user)) {
            return $this->ErrorResponse('A 2FA code is already active. Please check your email.', 400);
        }

        $this->generateAndSend2FACode($user);

        return $this->SuccessResponse(null, 'A 2FA code was resent successfully. Please verify.', 200);
    }

    private function hasActive2FACode($user)
    {
        return $user->two_factor_expires_at && $user->two_factor_expires_at->isFuture();
    }

    private function generateAndSend2FACode($user)
    {
        $google2fa = new Google2FA();
        $newCode = $google2fa->generateSecretKey();

        $user->update([
            'two_factor_code' => $newCode,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new TwoFactorMail($newCode));
    }
}
