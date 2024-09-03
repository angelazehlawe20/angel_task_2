<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorMail;
use App\Traits\ApiTrait;
use App\Http\Requests\LoginRequest;
use App\Models\User;



class LoginService
{
    use ApiTrait;

    public function loginUser(LoginRequest $request)
    {
        $validation=$request->validated();

        $user=User::where('email',$validation['identifier'])
                ->orWhere('phone_number',$validation['identifier'])
                ->first();

        if(!$user||!Hash::check($validation['password'],$user->password)){
            return $this->ErrorResponse('Invalid credentials. Please check your email or password and try again.',401);
        }
        $google2fa = new Google2FA();
        $code = $google2fa->generateSecretKey();

        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new TwoFactorMail($code));
        return $this->SuccessResponse($user,'A 2FA code has been sent to your email. Please verify.',200);
    }
}