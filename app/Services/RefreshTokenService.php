<?php
namespace App\Services;

use Illuminate\Http\Request;
use App\Traits\ApiTrait;

class RefreshTokenService
{
    use ApiTrait;

    public function refreshTokenUser(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->ErrorResponse('User not authenticated.', 401);
        }

        $this->revokeCurrentToken($user);
        $newAccessToken = $this->generateNewToken($user);

        return $this->SuccessResponse($newAccessToken, 'Token refreshed successfully. Expires in 10 minutes.', 200);
    }

    private function revokeCurrentToken($user)
    {
        $user->currentAccessToken()->delete();
    }

    private function generateNewToken($user)
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
