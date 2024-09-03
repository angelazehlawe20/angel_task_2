<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\VerifyTwoFactorRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\SignupService;
use App\Services\LoginService;
use App\Services\VerifyEmailService;
use App\Services\VerifyTwoFactorService;
use App\Services\RefreshTokenService;
use App\Services\Re_sendVFCodeService;
use App\Services\LogoutService;



class AuthController extends Controller
{
    protected $signupService;
    protected $loginService;
    protected $verifyEmailService;
    protected $verifyTwoFactorService;
    protected $logoutService;
    protected $resendVerifiedCodeService;


    public function __construct(
        SignupService $signupService,
        VerifyEmailService $verifyEmailService,
        LoginService $loginService,
        VerifyTwoFactorService $verifyTwoFactorService,
        RefreshTokenService $refreshTokenService,
        LogoutService $logoutService,
        Re_sendVFCodeService $resendVFCodeService
        )
    {
        $this->signupService=$signupService;
        $this->verifyEmailService=$verifyEmailService;
        $this->loginService=$loginService;
        $this->verifyTwoFactorService=$verifyTwoFactorService;
        $this->refreshTokenService=$refreshTokenService;
        $this->logoutService=$logoutService;
        $this->resendVFCodeService=$resendVFCodeService;

    }

    public function signup(RegisterRequest $request)
    {
        return $this->signupService->signupUser($request);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        return $this->verifyEmailService->verifyEmailUser($request);
    }

    public function resendVFCode(Request $request)
    {
        return $this->resendVFCodeService->resendVFCodeUser($request);
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
        return $this->logoutService->logoutUser($request);
    }

}
