<?php
namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorMail;
use App\Traits\ApiTrait;
use App\Models\User;
use Illuminate\Http\Request;



class Resend2FAcodeService
{
    use ApiTrait;

    public function Resend2FAcodeUser(Request $request)
    {
        $email=$request->input('email');
        $user=User::where('email',$email)->first();
        $newGoogle2fa = new Google2FA();
        $newcode = $newGoogle2fa->generateSecretKey();

        $user->two_factor_code = $newcode;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new TwoFactorMail($newcode));
        return $this->SuccessResponse($user,'A 2FA code resend successfully. Please verify.',200);
    }
}