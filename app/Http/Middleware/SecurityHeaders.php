<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clickjacking himoyasi
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-sniffing himoyasi
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS himoyasi (eski browserlar uchun)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS (faqat production HTTPS uchun)
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // CSP (Content Security Policy) — XSS asosiy himoyasi
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://api.openrouter.ai https://openrouter.ai",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ];

        // CSP'ni faqat HTML response uchun qo'shamiz (API JSON uchun emas)
        if (str_starts_with($response->headers->get('Content-Type', ''), 'text/html')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        // API response uchun cache yo'q
        if ($request->is('api/*') || $request->is('v1/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        // Server signature yashirish
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}