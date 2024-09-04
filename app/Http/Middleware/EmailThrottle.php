<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use App\Traits\ApiTrait;



class EmailThrottle
{
    use ApiTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();
        $cacheKey = 'email_requests_' . $ipAddress;

        $maxRequests = 5;
        $decayMinutes = 10;

        if (Cache::has($cacheKey)) {
            $requestCount = Cache::get($cacheKey);

            if ($requestCount >= $maxRequests) {
                return $this->ErrorResponse('A large number of orders from this address. Please try again later.', 429);
            }
        } else {
            Cache::put($cacheKey, 0, $decayMinutes * 60);
        }

        Cache::increment($cacheKey);

        return $next($request);
    }
}
