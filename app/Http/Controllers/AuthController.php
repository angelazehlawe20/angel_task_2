<?php

namespace App\Http\Controllers;

use App\Events\LoginEvent;
use App\Events\RegisterEvent;
use App\Mail\TwoFactorMail;
use App\Mail\VerificationMail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validation=$request->validated();

        $profilePhotoPath=$request->file('profile_photo')->store('profile_photo','public');
        $certificatePath=$request->file('certificate')->store('certificate','public');

        //الوصول الى الملف المرفوع
        $photo_url=Storage::url($profilePhotoPath);

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

            $token= $user->createToken('myapptoken')->plainTextToken;

            RegisterEvent::dispatch($user);

            return response()->json([
                'message'=>'user registered.Please check your email for verification.',
                'user'=>$user,
                'photo_url'=>$photo_url,
                'token'=>$token],201);

    }



    public function verifyemail(VerifyEmailRequest $request)
    {
        $request->validate([
            'email'=>'required|email',
            'code'=>'required|string'
        ]);
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
                return response()->json(['message'=>'Email verified successfully'],200);

            }
            return response()->json(['message'=>'Invalid or expired verification code'],400);
    }



    public function login(LoginRequest $request)
    {
        $validation=$request->validated();

        $user=User::where('email',$validation['identifier'])
                ->orWhere('phone_number',$validation['identifier'])
                ->first();

        if(!$user||!Hash::check($validation['password'],$user->password)){
            return response()->json(['message'=>'Invalid credentials. Please check your email or password and try again.'],401);
        }
        $google2fa = new Google2FA();
        $code = $google2fa->generateSecretKey();

        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new TwoFactorMail($code));
        return response()->json([
            'message' => 'A 2FA code has been sent to your email. Please verify.'],
            200
        );

    }


    public function verifyTwoFactor(VerifyTwoFactorRequest $request)
    {

    $request->validate([
        'email' => 'required|string',
        'code' => 'required|string',
    ]);

    $user = User::where('email', $request->email)
                ->first();

    if (!$user || $user->two_factor_code !== $request->code || $user->two_factor_expires_at->lt(now())) {
        return response()->json(['message' => 'Invalid or expired 2FA code'], 401);
    }

    $user->update([
        'two_factor_code' => null,
        'two_factor_expires_at' => null,
    ]);

    $token = $user->createToken('myapptoken')->plainTextToken;
    $refreshToken = Str::random(64);
    $user->update([
        'refresh_token' => $refreshToken,
        'refresh_token_expires_at' => now()->addMinutes(20),
    ]);

    LoginEvent::dispatch($user);

    return response()->json([
        'email' => $user->email,
        'token' => $token,
        'refresh_token' => $refreshToken,
        'expires_in' => now()->addMinutes(10),
    ], 200);

    }



    public function refreshToken(RefreshTokenRequest $request)
    {
    $request->validate([
        'refresh_token' => 'required|string',
    ]);

    $user = User::where('refresh_token', $request->refresh_token)
                 ->where('refresh_token_expires_at', '>', now())
                 ->first();

    if (!$user)
    {
        return response()->json(['message' => 'Invalid or expired refresh token'], 401);
    }

    $request->user()->currentAccessToken()->delete();

    $newAccessToken = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'access_token' => $newAccessToken,
        'expires_in' => '600 sec'
    ], 200);
    }


    public function logout(Request $request)
    {
        $user=$request->user();
        if($user){
            $user->update([
                'refresh_token'=>null,
                'refresh_token_expires_at'=>null
            ]);
            $user->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully.'], 200);
        }

    }






}
