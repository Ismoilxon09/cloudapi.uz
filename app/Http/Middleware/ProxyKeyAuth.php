<?php

namespace App\Http\Middleware;

use App\Models\ProxyKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ProxyKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // === 1. KEY OLISH ===
        $authHeader = $request->header('Authorization', '');
        $key = null;

        if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            $key = trim($matches[1]);
        }

        if (!$key) {
            return $this->error('Missing API key. Use "Authorization: Bearer cap-..." header', 'authentication_required', 401);
        }

        // === 2. FORMAT TEKSHIRUVI (oldinroq reject — DB ga bormaslik) ===
        if (!preg_match('/^cap-[A-Za-z0-9]{32,64}$/', $key)) {
            $this->logSuspicious($request, 'invalid_key_format', substr($key, 0, 8) . '...');
            return $this->error('Invalid API key format', 'invalid_api_key', 401);
        }

        // === 3. IP-BASED BRUTE-FORCE PROTECTION ===
        $ipKey = 'invalid_key_attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 20)) {
            $this->logSuspicious($request, 'brute_force_blocked', $request->ip());
            return $this->error('Too many invalid attempts. IP temporarily blocked.', 'ip_blocked', 429);
        }

        // === 4. KEY-NI TOPISH (hash bilan, plain text NEVER) ===
        $keyHash = hash('sha256', $key);

        // Cache da bor-yo'qligini tekshirish (5 daqiqa cache)
        $cacheKey = 'proxy_key:' . substr($keyHash, 0, 16);
        $proxyKey = Cache::remember($cacheKey, 300, function () use ($keyHash) {
            return ProxyKey::where('key_hash', $keyHash)
                ->with('user.wallet')
                ->first();
        });

        if (!$proxyKey) {
            RateLimiter::hit($ipKey, 600);
            $this->logSuspicious($request, 'invalid_key', substr($key, 0, 8) . '...');
            return $this->error('Invalid API key', 'invalid_api_key', 401);
        }

        // === 5. KEY STATUS ===
        if ($proxyKey->status !== 'active') {
            return $this->error("API key is {$proxyKey->status}", 'key_not_active', 401);
        }

        // === 6. USER STATUS ===
        if (!$proxyKey->user) {
            Cache::forget($cacheKey);
            return $this->error('User not found', 'user_not_found', 401);
        }

        if ($proxyKey->user->status === 'blocked') {
            return $this->error('Account is blocked. Contact support.', 'account_blocked', 403);
        }

        // === 7. EXPIRY ===
        if ($proxyKey->expires_at && $proxyKey->expires_at->isPast()) {
            return $this->error('API key has expired', 'key_expired', 401);
        }

        // === 8. CONSTANT-TIME VERIFICATION (timing attack himoyasi) ===
        if (!hash_equals($proxyKey->key_hash, $keyHash)) {
            return $this->error('Invalid API key', 'invalid_api_key', 401);
        }

        // Hammasi joyida
        $request->attributes->set('proxy_key', $proxyKey);
        $request->attributes->set('proxy_user', $proxyKey->user);

        return $next($request);
    }

    protected function error(string $message, string $code, int $status): Response
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'type' => 'authentication_error',
                'code' => $code,
            ],
        ], $status);
    }

    protected function logSuspicious(Request $request, string $type, string $extra = ''): void
    {
        Log::channel('security')->warning("[{$type}] IP: {$request->ip()} | UA: " . substr($request->userAgent() ?? '', 0, 200) . " | Extra: {$extra}");
    }
}