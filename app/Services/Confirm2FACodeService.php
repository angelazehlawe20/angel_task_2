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
        $request->validated();

        $user = User::where('email', $request->email)
                ->first();

        if (!$user || $user->two_factor_code !== $request->code || $user->two_factor_expires_at->lt(now())) 
        {
            return $this->ErrorResponse('Invalid or expired 2FA code',401);
        }

        $user->update([
        'two_factor_code' => null,
        'two_factor_expires_at' => null,
        ]);
        $oldToken=$request->user();
        $oldToken->currentAccessToken()->delete();
        $token = $user->createToken('myapptoken')->plainTextToken;
        LoginEvent::dispatch($user);
        return $this->SuccessResponse($token,'expires_in 10 minutes',200);
    }
}