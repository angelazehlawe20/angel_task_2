<?php
namespace App\Services;

use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Traits\ApiTrait;
use Illuminate\Support\Str;
use Illuminate\Http\Request;



class Re_sendVFCodeService
{
    use ApiTrait;

    public function resendVFCodeUser(Request $request)
    {
        $email = $request->input('email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->errorResponse('Invalid email address.', 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($user->email_verified) {
            return $this->errorResponse('Email already verified.', 400);
        }

        $this->generateAndSendVerificationCode($user);

        return $this->successResponse(null, 'Verification code resent successfully.', 200);
    }

    private function generateAndSendVerificationCode($user)
    {
        $newCode = Str::random(6);

        $user->update([
            'verification_code' => $newCode,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new VerificationMail($user));
    }

}
