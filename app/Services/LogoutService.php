<?php
namespace App\Services;

use App\Traits\ApiTrait;
use Illuminate\Http\Request;


class LogoutService
{
    use ApiTrait;
    public function logoutUser(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $this->revokeTokens($user);
            return $this->SuccessResponse(null, 'Logged out successfully.', 200);
        }

        return $this->ErrorResponse('User not authenticated.', 401);
    }

    private function revokeTokens($user)
    {
        $user->tokens()->delete();
    }
}
