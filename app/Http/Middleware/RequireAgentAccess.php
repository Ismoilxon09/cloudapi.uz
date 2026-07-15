<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Agent platformasi (Agentlar, Vantage) — hisobda minimal balans talab qilinadi.
 * Yetarli bo'lmasa "qulflangan" sahifa ko'rsatiladi (to'ldirish taklifi bilan).
 */
class RequireAgentAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->hasAgentAccess()) {
            return response()->view('dashboard.agents.locked', [
                'min'     => \App\Models\User::agentAccessMin(),
                'balance' => (float) ($user->wallet->balance_uzs ?? 0),
            ]);
        }

        return $next($request);
    }
}
