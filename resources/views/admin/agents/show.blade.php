@extends('admin.layout')

@section('title', $agent->name)
@section('page_title', 'Agent · ' . $agent->name)

@push('styles')
<style>
.as { padding: 24px; max-width: 1200px; margin: 0 auto; }
.as-grid { display: grid; grid-template-columns: 1fr 320px; gap: 18px; align-items: start; }
@media (max-width: 900px) { .as-grid { grid-template-columns: 1fr; } }
.card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 12px; padding: 18px; margin-bottom: 16px; }
.card h3 { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--text-subtle); margin-bottom: 12px; }
.kv { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; font-size: 13px; border-top: 1px solid var(--border); }
.kv:first-of-type { border-top: none; }
.kv .k { color: var(--text-muted); } .kv .v { color: var(--text-strong); font-weight: 600; text-align: right; }
.mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; }
.prompt { font-size: 12.5px; color: var(--text); background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 8px; padding: 12px; white-space: pre-wrap; max-height: 220px; overflow: auto; }
table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
th { text-align: left; font-size: 10px; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: .06em; padding: 9px 12px; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
td { padding: 9px 12px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
.msg-role { font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 1px 6px; border-radius: 5px; }
.role-user { background: var(--bg-subtle); color: var(--text-muted); }
.role-assistant { background: rgba(37,99,235,.12); color: var(--accent); }
.msg-content { max-width: 480px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text); }
.num { font-family: 'JetBrains Mono', monospace; font-weight: 600; }
.back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--text-muted); font-size: 13px; margin-bottom: 14px; }
.back-link:hover { color: var(--text-strong); }
.mcp-line { display: flex; justify-content: space-between; font-size: 12.5px; padding: 6px 0; border-top: 1px solid var(--border); }
.mcp-line:first-child { border-top: none; }
</style>
@endpush

@section('content')
<div class="as">
  <a href="{{ route('admin.agents.index') }}" class="back-link"><span class="material-icons-round" style="font-size:17px">arrow_back</span> Agentlar</a>

  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $agent->name }}</h1>
      <p class="page-subtitle">Egasi: {{ $agent->user->name ?? '—' }} ({{ $agent->user->email ?? '' }})</p>
    </div>
    <div style="display:flex;gap:8px;">
      <form method="POST" action="{{ route('admin.agents.toggle', $agent->id) }}">@csrf
        <button class="btn btn-secondary"><span class="material-icons-round">{{ $agent->status==='active' ? 'pause' : 'play_arrow' }}</span> {{ $agent->status==='active' ? 'To\'xtatish' : 'Faollashtirish' }}</button>
      </form>
      <form method="POST" action="{{ route('admin.agents.destroy', $agent->id) }}" onsubmit="return confirm('Agent o\'chiriladi. Davom etilsinmi?')">@csrf @method('DELETE')
        <button class="btn btn-danger"><span class="material-icons-round">delete_outline</span></button>
      </form>
    </div>
  </div>

  <div class="as-grid">
    <div>
      <div class="card">
        <h3>Suhbatlar (oxirgi 30)</h3>
        @if($conversations->isEmpty())
          <div style="color:var(--text-muted);font-size:13px;padding:8px 0;">Suhbat yo'q.</div>
        @else
          <table>
            <thead><tr><th>Kanal</th><th>Kim</th><th>Xabar</th><th>Oxirgi</th></tr></thead>
            <tbody>
              @foreach($conversations as $c)
              <tr>
                <td><span class="badge">{{ $c->channel_type }}</span></td>
                <td>{{ $c->title ?: $c->external_chat_id }}</td>
                <td class="num">{{ number_format($c->total_messages) }}</td>
                <td class="user-email" style="color:var(--text-muted)">{{ optional($c->last_message_at)->format('M d, H:i') ?? '—' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>

      <div class="card">
        <h3>Oxirgi xabarlar (40)</h3>
        @if($recentMessages->isEmpty())
          <div style="color:var(--text-muted);font-size:13px;padding:8px 0;">Xabar yo'q.</div>
        @else
          <table>
            <thead><tr><th>Rol</th><th>Matn</th><th>Token</th><th>Sarf</th></tr></thead>
            <tbody>
              @foreach($recentMessages as $m)
              <tr>
                <td><span class="msg-role role-{{ $m->role }}">{{ $m->role }}</span></td>
                <td class="msg-content">{{ \Illuminate\Support\Str::limit($m->content, 90) }}</td>
                <td class="num">{{ number_format((int)$m->tokens_input + (int)$m->tokens_output) }}</td>
                <td class="num">{{ number_format($m->cost_uzs, 0, '.', ' ') }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>

    <div>
      <div class="card">
        <h3>Sozlamalar</h3>
        <div class="kv"><span class="k">Holat</span><span class="v">{{ $agent->status }}</span></div>
        <div class="kv"><span class="k">Model</span><span class="v mono">{{ $agent->model_slug ?: $agent->model_mode }}</span></div>
        <div class="kv"><span class="k">Preset</span><span class="v">{{ $agent->behavior_preset ?: '—' }}</span></div>
        <div class="kv"><span class="k">Temperature</span><span class="v mono">{{ $agent->temperature }}</span></div>
        <div class="kv"><span class="k">Xotira</span><span class="v mono">{{ $agent->memory_limit }}</span></div>
        <div class="kv"><span class="k">Kunlik limit</span><span class="v mono">{{ $agent->spend_cap_daily_uzs ? number_format($agent->spend_cap_daily_uzs,0,'.',' ') : '∞' }}</span></div>
        <div class="kv"><span class="k">Jami sarf</span><span class="v mono">{{ number_format((float)$agent->total_spent_uzs,0,'.',' ') }} so'm</span></div>
        <div class="kv"><span class="k">Javoblar</span><span class="v mono">{{ number_format((int)$agent->total_replies) }}</span></div>
      </div>

      <div class="card">
        <h3>Kanallar</h3>
        <div class="kv"><span class="k">Telegram</span><span class="v">{{ ($agent->telegramChannel && $agent->telegramChannel->status==='active') ? '@'.($agent->telegramChannel->config['bot_username'] ?? 'ulangan') : '—' }}</span></div>
        <div class="kv"><span class="k">API</span><span class="v">{{ ($agent->apiChannel && $agent->apiChannel->status==='active') ? 'yoqilgan' : '—' }}</span></div>
        <div class="kv"><span class="k">Web widget</span><span class="v">{{ ($agent->webChannel && $agent->webChannel->status==='active') ? 'yoqilgan' : '—' }}</span></div>
      </div>

      @if($agent->mcpServers->count())
      <div class="card">
        <h3>MCP toollar</h3>
        @foreach($agent->mcpServers as $s)
          <div class="mcp-line"><span>{{ $s->name }}</span><span class="badge {{ $s->status==='ok' ? 'badge-success' : ($s->status==='error' ? 'badge-danger' : '') }}">{{ $s->tools_count }} tool</span></div>
        @endforeach
      </div>
      @endif

      @if($agent->system_prompt)
      <div class="card">
        <h3>System prompt</h3>
        <div class="prompt">{{ $agent->system_prompt }}</div>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
