<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RefreshToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $expiresIn = now()->diffInMinutes($user->currentAccessToken()->created_at->addMinutes(10));

        if ($expiresIn <= 20) {
            $user->currentAccessToken()->delete();

            $newAccessToken = $user->createToken('auth_token')->plainTextToken;

            Log::info('Token for user ID: ' . $user->id . ' was refreshed.');

            $response = $next($request);
            return $response->header('Authorization', 'Bearer ' . $newAccessToken);
        }

        return $next($request);
    }
}
