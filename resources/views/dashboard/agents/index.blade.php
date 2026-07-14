@extends('layouts.app')

@section('title', 'Agentlar — CloudAPI')

@push('styles')
<style>
.agents-page { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }
.agents-head {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; margin-bottom: 28px; flex-wrap: wrap;
}
.agents-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}
.agent-card {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: var(--r-lg); padding: 20px;
  display: flex; flex-direction: column; gap: 14px;
  transition: border-color .15s var(--ease), box-shadow .15s var(--ease), transform .15s var(--ease);
}
.agent-card:hover { border-color: var(--border-strong); box-shadow: var(--shadow-md); transform: translateY(-2px); }
.agent-card-top { display: flex; align-items: center; gap: 12px; }
.agent-avatar {
  width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: var(--text-strong); color: var(--bg-elevated);
  font-weight: 800; font-size: 18px; letter-spacing: -0.02em;
}
.agent-name { font-size: 15px; font-weight: 700; color: var(--text-strong); line-height: 1.2; }
.agent-desc { font-size: 12.5px; color: var(--text-muted); line-height: 1.5; min-height: 19px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.agent-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.agent-stats { display: flex; gap: 18px; padding-top: 12px; border-top: 1px solid var(--border); }
.agent-stat-val { font-size: 15px; font-weight: 700; color: var(--text-strong); }
.agent-stat-lbl { font-size: 11px; color: var(--text-subtle); }
.agent-actions { display: flex; gap: 8px; }
.agent-actions .btn { flex: 1; }
.empty-state {
  text-align: center; padding: 72px 24px;
  border: 1px dashed var(--border-strong); border-radius: var(--r-lg);
  background: var(--bg-subtle);
}
.empty-icon {
  width: 64px; height: 64px; border-radius: 18px; margin: 0 auto 18px;
  display: flex; align-items: center; justify-content: center;
  background: var(--bg-elevated); border: 1px solid var(--border); color: var(--text-muted);
}
.empty-icon .material-icons-round { font-size: 30px; }
.tg-dot { width: 6px; height: 6px; border-radius: 99px; background: var(--success); display: inline-block; }
</style>
@endpush

@section('content')
<div class="agents-page fade-up">

  <div class="agents-head">
    <div>
      <div class="page-title">AI Agentlar</div>
      <div class="page-subtitle">O'z AI agentingizni yarating va Telegramga ulang — kod yozmasdan.</div>
    </div>
    <a href="{{ route('agents.create') }}" class="btn btn-primary btn-lg">
      <span class="material-icons-round">add</span> Yangi agent
    </a>
  </div>

  @if(session('warning'))
    <div class="alert alert-warning"><span class="material-icons-round">warning</span><div>{{ session('warning') }}</div></div>
  @endif

  @if($agents->isEmpty())
    <div class="empty-state">
      <div class="empty-icon"><span class="material-icons-round">smart_toy</span></div>
      <div style="font-size:17px;font-weight:700;color:var(--text-strong);margin-bottom:6px;">Hali agent yo'q</div>
      <div class="text-muted" style="font-size:13px;max-width:420px;margin:0 auto 20px;">
        Birinchi agentingizni yarating: unga xarakter bering, model tanlang va o'z Telegram botingizga ulang.
      </div>
      <a href="{{ route('agents.create') }}" class="btn btn-primary btn-lg">
        <span class="material-icons-round">add</span> Agent yaratish
      </a>
    </div>
  @else
    <div class="agents-grid">
      @foreach($agents as $agent)
        @php $tg = $agent->telegramChannel; @endphp
        <div class="agent-card">
          <div class="agent-card-top">
            <div class="agent-avatar">{{ mb_strtoupper(mb_substr($agent->name, 0, 1)) }}</div>
            <div style="min-width:0;flex:1;">
              <div class="agent-name">{{ $agent->name }}</div>
              <div class="agent-meta" style="margin-top:5px;">
                @if($agent->status === 'active')
                  <span class="badge badge-success">Faol</span>
                @elseif($agent->status === 'paused')
                  <span class="badge badge-warning">To'xtatilgan</span>
                @else
                  <span class="badge">Qoralama</span>
                @endif
                @if($tg && $tg->isActive())
                  <span class="badge badge-info"><span class="tg-dot"></span> {{ '@'.($tg->config['bot_username'] ?? 'telegram') }}</span>
                @endif
              </div>
            </div>
          </div>

          <div class="agent-desc">{{ $agent->description ?: 'Tavsif yo\'q' }}</div>

          <div class="agent-stats">
            <div>
              <div class="agent-stat-val">{{ number_format($agent->total_replies) }}</div>
              <div class="agent-stat-lbl">Javoblar</div>
            </div>
            <div>
              <div class="agent-stat-val">{{ number_format($agent->total_spent_uzs, 0) }}</div>
              <div class="agent-stat-lbl">Sarflandi (so'm)</div>
            </div>
          </div>

          <div class="agent-actions">
            <a href="{{ route('agents.edit', $agent) }}" class="btn btn-secondary btn-sm">
              <span class="material-icons-round">tune</span> Sozlash
            </a>
            <form method="POST" action="{{ route('agents.destroy', $agent) }}" style="flex:0;"
                  onsubmit="return confirm('Agent va uning barcha suhbatlari o\'chiriladi. Davom etilsinmi?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-ghost btn-sm" title="O'chirish">
                <span class="material-icons-round">delete_outline</span>
              </button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @endif

</div>
@endsection
