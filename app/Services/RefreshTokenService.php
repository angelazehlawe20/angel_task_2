<?php
namespace App\Services;

use Illuminate\Http\Request;
use App\Traits\ApiTrait;

class RefreshTokenService
{
    use ApiTrait;

    public function refreshTokenUser(Request $request)
    {
        $user=$request->user();
        $user->currentAccessToken()->delete();
        $newAccessToken = $user->createToken('auth_token')->plainTextToken;
        return $this->SuccessResponse($newAccessToken,'expires_in 10 minutes',200);
    }
}
