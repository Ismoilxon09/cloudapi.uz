<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitProxy
{
    public function handle(Request $request, Closure $next): Response
    {
        $proxyKey = $request->attributes->get('proxy_key');

        if (!$proxyKey) {
            return $next($request);
        }

        $limit = $proxyKey->rate_limit_per_minute ?? 60;

        // 1. Per-key rate limit
        $keyId = 'rl:key:' . $proxyKey->id;
        if (RateLimiter::tooManyAttempts($keyId, $limit)) {
            $retryAfter = RateLimiter::availableIn($keyId);
            return $this->limitResponse('Rate limit exceeded for this API key', $limit, 0, $retryAfter);
        }
        RateLimiter::hit($keyId, 60);

        // 2. Per-IP rate limit (kuchliroq — DDoS protection)
        $ipKey = 'rl:ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 300)) { // 300/min per IP
            $retryAfter = RateLimiter::availableIn($ipKey);
            return $this->limitResponse('Too many requests from this IP', 300, 0, $retryAfter);
        }
        RateLimiter::hit($ipKey, 60);

        // 3. Per-user rate limit (har minutiga 500)
        $userKey = 'rl:user:' . $proxyKey->user_id;
        if (RateLimiter::tooManyAttempts($userKey, 500)) {
            $retryAfter = RateLimiter::availableIn($userKey);
            return $this->limitResponse('User rate limit exceeded', 500, 0, $retryAfter);
        }
        RateLimiter::hit($userKey, 60);

        $response = $next($request);

        // Headers qo'shish (OpenAI/OpenRouter dek)
        $remaining = max(0, $limit - RateLimiter::attempts($keyId));
        $response->headers->set('X-RateLimit-Limit', (string)$limit);
        $response->headers->set('X-RateLimit-Remaining', (string)$remaining);
        $response->headers->set('X-RateLimit-Reset', (string)(time() + RateLimiter::availableIn($keyId)));

        return $response;
    }

    protected function limitResponse(string $message, int $limit, int $remaining, int $retryAfter): Response
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'type' => 'rate_limit_error',
                'code' => 'rate_limit_exceeded',
            ],
        ], 429)->withHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => time() + $retryAfter,
            'Retry-After' => $retryAfter,
        ]);
    }
}