<?php

namespace App\Http\Controllers;

use App\Events\LoginEvent;
use App\Mail\TwoFactorMail;
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
use App\Traits\ApiTrait;
use App\Traits\FileUploadTrait;
use App\Services\RegisterService;

class AuthController extends Controller
{
    use ApiTrait;
    use FileUploadTrait;

    protected $registerService;
    protected $loginService;
    public function __construct(RegisterService $registerService,LoginService $loginService)
    {
        $this->registerService=$registerService;
        $this->loginService=$loginService;
    }

    public function register(RegisterRequest $request)
    {
        return $this->registerService->registerUser($request);
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
        return $this->loginService->loginUser($request);
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
