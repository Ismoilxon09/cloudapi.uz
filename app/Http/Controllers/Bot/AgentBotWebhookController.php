<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Models\AgentChannel;
use App\Models\AgentConversation;
use App\Services\Agent\AgentRunner;
use App\Services\Agent\AgentTelegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Foydalanuvchi agentlarining Telegram botlari uchun webhook.
 * Har agent bot o'z URL'iga (secret path) so'rov yuboradi.
 */
class AgentBotWebhookController extends Controller
{
    public function __construct(protected AgentRunner $runner) {}

    public function handle(Request $request, string $secret)
    {
        // Kanalni webhook secret bo'yicha topish
        $channel = AgentChannel::where('webhook_secret', $secret)
            ->where('type', 'telegram')
            ->first();

        if (!$channel) {
            return response('OK'); // noma'lum — jim
        }

        // Qo'shimcha himoya: Telegram secret_token header (agar bor bo'lsa)
        $header = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if ($header && $header !== $secret) {
            return response('OK');
        }

        try {
            $this->process($channel, $request->all());
        } catch (\Throwable $e) {
            Log::error('Agent webhook error', [
                'channel_id' => $channel->id,
                'message'    => $e->getMessage(),
                'line'       => $e->getLine(),
            ]);
        }

        return response('OK');
    }

    protected function process(AgentChannel $channel, array $update): void
    {
        $message = $update['message'] ?? null;
        if (!$message) return;

        $chatId = $message['chat']['id'] ?? null;
        $text   = trim($message['text'] ?? '');
        if (!$chatId || $text === '') return;

        $token = $channel->getTelegramToken();
        if (!$token) return;
        $tg = new AgentTelegram($token);

        $agent = $channel->agent;

        // Agent yoki kanal faol emas
        if (!$agent || $agent->status !== 'active' || !$channel->isActive()) {
            if (str_starts_with($text, '/start')) {
                $tg->sendMessage($chatId, "Bu agent hozircha faol emas.");
            }
            return;
        }

        $from = $message['from'] ?? [];

        // /start — salom
        if ($text === '/start' || str_starts_with($text, '/start ')) {
            $greeting = $agent->greeting ?: "Salom! Men {$agent->name}. Savolingizni yozing.";
            $tg->sendMessage($chatId, $greeting);
            // suhbatni oldindan yaratib qo'yamiz (meta bilan)
            $this->conversation($channel, $chatId, $from);
            return;
        }

        $conversation = $this->conversation($channel, $chatId, $from);

        $tg->sendChatAction($chatId, 'typing');

        $result = $this->runner->reply($agent, $conversation, $text);

        if ($result['success'] ?? false) {
            $tg->sendMessage($chatId, $result['content']);
            return;
        }

        // Xatoliklarni oxirgi foydalanuvchiga umumiy ko'rinishda
        $error = $result['error'] ?? 'error';
        $reply = match ($error) {
            'insufficient_balance', 'daily_cap_reached' => 'Kechirasiz, agent hozircha javob bera olmaydi. Birozdan so\'ng urinib ko\'ring.',
            'empty_message' => 'Iltimos, savolingizni matn ko\'rinishida yozing.',
            default => 'Kechirasiz, javob berishda xatolik yuz berdi. Birozdan so\'ng urinib ko\'ring.',
        };
        $tg->sendMessage($chatId, $reply);
    }

    /** Chat bo'yicha suhbatni topish/yaratish (+ meta). */
    protected function conversation(AgentChannel $channel, int|string $chatId, array $from): AgentConversation
    {
        $conv = AgentConversation::firstOrNew([
            'agent_id'         => $channel->agent_id,
            'channel_type'     => 'telegram',
            'external_chat_id' => (string) $chatId,
        ]);

        if (!$conv->exists) {
            $conv->channel_id       = $channel->id;
            $conv->external_user_id = isset($from['id']) ? (string) $from['id'] : null;
            $conv->title            = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')) ?: ($from['username'] ?? null);
            $conv->meta             = [
                'first_name' => $from['first_name'] ?? null,
                'username'   => $from['username'] ?? null,
            ];
            $conv->save();
        }

        return $conv;
    }
}
