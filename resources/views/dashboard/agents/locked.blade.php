@extends('layouts.app')

@section('title', 'Agent platformasi — CloudAPI')

@push('styles')
<style>
.lock-wrap { max-width: 560px; margin: 60px auto; padding: 0 24px; text-align: center; }
.lock-icon {
  width: 76px; height: 76px; border-radius: 22px; margin: 0 auto 22px;
  display: flex; align-items: center; justify-content: center;
  background: var(--bg-subtle); border: 1px solid var(--border); color: var(--text-muted);
}
.lock-icon .material-icons-round { font-size: 38px; }
.lock-title { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); margin-bottom: 10px; }
.lock-text { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 26px; }
.lock-card {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: var(--r-lg); padding: 22px; margin-bottom: 24px; text-align: left;
}
.lock-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; font-size: 14px; }
.lock-row + .lock-row { border-top: 1px solid var(--border); }
.lock-row .k { color: var(--text-muted); }
.lock-row .v { font-weight: 700; color: var(--text-strong); font-variant-numeric: tabular-nums; }
.lock-row .v.need { color: var(--danger); }
.lock-feats { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 26px; }
.lock-feat { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); background: var(--bg-subtle); border: 1px solid var(--border); padding: 6px 11px; border-radius: 99px; }
.lock-feat .material-icons-round { font-size: 15px; }
</style>
@endpush

@section('content')
<div class="lock-wrap fade-up">
  <div class="lock-icon"><span class="material-icons-round">lock</span></div>
  <div class="lock-title">Agent platformasi qulflangan</div>
  <div class="lock-text">
    AI Agentlar, MCP toollar, web widget, API va Vantage — CloudAPI'ning premium imkoniyatlari.
    Ulardan foydalanish uchun hisobingizda kamida <b>{{ number_format($min, 0, '.', ' ') }} so'm</b> bo'lishi kerak.
  </div>

  <div class="lock-card">
    <div class="lock-row">
      <span class="k">Talab qilinadigan minimal</span>
      <span class="v">{{ number_format($min, 0, '.', ' ') }} so'm</span>
    </div>
    <div class="lock-row">
      <span class="k">Hozirgi balans</span>
      <span class="v">{{ number_format($balance, 0, '.', ' ') }} so'm</span>
    </div>
    @if($balance < $min)
      <div class="lock-row">
        <span class="k">Yetishmaydi</span>
        <span class="v need">{{ number_format($min - $balance, 0, '.', ' ') }} so'm</span>
      </div>
    @endif
  </div>

  <div class="lock-feats">
    <span class="lock-feat"><span class="material-icons-round">smart_toy</span> AI Agentlar</span>
    <span class="lock-feat"><span class="material-icons-round">build</span> MCP toollar</span>
    <span class="lock-feat"><span class="material-icons-round">code</span> Web widget + API</span>
    <span class="lock-feat"><span class="material-icons-round">radar</span> Vantage</span>
  </div>

  <a href="{{ route('billing.topup') }}" class="btn btn-primary btn-lg">
    <span class="material-icons-round">add_circle</span> Hisobni to'ldirish
  </a>
</div>
@endsection
