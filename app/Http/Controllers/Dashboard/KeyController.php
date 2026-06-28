<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProxyKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeyController extends Controller
{
    /**
     * Barcha kalitlarim
     */
    public function index()
    {
        $keys = Auth::user()->proxyKeys()
            ->withCount('usage')
            ->latest()
            ->get();

        return view('dashboard.keys.index', compact('keys'));
    }

    /**
     * Yangi kalit yaratish — OpenRouter usuli (alohida balansiz)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:1000',
            'allowed_models' => 'nullable|array',
        ]);

        $user = Auth::user();

        // Foydalanuvchi nechta key yaratganini tekshirish (limit: 10)
        $existingCount = $user->proxyKeys()->whereNull('deleted_at')->count();
        if ($existingCount >= 10) {
            return back()->withErrors([
                'name' => __('keys.errors.max_reached', ['max' => 10])
            ]);
        }

        // Kalit yaratish — alohida balansiz, hammasi wallet'dan yechiladi
        $key = ProxyKey::generate($user->id, $validated['name'], 0);

        $key->update([
            'rate_limit_per_minute' => $validated['rate_limit_per_minute'] ?? 60,
            'allowed_models' => $validated['allowed_models'] ?? null,
        ]);

        return redirect()->route('keys.index')->with([
            'success' => __('keys.created_success'),
            'new_key' => $key->full_key,
        ]);
    }

    /**
     * Kalitni ko'rish — batafsil statistika
     */
    public function show(ProxyKey $key)
    {
        if ($key->user_id !== Auth::id()) {
            abort(403);
        }

        $stats = [
            'total_requests' => $key->usage()->count(),
            'today_requests' => $key->usage()->whereDate('created_at', today())->count(),
            'total_spent' => $key->usage()->sum('cost_uzs'),
            'avg_latency' => (int)$key->usage()->avg('latency_ms'),
        ];

        $recentUsage = $key->usage()->latest()->limit(20)->get();

        return view('dashboard.keys.show', compact('key', 'stats', 'recentUsage'));
    }

    /**
     * Kalitni bekor qilish
     */
    public function revoke(ProxyKey $key)
    {
        if ($key->user_id !== Auth::id()) {
            abort(403);
        }

        $key->update(['status' => 'revoked']);

        return back()->with('success', __('keys.revoked_success'));
    }

    /**
     * Kalitni o'chirish
     */
    public function destroy(ProxyKey $key)
    {
        if ($key->user_id !== Auth::id()) {
            abort(403);
        }

        $key->delete(); // soft delete

        return back()->with('success', __('keys.deleted_success'));
    }
}