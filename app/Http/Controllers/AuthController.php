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
use App\Services\LoginService;
use App\Services\VerifyEmailService;
use App\Services\VerifyTwoFactorService;
use App\Services\RefreshTokenService;


class AuthController extends Controller
{
    use ApiTrait;
    use FileUploadTrait;

    protected $registerService;
    protected $loginService;
    protected $verifyEmailService;
    protected $verifyTwoFactorService;


    public function __construct(
        RegisterService $registerService,
        VerifyEmailService $verifyEmailService,
        LoginService $loginService,
        VerifyTwoFactorService $verifyTwoFactorService,
        RefreshTokenService $refreshTokenService
        )
    {
        $this->registerService=$registerService;
        $this->verifyEmailService=$verifyEmailService;
        $this->loginService=$loginService;
        $this->verifyTwoFactorService=$verifyTwoFactorService;
        $this->refreshTokenService=$refreshTokenService;

    }

    public function register(RegisterRequest $request)
    {
        return $this->registerService->registerUser($request);
    }

    public function verifyemail(VerifyEmailRequest $request)
    {
        return $this->verifyEmailService->verifyEmailUser($request);
    }

    public function login(LoginRequest $request)
    {
        return $this->loginService->loginUser($request);
    }


    public function verifyTwoFactor(VerifyTwoFactorRequest $request)
    {
        return $this->verifyTwoFactorService->verifyTwoFactorUser($request);
    }



    public function refreshToken(Request $request)
    {
        return $this->refreshTokenService->refreshTokenUser($request);
    }


    public function logout(Request $request)
    {
        $user=$request->user();
            $user->tokens()->delete();
            return $this->successResponse(null,'logged out successfully.',200);
    }

}
