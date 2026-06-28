<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Faqat admin va super_admin role'lariga ruxsat
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'Admin access required');
        }

        return $next($request);
    }
}