<?php
namespace App\Services;
use App\Traits\ApiTrait;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;

class ConfirmEmail_VFCodeService
{
    use ApiTrait;

    public function confirmEmail_VFCodeUser(VerifyEmailRequest $request)
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
                $token=$user->createToken('myapptoken')->plainTextToken;
                return $this->SuccessResponse($token,'Email verified successfully',200);
            }
            return $this->ErrorResponse('Invalid or expired verification code',400);
    }

}

