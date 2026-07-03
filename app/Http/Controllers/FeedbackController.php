<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    /**
     * Landing sahifadan feedback yuborish (AJAX)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'required|string|min:5|max:1000',
            'name' => 'nullable|string|max:100',
        ], [
            'rating.required' => 'Yulduz baholang',
            'rating.min' => 'Kamida 1 yulduz',
            'rating.max' => 'Maksimum 5 yulduz',
            'text.required' => 'Fikringizni yozing',
            'text.min' => 'Fikringiz kamida 5 belgi bo\'lsin',
            'text.max' => 'Fikringiz juda uzun',
        ]);

        $user = auth()->user();

        $feedback = Feedback::create([
            'user_id' => $user?->id,
            'telegram_id' => $user?->telegram_id,
            'name' => $user?->name ?? strip_tags($validated['name'] ?? 'Anonim'),
            'rating' => $validated['rating'],
            'text' => strip_tags($validated['text']),
            'is_published' => 1,
            'source' => 'web',
        ]);

        // Admin'ga Telegram orqali notif
        $this->notifyAdmin($feedback);

        return response()->json([
            'ok' => true,
            'message' => 'Rahmat! Fikringiz uchun katta minnatdorchilik.',
        ]);
    }

    /**
     * Barcha feedback'lar sahifasi (agar kerak bo'lsa)
     */
    public function index()
    {
        $feedbacks = Feedback::where('is_published', 1)
            ->whereNotNull('text')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('landing.feedbacks', compact('feedbacks'));
    }

    /**
     * Admin'ga yangi feedback haqida Telegram xabar
     */
    protected function notifyAdmin(Feedback $feedback): void
    {
        try {
            $adminChatId = env('TELEGRAM_ADMIN_CHAT_ID');
            $botToken = env('TELEGRAM_BOT_TOKEN');

            if (!$adminChatId || !$botToken) return;

            $stars = str_repeat('⭐', $feedback->rating);
            $name = htmlspecialchars($feedback->display_name, ENT_QUOTES);
            $text = htmlspecialchars($feedback->text, ENT_QUOTES);

            $message = "💬 <b>Yangi feedback</b>\n\n";
            $message .= "<b>Kimdan:</b> {$name}\n";
            $message .= "<b>Baho:</b> {$stars} ({$feedback->rating}/5)\n";
            $message .= "<b>Manba:</b> {$feedback->source}\n\n";
            $message .= "<b>Fikr:</b>\n{$text}";

            Http::timeout(3)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $adminChatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
            );
        } catch (\Exception $e) {
            Log::warning("Admin feedback notify failed: " . $e->getMessage());
        }
    }
}