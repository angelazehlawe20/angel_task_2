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
        $email=$request->input('email');
        $user=User::where('email',$email)->first();
        if (!$user) 
        {
            return $this->errorResponse('User not found.', 404);
        }
        if($user->email_verified)
        {
            return $this->errorResponse('Email already verified.',400);
        }
        $newCode=Str::random(6);
        $user->verification_code=$newCode;
        $user->verification_code_expires_at=now()->addMinutes(10);
        $user->save();
        Mail::to($user->email)->send(new VerificationMail($user));
        return $this->successResponse($user,'Verification code resent successfully.',200);
    }

}
