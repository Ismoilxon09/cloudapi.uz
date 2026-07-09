<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $rating = $request->get('rating');
        $search = $request->get('q');

        $feedbacks = Feedback::query()
            ->with('user')
            ->when($rating, fn($q) => $q->where('rating', $rating))
            ->when($search, fn($q) => $q->where(function ($qq) use ($search) {
                $qq->where('text', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(30);

        $stats = [
            'total' => Feedback::count(),
            'avg_rating' => round(Feedback::avg('rating') ?? 0, 1),
            'published' => Feedback::where('is_published', 1)->count(),
            'featured' => Feedback::where('is_featured', 1)->count(),
            'unanswered' => Feedback::whereNull('admin_reply')->count(),
        ];

        return view('admin.feedbacks.index', compact('feedbacks', 'stats', 'rating', 'search'));
    }

    public function show(Feedback $feedback)
    {
        return view('admin.feedbacks.show', compact('feedback'));
    }

    public function reply(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'reply' => 'required|string|min:3|max:1000',
        ]);

        $feedback->update([
            'admin_reply' => $validated['reply'],
            'replied_at' => now(),
        ]);

        if ($feedback->telegram_id) {
            $this->notifyUser($feedback);
        }

        return back()->with('success', 'Javob saqlandi va user xabardor qilindi.');
    }

    public function togglePublish(Feedback $feedback)
    {
        $feedback->update(['is_published' => !$feedback->is_published]);
        return back()->with('success', 'Publish holati o\'zgartirildi.');
    }

    public function toggleFeature(Feedback $feedback)
    {
        $feedback->update(['is_featured' => !$feedback->is_featured]);
        return back()->with('success', 'Featured holati o\'zgartirildi.');
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return redirect()->route('admin.feedbacks.index')->with('success', 'O\'chirildi.');
    }

    protected function notifyUser(Feedback $feedback): void
    {
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            if (!$botToken || !$feedback->telegram_id) return;

            $reply = htmlspecialchars($feedback->admin_reply, ENT_QUOTES);

            $text = "💬 <b>Feedback'ingizga javob keldi</b>\n\n{$reply}";

            Http::timeout(5)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $feedback->telegram_id,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ]
            );
        } catch (\Exception $e) {
            Log::warning("Feedback user notify failed: " . $e->getMessage());
        }
    }
}