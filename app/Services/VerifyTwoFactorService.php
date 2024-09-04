<?php
namespace App\Services;

use App\Traits\ApiTrait;
use App\Http\Requests\VerifyTwoFactorRequest;
use App\Models\User;
use Illuminate\Support\Str;
use App\Events\LoginEvent;


class VerifyTwoFactorService
{
    use ApiTrait;

    public function verifyTwoFactorUser(VerifyTwoFactorRequest $request)
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

        $token = $user->createToken('myapptoken')->plainTextToken;

        LoginEvent::dispatch($user);

        return $this->SuccessResponse($token,'expires_in 10 minutes',200);

    }
}