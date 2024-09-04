<?php
namespace App\Services;

use App\Traits\ApiTrait;
use Illuminate\Http\Request;


class LogoutService
{
    use ApiTrait;
    public function logoutUser(Request $request)
    {
        $user=$request->user();
        $user->tokens()->delete();
        return $this->successResponse(null,'logged out successfully.',200);
    }
}
