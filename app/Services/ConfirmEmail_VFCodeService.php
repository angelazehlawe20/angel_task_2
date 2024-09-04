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

        $user = User::where('email', $request->email)
                ->where(function ($query) use ($request) {
                    $query->where('verification_code', $request->code)
                          ->where('verification_code_expires_at', '>', now());
                })
                ->orWhere(function ($query) use ($request) {
                    $query->where('email', $request->email)
                          ->whereNull('verification_code')
                          ->whereNull('verification_code_expires_at');
                })
                ->first();

                if (!$user) 
                {
                    return $this->ErrorResponse('Invalid or expired verification code', 400);
                }

                if ($user->email_verified)
                {
                    return $this->ErrorResponse('Email is already verified.', 400);
                }

                $user->email_verified=true;
                $user->verification_code=null;
                $user->verification_code_expires_at=null;
                $user->save();
                $token=$user->createToken('myapptoken')->plainTextToken;
                LoginEvent::dispatch($user);
                return $this->SuccessResponse($token,'Email verified successfully',200);
    }

}

