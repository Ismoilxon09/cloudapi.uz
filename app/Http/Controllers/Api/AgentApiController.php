<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentConversation;
use App\Services\Agent\AgentRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Agentning tashqi kanallari:
 *  - server-to-server API (kalit bilan): POST /api/agent/{slug}/chat
 *  - web widget (public): GET /agent/{slug}/widget.js + POST /api/agent/{slug}/widget
 */
class AgentApiController extends Controller
{
    public function __construct(protected AgentRunner $runner) {}

    /** Server-to-server API — Authorization: Bearer <agtk_...> */
    public function chat(Request $request, string $slug)
    {
        $agent = $this->resolveAgent($slug);
        if (!$agent) {
            return response()->json(['error' => 'agent_not_found'], 404);
        }

        $channel = $agent->apiChannel;
        if (!$channel || !$channel->isActive()) {
            return response()->json(['error' => 'api_disabled'], 403);
        }

        $key = $this->bearer($request);
        if (!$key || !$channel->matchesApiKey($key)) {
            return response()->json(['error' => 'invalid_api_key'], 401);
        }

        $data = $request->validate([
            'message' => 'required|string|max:8000',
            'session' => 'nullable|string|max:120',
        ]);

        $session = $data['session'] ?: (string) Str::uuid();
        $conv = $this->conversation($agent, $channel->id, 'api', $session);

        $result = $this->runner->reply($agent, $conv, $data['message']);

        if ($result['success'] ?? false) {
            return response()->json([
                'reply'   => $result['content'],
                'session' => $session,
                'usage'   => [
                    'model'         => $result['model'] ?? null,
                    'tokens_input'  => $result['tokens_input'] ?? 0,
                    'tokens_output' => $result['tokens_output'] ?? 0,
                    'cost_uzs'      => $result['cost_uzs'] ?? 0,
                    'tools_used'    => $result['tools_used'] ?? [],
                ],
            ]);
        }

        return response()->json(['error' => $result['error'] ?? 'error'], $this->errorStatus($result['error'] ?? ''));
    }

