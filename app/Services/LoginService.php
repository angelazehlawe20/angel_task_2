<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorMail;
use App\Traits\ApiTrait;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Events\LoginEvent;
use Illuminate\Support\Facades\Cache;

class LoginService
{
    use ApiTrait;

    private $cacheTTL = 600;

    public function loginUser(LoginRequest $request)
    {
        $validatedData = $request->validated();
        $user = $this->getUserByIdentifier($validatedData['identifier']);

        if (!$user || !$this->isValidPassword($validatedData['password'], $user->password)) {
            return $this->ErrorResponse('Invalid credentials. Please check your email or password and try again.', 401);
        }

        $ipAddress = $request->ip();
        $cacheKey = $this->getCacheKey($ipAddress);

        if ($this->isRateLimited($cacheKey)) {
            return $this->ErrorResponse('Please wait before requesting another 2FA code.', 429);
        }

        $newToken = $this->createUserToken($user);
        $this->dispatchLoginEvent($user);
        $this->sendTwoFactorCode($user);

        Cache::put($cacheKey, true, $this->cacheTTL);

        return $this->SuccessResponse($newToken, 'A 2FA code has been sent to your email. Please verify.', 200);
    }

    private function getUserByIdentifier($identifier)
    {
        return User::where('email', $identifier)
                    ->orWhere('phone_number', $identifier)
                    ->first();
    }

    private function isValidPassword($inputPassword, $storedPassword)
    {
        return Hash::check($inputPassword, $storedPassword);
    }

    private function getCacheKey($ipAddress)
    {
        return 'login_2fa_' . $ipAddress;
    }

    private function isRateLimited($cacheKey)
    {
        return Cache::has($cacheKey);
    }

    private function createUserToken($user)
    {
        return $user->createToken('myapptoken')->plainTextToken;
    }

    private function dispatchLoginEvent($user)
    {
        LoginEvent::dispatch($user);
    }

    private function sendTwoFactorCode($user)
    {
        $google2fa = new Google2FA();
        $code = $google2fa->generateSecretKey();

        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new TwoFactorMail($code));
    }
}
