<?php

namespace App\Http\Middleware;

use App\Services\FraudDetectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    public function __construct(private FraudDetectionService $fraud) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // Local IP — o'tkazib yuborish
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return $next($request);
        }

        // Bloklangan?
        if ($this->fraud->isIpBlocked($ip)) {
            $block = \App\Models\BlockedIp::where('ip_address', $ip)->first();
            
            // Agar sahifa "blocked" o'zi bo'lsa — loop bo'lmasin
            if ($request->is('blocked') || $request->is('blocked/*')) {
                return $next($request);
            }

            return response()->view('errors.blocked', [
                'ip' => $ip,
                'reason' => $block->reason ?? 'Shubhali harakat aniqlandi',
                'remaining' => $block->remainingTime() ?? null,
                'is_permanent' => $block->is_permanent ?? false,
            ], 403);
        }

        return $next($request);
    }
}