    /** Web widget uchun embed skript. */
    public function widgetJs(string $slug)
    {
        $agent = $this->resolveAgent($slug);
        $channel = $agent?->webChannel;

        $js = ($agent && $channel && $channel->isActive())
            ? $this->buildWidgetJs($agent)
            : "console.warn('CloudAPI agent widget: mavjud emas yoki o\'chirilgan');";

        return response($js, 200, [
            'Content-Type'  => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    /** Web widget xabari — public (kalitsiz), CORS + spend-cap himoyasi. */
    public function widgetMessage(Request $request, string $slug)
    {
        $origin = $request->header('Origin', '*');
        $agent = $this->resolveAgent($slug);
        $channel = $agent?->webChannel;

        if (!$agent || !$channel || !$channel->isActive()) {
            return $this->cors(response()->json(['error' => 'widget_disabled'], 403), $origin);
        }

        // Origin cheklovi (agar sozlangan bo'lsa)
        $allowed = $channel->config['allowed_origins'] ?? [];
        if (!empty($allowed) && $origin !== '*' && !$this->originAllowed($origin, $allowed)) {
            return $this->cors(response()->json(['error' => 'origin_not_allowed'], 403), $origin);
        }

        $data = $request->validate([
            'message' => 'required|string|max:4000',
            'session' => 'nullable|string|max:120',
        ]);

        $session = $data['session'] ?: (string) Str::uuid();
        $conv = $this->conversation($agent, $channel->id, 'web', $session);

        $result = $this->runner->reply($agent, $conv, $data['message']);

        if ($result['success'] ?? false) {
            return $this->cors(response()->json([
                'reply'   => $result['content'],
                'session' => $session,
            ]), $origin);
        }

        $msg = in_array($result['error'] ?? '', ['insufficient_balance', 'daily_cap_reached'])
            ? 'Agent hozircha javob bera olmaydi.'
            : 'Xatolik yuz berdi. Birozdan so\'ng urinib ko\'ring.';
        return $this->cors(response()->json(['error' => $msg], 200), $origin);
    }

    /** CORS preflight. */
    public function widgetPreflight(Request $request, string $slug)
    {
        return $this->cors(response('', 204), $request->header('Origin', '*'));
    }

    // === Helpers ===

    protected function resolveAgent(string $slug): ?Agent
    {
        return Agent::where('slug', $slug)->where('status', 'active')->first();
    }

    protected function bearer(Request $request): ?string
    {
        $h = $request->header('Authorization', '');
        if (str_starts_with($h, 'Bearer ')) return trim(substr($h, 7));
        return $request->header('X-Agent-Key') ?: null;
    }

    protected function conversation(Agent $agent, ?int $channelId, string $type, string $session): AgentConversation
    {
        $conv = AgentConversation::firstOrNew([
            'agent_id'         => $agent->id,
            'channel_type'     => $type,
            'external_chat_id' => $session,
        ]);
        if (!$conv->exists) {
            $conv->channel_id = $channelId;
            $conv->title = ucfirst($type) . ' · ' . substr($session, 0, 8);
            $conv->save();
        }
        return $conv;
    }

    protected function errorStatus(string $error): int
    {
        return match ($error) {
            'insufficient_balance', 'daily_cap_reached' => 402,
            'model_unavailable', 'owner_missing' => 503,
            default => 500,
        };
    }

    protected function originAllowed(string $origin, array $allowed): bool
    {
        $host = parse_url($origin, PHP_URL_HOST) ?: $origin;
        foreach ($allowed as $a) {
            $a = trim($a);
            if ($a === '' || $a === '*') return true;
            $aHost = parse_url($a, PHP_URL_HOST) ?: $a;
            if (strcasecmp($host, $aHost) === 0) return true;
        }
        return false;
    }

    protected function cors($response, string $origin)
    {
        return $response
            ->header('Access-Control-Allow-Origin', $origin ?: '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type')
            ->header('Vary', 'Origin');
    }

    protected function buildWidgetJs(Agent $agent): string
    {
        $cfg = json_encode([
            'slug'     => $agent->slug,
            'name'     => $agent->name,
            'greeting' => $agent->greeting ?: "Salom! Men {$agent->name}. Savolingizni yozing.",
            'accent'   => $agent->webChannel->config['accent'] ?? '#111111',
            'endpoint' => rtrim(config('app.url'), '/') . '/api/agent/' . $agent->slug . '/widget',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<JS
(function(){
  var CFG = {$cfg};
  var KEY = 'cloudapi_agent_' + CFG.slug + '_session';
  var session = localStorage.getItem(KEY);
  if(!session){ session = (crypto.randomUUID ? crypto.randomUUID() : String(Date.now())+Math.random()); localStorage.setItem(KEY, session); }
  var open=false, booted=false;

  var css = ''
    + '.cai-btn{position:fixed;right:20px;bottom:20px;width:56px;height:56px;border-radius:50%;background:'+CFG.accent+';color:#fff;border:none;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.25);z-index:2147483000;display:flex;align-items:center;justify-content:center;font-size:24px}'
    + '.cai-panel{position:fixed;right:20px;bottom:88px;width:360px;max-width:calc(100vw - 40px);height:520px;max-height:calc(100vh - 120px);background:#fff;border-radius:16px;box-shadow:0 20px 48px rgba(0,0,0,.28);z-index:2147483000;display:none;flex-direction:column;overflow:hidden;font-family:-apple-system,system-ui,sans-serif}'
    + '.cai-panel.on{display:flex}'
    + '.cai-hd{background:'+CFG.accent+';color:#fff;padding:14px 16px;font-weight:700;font-size:15px;display:flex;justify-content:space-between;align-items:center}'
    + '.cai-x{cursor:pointer;opacity:.85;font-size:20px;line-height:1;background:none;border:none;color:#fff}'
    + '.cai-msgs{flex:1;overflow-y:auto;padding:16px;background:#f7f7f8;display:flex;flex-direction:column;gap:10px}'
    + '.cai-m{max-width:82%;padding:9px 12px;border-radius:14px;font-size:14px;line-height:1.45;white-space:pre-wrap;word-wrap:break-word}'
    + '.cai-u{align-self:flex-end;background:'+CFG.accent+';color:#fff;border-bottom-right-radius:4px}'
    + '.cai-a{align-self:flex-start;background:#fff;color:#111;border:1px solid #e5e7eb;border-bottom-left-radius:4px}'
    + '.cai-ft{display:flex;gap:8px;padding:12px;border-top:1px solid #eee;background:#fff}'
    + '.cai-in{flex:1;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;font-size:14px;outline:none;font-family:inherit}'
    + '.cai-snd{background:'+CFG.accent+';color:#fff;border:none;border-radius:10px;padding:0 14px;cursor:pointer;font-size:14px}'
    + '.cai-snd:disabled{opacity:.5;cursor:default}';
  var st=document.createElement('style'); st.textContent=css; document.head.appendChild(st);

  var btn=document.createElement('button'); btn.className='cai-btn'; btn.innerHTML='&#128172;';
  var panel=document.createElement('div'); panel.className='cai-panel';
  panel.innerHTML=''
    + '<div class="cai-hd"><span></span><button class="cai-x" aria-label="close">&times;</button></div>'
    + '<div class="cai-msgs"></div>'
    + '<div class="cai-ft"><input class="cai-in" placeholder="Xabar yozing..."><button class="cai-snd">&#10148;</button></div>';
  document.body.appendChild(btn); document.body.appendChild(panel);
  panel.querySelector('.cai-hd span').textContent=CFG.name;

  var msgs=panel.querySelector('.cai-msgs');
  var input=panel.querySelector('.cai-in');
  var send=panel.querySelector('.cai-snd');

  function add(text, who){ var d=document.createElement('div'); d.className='cai-m '+(who==='u'?'cai-u':'cai-a'); d.textContent=text; msgs.appendChild(d); msgs.scrollTop=msgs.scrollHeight; return d; }
  function toggle(){ open=!open; panel.classList.toggle('on', open); if(open){ if(!booted){ booted=true; add(CFG.greeting,'a'); } input.focus(); } }
  btn.addEventListener('click', toggle);
  panel.querySelector('.cai-x').addEventListener('click', toggle);

  function submit(){
    var text=(input.value||'').trim(); if(!text) return;
    input.value=''; add(text,'u'); send.disabled=true;
    var typing=add('...', 'a');
    fetch(CFG.endpoint, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text,session:session})})
      .then(function(r){return r.json();})
      .then(function(d){ typing.textContent = d.reply || d.error || 'Xatolik.'; })
      .catch(function(){ typing.textContent='Ulanishда xatolik.'; })
      .finally(function(){ send.disabled=false; msgs.scrollTop=msgs.scrollHeight; input.focus(); });
  }
  send.addEventListener('click', submit);
  input.addEventListener('keydown', function(e){ if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); submit(); } });
})();
JS;
    }
}
