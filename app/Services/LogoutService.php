<?php
namespace App\Services;

class LogoutService
{
    public function logoutUser(Request $request)
    {
        $user=$request->user();
        $user->tokens()->delete();
        return $this->successResponse(null,'logged out successfully.',200);
    }
}