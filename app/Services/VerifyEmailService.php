<?php
namespace App\Services;
use App\Traits\ApiTrait;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;

class VerifyEmailService
{
    use ApiTrait;
    public function verifyEmailUser(VerifyEmailRequest $request)
    {
        $request->validated();

        $user=User::where('email',$request->email)
                   ->where('verification_code',$request->code)
                   ->where('verification_code_expires_at','>',now())
                   ->first();
            if($user)
            {
                $user->email_verified=true;
                $user->verification_code=null;
                $user->verification_code_expires_at=null;
                $user->save();
                return $this->SuccessResponse($user,'Email verified successfully',200);
            }
            return $this->ErrorResponse('Invalid or expired verification code',400);
    }

}

