<?php

namespace App\Http\Controllers\Bot\Commands;

use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\Telegram\BotNotifier;
use App\Services\Telegram\TelegramService;

class TasksCommand
{
    public function __construct(protected TelegramService $tg) {}

    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];

        $user = User::where('telegram_id', $telegramId)->first();
        if (!$user) {
            $this->tg->sendMessage($chatId, "❌ /start ni bosing.");
            return;
        }

        $tasks = Task::where('active', 1)->orderBy('sort_order')->orderBy('id')->get();
        $completed = TaskCompletion::where('user_id', $user->id)->pluck('task_id')->toArray();

        $available = $tasks->filter(fn($t) => !in_array($t->id, $completed));

        if ($available->isEmpty()) {
            $this->tg->sendMessage($chatId,
                "✅ <b>Barcha vazifalar bajarilgan!</b>\n\n" .
                "Yaqin kunlarda yangi vazifalar qo'shiladi. Kuzatib turing 👀");
            return;
        }

        $text = "✅ <b>Aktiv vazifalar</b>\n\n";
        $text .= "Quyidagi kanallarga obuna bo'ling va GP toping:\n\n";

        $keyboard = [];
        foreach ($available as $task) {
            $text .= "📌 <b>{$task->channel_name}</b>\n";
            $text .= "    🎁 Mukofot: <b>+{$task->reward_gp} GP</b>\n\n";

            $keyboard[] = [
                ['text' => "📢 {$task->channel_name}", 'url' => $task->channel_url],
            ];
            $keyboard[] = [
                ['text' => "✅ Tekshirish (+{$task->reward_gp} GP)", 'callback_data' => "task_check:{$task->id}"],
            ];
        }

        $text .= "<i>💡 Kanalga obuna bo'lib, keyin \"Tekshirish\" tugmasini bosing.</i>";

        $this->tg->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }

    /**
     * Obuna tekshirish (callback)
     */
    public function handleCheck(array $callback, ?string $taskId): void
    {
        if (!$taskId) return;

        $chatId = $callback['message']['chat']['id'];
        $telegramId = $callback['from']['id'];
        $messageId = $callback['message']['message_id'];

        $user = User::where('telegram_id', $telegramId)->first();
        if (!$user) return;

        $task = Task::find($taskId);
        if (!$task || !$task->active) {
            $this->tg->sendMessage($chatId, "❌ Vazifa topilmadi yoki yopilgan.");
            return;
        }

        // Avval bajarganmi?
        $alreadyDone = TaskCompletion::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->exists();

        if ($alreadyDone) {
            $this->tg->sendMessage($chatId, "✅ Bu vazifani allaqachon bajargansiz.");
            return;
        }

        // Telegram'dan tekshirish
        $isSubscribed = $this->tg->isUserSubscribed($task->channel_id, $telegramId);

        if (!$isSubscribed) {
            $this->tg->sendMessage($chatId,
                "❌ Hali obuna bo'lmagansiz!\n\n" .
                "Kanalga obuna bo'ling: {$task->channel_url}\n\n" .
                "Keyin yana \"Tekshirish\" tugmasini bosing.");
            return;
        }

        // Vazifa bajardi — GP berish
        TaskCompletion::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'reward_gp' => $task->reward_gp,
        ]);

        if ($user->wallet) {
            $user->wallet->increment('bonus_balance_uzs', $task->reward_gp);
            $user->wallet->increment('total_bonus_earned', $task->reward_gp);
        }

        // Notification (yangi balans bilan)
        app(BotNotifier::class)->notifyTaskCompleted($user->fresh(), $task->channel_name, $task->reward_gp);
    }
}