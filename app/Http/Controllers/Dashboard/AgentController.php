<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentChannel;
use App\Models\AgentConversation;
use App\Models\AgentMcpServer;
use App\Models\AiModel;
use App\Services\Agent\AgentRunner;
use App\Services\Agent\AgentTelegram;
use App\Services\Agent\Mcp\McpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    /** Agentlar ro'yxati. */
    public function index()
    {
        $agents = Auth::user()->agents()
            ->with('telegramChannel')
            ->latest()
            ->get();

        return view('dashboard.agents.index', compact('agents'));
    }

    /** Yangi agent formasi. */
    public function create()
    {
        $agent = new Agent([
            'model_mode'   => 'single',
            'model_slug'   => 'gpt-4o-mini',
            'temperature'  => 0.7,
            'memory_limit' => 20,
            'behavior_preset' => 'general',
            'status'       => 'draft',
        ]);

        return view('dashboard.agents.edit', [
            'agent'  => $agent,
            'models' => $this->modelOptions(),
            'isNew'  => true,
        ]);
    }

    /** Yangi agentni saqlash. */
    public function store(Request $request)
    {
        $data = $this->validateAgent($request);

        $user = Auth::user();
        if ($user->agents()->count() >= 20) {
            return back()->withInput()->withErrors(['name' => 'Maksimal agentlar soniga yetdingiz (20).']);
        }

        $data['user_id'] = $user->id;
        $data['slug']    = Agent::generateSlug($data['name']);
        $data['status']  = 'draft';

        $agent = Agent::create($data);

        return redirect()->route('agents.edit', $agent)
            ->with('success', 'Agent yaratildi. Endi uni Telegramga ulang.');
    }

    /** Tahrirlash / builder. */
    public function edit(Agent $agent)
    {
        $this->authorizeAgent($agent);

        return view('dashboard.agents.edit', [
            'agent'  => $agent->load('telegramChannel', 'apiChannel', 'webChannel', 'mcpServers'),
            'models' => $this->modelOptions(),
            'isNew'  => false,
        ]);
    }

    /** Agentni yangilash. */
    public function update(Request $request, Agent $agent)
    {
        $this->authorizeAgent($agent);
        $data = $this->validateAgent($request);
        $agent->update($data);

        return back()->with('success', 'Saqlandi.');
    }

    /** O'chirish. */
    public function destroy(Agent $agent)
    {
        $this->authorizeAgent($agent);

        // Telegram webhookni tozalash
        if ($ch = $agent->telegramChannel) {
            if ($token = $ch->getTelegramToken()) {
                try { (new AgentTelegram($token))->deleteWebhook(); } catch (\Throwable $e) {}
            }
        }

        $agent->delete();

        return redirect()->route('agents.index')->with('success', 'Agent o\'chirildi.');
    }

    /** Holatni almashtirish (active <-> paused). */
    public function toggleStatus(Agent $agent)
    {
        $this->authorizeAgent($agent);

        if ($agent->status === 'active') {
            $agent->update(['status' => 'paused']);
        } else {
            $agent->update(['status' => 'active']);
        }

        return back()->with('success', 'Holat yangilandi.');
    }

    /** Telegram botni ulash: token → getMe → webhook. */
    public function connectTelegram(Request $request, Agent $agent)
    {
        $this->authorizeAgent($agent);

        // Nusxalashda tushib qolgan bo'sh joy/qatorlarni validatsiyadan OLDIN tozalash
        $token = trim((string) $request->input('bot_token'));
        $token = preg_replace('/\s+/', '', $token); // ichki bo'shliqlarni ham
        $request->merge(['bot_token' => $token]);

        $request->validate([
            'bot_token' => ['required', 'string', 'regex:/^\d{5,}:[A-Za-z0-9_-]{30,}$/'],
        ], [
            'bot_token.regex' => 'Token formati noto\'g\'ri. @BotFather bergan «123456789:AAE...» ko\'rinishidagi tokenni to\'liq nusxalang.',
        ]);

        // Tokenni Telegram orqali tekshirish
        $tg = new AgentTelegram($token);
        $me = $tg->getMe();
        if (!($me['ok'] ?? false)) {
            $desc = $me['description'] ?? 'javob yo\'q (server Telegram API ga chiqa olmadi?)';
            return back()->withErrors(['bot_token' => 'Telegram tokenni rad etdi: ' . $desc]);
        }
        $bot = $me['result'];

        // Shu bot boshqa agentga ulanganmi?
        $exists = AgentChannel::where('type', 'telegram')
            ->where('external_id', (string) $bot['id'])
            ->where('agent_id', '!=', $agent->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['bot_token' => 'Bu bot allaqachon boshqa agentga ulangan.']);
        }

        // Kanalni yaratish/yangilash
        $channel = $agent->telegramChannel ?? new AgentChannel([
            'agent_id' => $agent->id,
            'type'     => 'telegram',
        ]);
        $channel->external_id    = (string) $bot['id'];
        $channel->webhook_secret = $channel->webhook_secret ?: AgentChannel::newWebhookSecret();
        $channel->status         = 'active';
        $channel->connected_at   = now();
        $channel->setTelegramToken($token); // config['bot_token'] shifrlanadi
        $channel->config = array_merge($channel->config ?? [], [
            'bot_username'   => $bot['username'] ?? null,
            'bot_first_name' => $bot['first_name'] ?? null,
        ]);
        $channel->agent()->associate($agent);
        $channel->save();

        // Webhookni o'rnatish (public https URL kerak)
        $webhookUrl = $this->webhookUrl($channel);
        $isLocal = str_contains($webhookUrl, 'localhost') || str_contains($webhookUrl, '127.0.0.1');
        $isHttps = str_starts_with($webhookUrl, 'https://');

        if ($isLocal || !$isHttps) {
            $agent->update(['status' => 'active']);
            return back()->with('warning', "Bot ulandi (@{$bot['username']}), lekin APP_URL public https emas ({$webhookUrl}) — webhook o'rnatilmadi. .env dagi APP_URL ni to'g'ri public https manzilga qo'ying.");
        }

        $res = $tg->setWebhook($webhookUrl, $channel->webhook_secret);
        $this->storeWebhookResult($channel, $res, $webhookUrl);

        if (!($res['ok'] ?? false)) {
            return back()->withErrors(['bot_token' => 'Bot topildi, lekin webhook o\'rnatilmadi: ' . ($res['description'] ?? 'noma\'lum xato') . ' (URL: ' . $webhookUrl . ')']);
        }

        $agent->update(['status' => 'active']);

        return back()->with('success', "Bot ulandi: @{$bot['username']}. Agent faol!");
    }

    /** Telegram webhook holati (diagnostika) — JSON. */
    public function telegramStatus(Agent $agent)
    {
        $this->authorizeAgent($agent);
        $ch = $agent->telegramChannel;
        if (!$ch || !($token = $ch->getTelegramToken())) {
            return response()->json(['connected' => false]);
        }

        $expected = $this->webhookUrl($ch);
        $info = (new AgentTelegram($token))->getWebhookInfo();
        $r = $info['result'] ?? [];

        return response()->json([
            'connected'    => true,
            'bot'          => '@' . ($ch->config['bot_username'] ?? ''),
            'expected_url' => $expected,
            'current_url'  => $r['url'] ?? '',
            'match'        => ($r['url'] ?? null) === $expected,
            'pending'      => $r['pending_update_count'] ?? 0,
            'last_error'   => $r['last_error_message'] ?? null,
            'last_error_at'=> isset($r['last_error_date']) ? date('Y-m-d H:i', $r['last_error_date']) : null,
        ]);
    }

    /** Webhookni qayta o'rnatish. */
    public function resetWebhook(Agent $agent)
    {
        $this->authorizeAgent($agent);
        $ch = $agent->telegramChannel;
        if (!$ch || !($token = $ch->getTelegramToken())) {
            return back()->withErrors(['bot_token' => 'Avval botni ulang.']);
        }

        $url = $this->webhookUrl($ch);
        if (!str_starts_with($url, 'https://') || str_contains($url, 'localhost')) {
            return back()->withErrors(['bot_token' => "APP_URL public https emas: {$url}"]);
        }

        $res = (new AgentTelegram($token))->setWebhook($url, $ch->webhook_secret);
        $this->storeWebhookResult($ch, $res, $url);

        if (!($res['ok'] ?? false)) {
            return back()->withErrors(['bot_token' => 'Webhook o\'rnatilmadi: ' . ($res['description'] ?? 'noma\'lum')]);
        }
        return back()->with('success', 'Webhook qayta o\'rnatildi: ' . $url);
    }

    /** Egа uchun sinov — agentni to'g'ridan-to'g'ri chaqirib, ANIQ natija/xatoни ko'rsatadi. */
    public function testAgent(Request $request, Agent $agent, AgentRunner $runner)
    {
        $this->authorizeAgent($agent);
        $request->validate(['message' => 'required|string|max:2000']);

        $conv = AgentConversation::firstOrCreate([
            'agent_id'         => $agent->id,
            'channel_type'     => 'test',
            'external_chat_id' => 'owner-test-' . Auth::id(),
        ], [
            'title' => 'Panel sinovi',
        ]);

        $result = $runner->reply($agent, $conv, (string) $request->input('message'));

        if ($result['success'] ?? false) {
            return response()->json([
                'success'  => true,
                'content'  => $result['content'],
                'model'    => $result['model'] ?? null,
                'cost_uzs' => $result['cost_uzs'] ?? 0,
            ]);
        }

        return response()->json([
            'success' => false,
            'error'   => $result['error'] ?? 'error',
        ]);
    }

    // === MCP serverlar ===

    public function addMcp(Request $request, Agent $agent)
    {
        $this->authorizeAgent($agent);
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'url'         => 'required|url|max:500',
            'auth_token'  => 'nullable|string|max:2000',
        ]);

        if ($agent->mcpServers()->count() >= 10) {
            return back()->withErrors(['mcp' => 'Maksimal MCP serverlar soniga yetdingiz (10).']);
        }

        $server = new AgentMcpServer([
            'agent_id'  => $agent->id,
            'name'      => $data['name'],
            'url'       => $data['url'],
            'transport' => 'http',
            'enabled'   => true,
            'status'    => 'unknown',
        ]);
        if (!empty($data['auth_token'])) {
            $token = trim($data['auth_token']);
            $server->setHeaders(['Authorization' => str_starts_with($token, 'Bearer ') ? $token : "Bearer {$token}"]);
        }
        $server->agent()->associate($agent);
        $server->save();

        $res = $this->discoverMcp($server);
        return $res['ok']
            ? back()->with('success', "MCP ulandi: {$server->name} · {$res['count']} tool topildi.")
            : back()->with('warning', "MCP saqlandi, lekin ulanmadi: {$res['error']}");
    }

    public function testMcp(Agent $agent, AgentMcpServer $mcp)
    {
        $this->authorizeAgent($agent);
        abort_unless($mcp->agent_id === $agent->id, 404);

        $res = $this->discoverMcp($mcp);
        return $res['ok']
            ? back()->with('success', "{$mcp->name}: {$res['count']} tool topildi.")
            : back()->withErrors(['mcp' => "{$mcp->name}: {$res['error']}"]);
    }

    public function toggleMcp(Agent $agent, AgentMcpServer $mcp)
    {
        $this->authorizeAgent($agent);
        abort_unless($mcp->agent_id === $agent->id, 404);
        $mcp->update(['enabled' => !$mcp->enabled]);
        return back()->with('success', $mcp->enabled ? 'MCP yoqildi.' : 'MCP o\'chirildi.');
    }

    public function deleteMcp(Agent $agent, AgentMcpServer $mcp)
    {
        $this->authorizeAgent($agent);
        abort_unless($mcp->agent_id === $agent->id, 404);
        $mcp->delete();
        return back()->with('success', 'MCP server o\'chirildi.');
    }

    /** MCP serverга ulanib toollarni aniqlaydi va keshlaydi. */
    protected function discoverMcp(AgentMcpServer $server): array
    {
        try {
            $client = new McpClient($server->url, $server->getHeaders(), 20);
            $client->initialize();
            $tools = $client->listTools();

            $clean = [];
            foreach ($tools as $t) {
                if (empty($t['name'])) continue;
                $clean[] = [
                    'name'        => $t['name'],
                    'description' => $t['description'] ?? '',
                    'inputSchema' => $t['inputSchema'] ?? ['type' => 'object', 'properties' => (object) []],
                ];
            }

            $server->update([
                'status'          => 'ok',
                'tools'           => $clean,
                'tools_count'     => count($clean),
                'last_error'      => null,
                'last_checked_at' => now(),
            ]);
            return ['ok' => true, 'count' => count($clean)];
        } catch (\Throwable $e) {
            $server->update([
                'status'          => 'error',
                'last_error'      => mb_substr($e->getMessage(), 0, 300),
                'last_checked_at' => now(),
            ]);
            return ['ok' => false, 'error' => mb_substr($e->getMessage(), 0, 200)];
        }
    }

    // === API kanal ===

    public function generateApiKey(Agent $agent)
    {
        $this->authorizeAgent($agent);

        $channel = $agent->apiChannel ?? new AgentChannel(['agent_id' => $agent->id, 'type' => 'api']);
        $key = AgentChannel::newApiKey();
        $channel->setApiKey($key);
        $channel->status = 'active';
        $channel->connected_at = now();
        $channel->agent()->associate($agent);
        $channel->save();

        if ($agent->status === 'draft') $agent->update(['status' => 'active']);

        return back()
            ->with('success', 'API kaliti yaratildi. Uni hoziroq saqlang — qayta ko\'rsatilmaydi.')
            ->with('new_api_key', $key);
    }

    public function revokeApi(Agent $agent)
    {
        $this->authorizeAgent($agent);
        $agent->apiChannel?->delete();
        return back()->with('success', 'API o\'chirildi.');
    }

    // === Web widget kanal ===

    public function saveWidget(Request $request, Agent $agent)
    {
        $this->authorizeAgent($agent);
        $data = $request->validate([
            'accent'          => 'nullable|string|max:9',
            'allowed_origins' => 'nullable|string|max:2000',
        ]);

        $origins = collect(preg_split('/[\s,]+/', (string) ($data['allowed_origins'] ?? '')))
            ->filter()->values()->all();

        $channel = $agent->webChannel ?? new AgentChannel(['agent_id' => $agent->id, 'type' => 'web']);
        $channel->status = 'active';
        $channel->connected_at = now();
        $channel->config = array_merge($channel->config ?? [], [
            'accent'          => $data['accent'] ?: '#111111',
            'allowed_origins' => $origins,
        ]);
        $channel->agent()->associate($agent);
        $channel->save();

        if ($agent->status === 'draft') $agent->update(['status' => 'active']);

        return back()->with('success', 'Web widget yoqildi.');
    }

    public function disableWidget(Agent $agent)
    {
        $this->authorizeAgent($agent);
        $agent->webChannel?->delete();
        return back()->with('success', 'Web widget o\'chirildi.');
    }

    protected function webhookUrl(AgentChannel $channel): string
    {
        return rtrim(config('app.url'), '/') . '/api/agent/webhook/' . $channel->webhook_secret;
    }

    protected function storeWebhookResult(AgentChannel $channel, ?array $res, string $url): void
    {
        $channel->config = array_merge($channel->config ?? [], [
            'webhook_url'      => $url,
            'webhook_ok'       => (bool) ($res['ok'] ?? false),
            'webhook_error'    => ($res['ok'] ?? false) ? null : ($res['description'] ?? 'noma\'lum'),
            'webhook_set_at'   => now()->toDateTimeString(),
        ]);
        $channel->save();
    }

    /** Telegram botni uzish. */
    public function disconnectTelegram(Agent $agent)
    {
        $this->authorizeAgent($agent);

        if ($ch = $agent->telegramChannel) {
            if ($token = $ch->getTelegramToken()) {
                try { (new AgentTelegram($token))->deleteWebhook(); } catch (\Throwable $e) {}
            }
            $ch->delete();
        }

        return back()->with('success', 'Telegram uzildi.');
    }

    // === Helpers ===

    protected function validateAgent(Request $request): array
    {
        return $request->validate([
            'name'                => 'required|string|max:80',
            'description'         => 'nullable|string|max:300',
            'behavior_preset'     => 'required|in:general,coder,support,sales,tutor,custom',
            'system_prompt'       => 'nullable|string|max:8000',
            'greeting'            => 'nullable|string|max:1000',
            'model_slug'          => 'required|string|max:120',
            'temperature'         => 'required|numeric|min:0|max:2',
            'max_tokens'          => 'nullable|integer|min:64|max:32000',
            'memory_limit'        => 'required|integer|min:2|max:100',
            'spend_cap_daily_uzs' => 'nullable|numeric|min:0|max:100000000',
        ]);
    }

    protected function authorizeAgent(Agent $agent): void
    {
        if ($agent->user_id !== Auth::id()) {
            abort(403);
        }
    }

    /** Agent uchun mos (matnli) modellar. */
    protected function modelOptions()
    {
        return AiModel::active()
            ->whereNotIn('category', ['image', 'audio', 'video', 'embedding'])
            ->orderByDesc('is_featured')
            ->orderBy('priority')
            ->get(['model_id', 'slug', 'display_name', 'provider', 'category', 'is_free'])
            ->unique('slug')
            ->values();
    }
}
