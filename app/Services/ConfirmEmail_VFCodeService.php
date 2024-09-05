<?php
namespace App\Services;
use App\Traits\ApiTrait;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;
use App\Events\LoginEvent;


class ConfirmEmail_VFCodeService
{
    use ApiTrait;

    public function confirmEmail_VFCodeUser(VerifyEmailRequest $request)
    {
        $request->validated();

        $user = $this->getUserForVerification($request->email, $request->code);

        if (!$user) {
            return $this->ErrorResponse('Invalid or expired verification code', 400);
        }

        $this->markEmailAsVerified($user);

        $token = $user->createToken('myapptoken')->plainTextToken;
        LoginEvent::dispatch($user);

        return $this->SuccessResponse($token, 'Email verified successfully', 200);
    }

    private function getUserForVerification($email, $code)
    {
        return User::where('email', $email)
            ->where(function ($query) use ($code) {
                $query->where('verification_code', $code)
                      ->where('verification_code_expires_at', '>', now());
            })
            ->first();
    }

    private function markEmailAsVerified($user)
    {
        $user->update([
            'email_verified' => true,
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);
    }

}

