<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    protected array $available = ['en', 'uz', 'ru'];

    public function handle(Request $request, Closure $next)
    {
        // Priority: query param > session > cookie > default
        $locale = $request->get('lang')
            ?? session('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale', 'en');

        if (!in_array($locale, $this->available)) {
            $locale = 'en';
        }

        App::setLocale($locale);
        session(['locale' => $locale]);

        $response = $next($request);

        if ($request->has('lang')) {
            $response->headers->setCookie(cookie('locale', $locale, 60 * 24 * 365));
        }

        return $response;
    }
}