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

class LoginService
{
    use ApiTrait;

    public function loginUser(LoginRequest $request)
    {
        $validatedData = $request->validated();

        $user = $this->findUserByIdentifier($validatedData['identifier']);

        if (!$user || !$this->isValidPassword($validatedData['password'], $user->password)) {
            return $this->ErrorResponse('Invalid credentials. Please check your email or password and try again.', 401);
        }

        $newToken = $user->createToken('myapptoken')->plainTextToken;
        LoginEvent::dispatch($user);

        $this->handleTwoFactorAuthentication($user);

        return $this->SuccessResponse($newToken, 'A 2FA code has been sent to your email. Please verify.', 200);
    }

    private function findUserByIdentifier($identifier)
    {
        return User::where('email', $identifier)
                    ->orWhere('phone_number', $identifier)
                    ->first();
    }

    private function isValidPassword($inputPassword, $storedPassword)
    {
        return Hash::check($inputPassword, $storedPassword);
    }

    private function handleTwoFactorAuthentication(User $user)
    {
        $google2fa = new Google2FA();
        $code = $google2fa->generateSecretKey();

        $user->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new TwoFactorMail($code));
    }
}
