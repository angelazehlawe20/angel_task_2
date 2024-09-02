<?php

namespace App\Http\Controllers;

use App\Events\LoginEvent;
use App\Events\RegisterEvent;
use App\Mail\TwoFactorMail;
use App\Mail\VerificationMail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\VerifyTwoFactorRequest;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiTrait;
use App\Traits\FileUploadTrait;

class AuthController extends Controller
{
    use ApiTrait;
    use FileUploadTrait;

    public function register(RegisterRequest $request)
    {
        $validation=$request->validated();

        $profilePhotoPath=$this->uploadFile($request,'profile_photo','profile_photos','public');
        $certificatePath=$this->uploadFile($request,'certificate','certificates','public');

        //الوصول الى الصورةالمرفوعة
        $photo_url=$this->fileUrl($profilePhotoPath);

            $user= User::create([
                'email'=>$validation['email'],
                'phone_number'=>$validation['phone_number'],
                'username'=>$validation['username'],
                'profile_photo'=>$profilePhotoPath,
                'certificate'=>$certificatePath,
                'password'=>bcrypt($validation['password']),
                'verification_code'=>Str::random(6),
                'verification_code_expires_at'=>now()->addMinutes(10),
            ]);
            Mail::to($user->email)->send(new VerificationMail($user));

            RegisterEvent::dispatch($user);

            return $this->SuccessResponse($user,'user registered.Please check your email for verification.',201);

    }

    public function verifyemail(VerifyEmailRequest $request)
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



    public function login(LoginRequest $request)
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


    public function verifyTwoFactor(VerifyTwoFactorRequest $request)
    {

    $request->validated();

    $user = User::where('email', $request->email)
                ->first();

    if (!$user || $user->two_factor_code !== $request->code || $user->two_factor_expires_at->lt(now())) {
        return $this->ErrorResponse('Invalid or expired 2FA code',401);
    }

    $user->update([
        'two_factor_code' => null,
        'two_factor_expires_at' => null,
    ]);

    $token = $user->createToken('myapptoken')->plainTextToken;
    $refreshToken = Str::random(64);

    LoginEvent::dispatch($user);

    return $this->SuccessResponse($token,'',200);
    /*response()->json([
        'email' => $user->email,
        'token' => $token,
        'refresh_token' => $refreshToken,
        'expires_in' => now()->addMinutes(10),
    ], 200);*/

    }



    public function refreshToken(Request $request)
    {
    $request->user()->currentAccessToken()->delete();

    $newAccessToken = $user->createToken('auth_token')->plainTextToken;

    return $this->successResponse($newAccessToken,'expires_in 10 minutes',200);
    }


    public function logout(Request $request)
    {
        $user=$request->user();
            $user->tokens()->delete();
            return $this->successResponse(null,'logged out successfully.',200);
    }

    }
