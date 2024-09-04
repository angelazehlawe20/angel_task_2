<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\VerifyTwoFactorRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\SignupService;
use App\Services\LoginService;
use App\Services\ConfirmEmail_VFCodeService;
use App\Services\Confirm2FACodeService;
use App\Services\RefreshTokenService;
use App\Services\Re_sendVFCodeService;
use App\Services\LogoutService;
use App\Services\Resend2FAcodeService;


class AuthController extends Controller
{
    protected $signupService;
    protected $loginService;
    protected $confirmEmail_VFCodeService;
    protected $confirm2FACodeService;
    protected $logoutService;
    protected $resend2FAcodeService;
    protected $refreshTokenService;
    protected $resendVFCodeService;


    public function __construct(
        SignupService $signupService,
        ConfirmEmail_VFCodeService $confirmEmail_VFCodeService,
        LoginService $loginService,
        Confirm2FACodeService $confirm2FACodeService,
        RefreshTokenService $refreshTokenService,
        LogoutService $logoutService,
        Re_sendVFCodeService $resendVFCodeService,
        Resend2FAcodeService $resend2FAcodeService
        )
    {
        $this->signupService=$signupService;
        $this->confirmEmail_VFCodeService=$confirmEmail_VFCodeService;
        $this->loginService=$loginService;
        $this->confirm2FACodeService=$confirm2FACodeService;
        $this->refreshTokenService=$refreshTokenService;
        $this->logoutService=$logoutService;
        $this->resendVFCodeService=$resendVFCodeService;
        $this->resend2FAcodeService=$resend2FAcodeService;

    }

    public function signup(RegisterRequest $request)
    {
        return $this->signupService->signupUser($request);
    }

    public function confirmEmail_VFCode(VerifyEmailRequest $request)
    {
        return $this->confirmEmail_VFCodeService->confirmEmail_VFCodeUser($request);
    }

    public function resendVFCode(Request $request)
    {
        return $this->resendVFCodeService->resendVFCodeUser($request);
    }

    public function login(LoginRequest $request)
    {
        return $this->loginService->loginUser($request);
    }

    public function resend2FAcode(Request $request)
    {
        return $this->resend2FAcodeService->Resend2FAcodeUser($request);
    }

    public function Confirm2FACode(VerifyTwoFactorRequest $request)
    {
        return $this->confirm2FACodeService->Confirm2FACodeUser($request);
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
