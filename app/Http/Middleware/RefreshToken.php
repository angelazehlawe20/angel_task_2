<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $response = $next($request);
        $user = Auth::user();

        if ($user) {
            $tokenStartedAt = $user->currentAccessToken()->created_at;

            if ($tokenStartedAt->diffInMinutes(now()) >= 20) {
                $user->tokens()->delete();

                $newToken = $user->createToken('myapptoken')->plainTextToken;

                $response->headers->set('Authorization', 'Bearer ' . $newToken);
            }
        }

        return $response;
    }
}
