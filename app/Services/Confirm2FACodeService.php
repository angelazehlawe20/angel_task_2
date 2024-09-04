<?php
namespace App\Services;
use App\Http\Requests\VerifyTwoFactorRequest;
use App\Models\User;
use App\Traits\ApiTrait;
use App\Events\LoginEvent;

class Confirm2FACodeService
{
    use ApiTrait;

    public function Confirm2FACodeUser(VerifyTwoFactorRequest $request)
    {
        $user = $this->getUserByEmail($request->email);

        if ($this->isInvalid2FACode($user, $request->code)) {
            return $this->ErrorResponse('Invalid or expired 2FA code', 401);
        }

        $this->clear2FACode($user);

        $token = $this->regenerateToken($user);

        LoginEvent::dispatch($user);

        return $this->SuccessResponse($token, 'Token expires in 10 minutes', 200);
    }

    private function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    private function isInvalid2FACode($user, $code)
    {
        return !$user || $user->two_factor_code !== $code || $this->is2FACodeExpired($user->two_factor_expires_at);
    }

    private function is2FACodeExpired($expiresAt)
    {
        return $expiresAt->lt(now());
    }

    private function clear2FACode($user)
    {
        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);
    }

    private function regenerateToken($user)
    {
        $user->currentAccessToken()->delete();
        return $user->createToken('myapptoken')->plainTextToken;
    }

}
