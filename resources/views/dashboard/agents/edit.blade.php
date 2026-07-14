@extends('layouts.app')

@section('title', ($isNew ? 'Yangi agent' : $agent->name) . ' — CloudAPI')

@push('styles')
<style>
.builder-page { max-width: 1080px; margin: 0 auto; padding: 28px 24px 64px; }
.builder-top { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
.builder-back {
  width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid var(--border); color: var(--text-muted);
  transition: all .15s var(--ease);
}
.builder-back:hover { background: var(--bg-subtle); color: var(--text); }
.builder-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
@media (max-width: 900px) { .builder-grid { grid-template-columns: 1fr; } }
.section-card {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: var(--r-lg); padding: 22px; margin-bottom: 18px;
}
.section-title {
  font-size: 13px; font-weight: 700; color: var(--text-strong);
  text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px;
}
.section-hint { font-size: 12px; color: var(--text-subtle); margin-bottom: 18px; }
.side-card {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: var(--r-lg); padding: 20px; margin-bottom: 18px;
}
.help { font-size: 11.5px; color: var(--text-subtle); margin-top: 6px; line-height: 1.5; }
.range-row { display: flex; align-items: center; gap: 14px; }
.range-row input[type=range] { flex: 1; accent-color: var(--accent); }
.range-val {
  min-width: 44px; text-align: center; font-weight: 700; font-size: 13px;
  padding: 4px 8px; border-radius: 8px; background: var(--bg-subtle); color: var(--text-strong);
}
.preset-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
@media (max-width: 520px) { .preset-grid { grid-template-columns: repeat(2, 1fr); } }
.preset-opt {
  position: relative; cursor: pointer;
  border: 1px solid var(--border); border-radius: 10px; padding: 12px 10px;
  text-align: center; transition: all .15s var(--ease);
}
.preset-opt:hover { border-color: var(--border-strong); background: var(--bg-subtle); }
.preset-opt input { position: absolute; opacity: 0; }
.preset-opt .material-icons-round { font-size: 20px; color: var(--text-muted); }
.preset-opt .p-label { font-size: 12px; font-weight: 600; margin-top: 4px; color: var(--text); }
.preset-opt.on { border-color: var(--text-strong); background: var(--bg-subtle); }
.preset-opt.on .material-icons-round, .preset-opt.on .p-label { color: var(--text-strong); }
.tg-connected { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.tg-logo {
  width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: #229ED9; color: #fff;
}
.tg-logo .material-icons-round { font-size: 22px; }
.mono-box {
  font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-muted);
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 8px;
  padding: 8px 10px; word-break: break-all; margin-top: 8px;
}
.sticky-actions {
  position: sticky; bottom: 0; margin-top: 4px;
  display: flex; gap: 10px; justify-content: flex-end;
  padding: 16px 0 0;
}
.err-text { color: var(--danger); font-size: 12px; margin-top: 6px; }
</style>
@endpush

@section('content')
<div class="builder-page fade-up">

  <div class="builder-top">
    <a href="{{ route('agents.index') }}" class="builder-back"><span class="material-icons-round">arrow_back</span></a>
    <div style="flex:1;min-width:0;">
      <div class="page-title" style="margin-bottom:2px;">{{ $isNew ? 'Yangi agent' : $agent->name }}</div>
      <div class="page-subtitle">{{ $isNew ? 'Agentga xarakter bering va model tanlang.' : 'Agent sozlamalari' }}</div>
    </div>
    @if(!$isNew)
      <form method="POST" action="{{ route('agents.toggle', $agent) }}">
        @csrf
        @if($agent->status === 'active')
          <button class="btn btn-secondary"><span class="material-icons-round">pause</span> To'xtatish</button>
        @else
          <button class="btn btn-primary"><span class="material-icons-round">play_arrow</span> Faollashtirish</button>
        @endif
      </form>
    @endif
  </div>

  @if(session('warning'))
    <div class="alert alert-warning"><span class="material-icons-round">warning</span><div>{{ session('warning') }}</div></div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger"><span class="material-icons-round">error_outline</span>
      <div>{{ $errors->first() }}</div></div>
  @endif

  <div class="builder-grid">

      {{-- LEFT: config — asosiy saqlash formasi = chap ustun. Kanal formalari
           nested <form> bo'lib qolmasligi uchun o'ng ustun bu formadan TASHQARIDA. --}}
      <form method="POST" action="{{ $isNew ? route('agents.store') : route('agents.update', $agent) }}">
        @csrf
        @unless($isNew) @method('PUT') @endunless
        {{-- Identity --}}
        <div class="section-card">
          <div class="section-title">Asosiy</div>
          <div class="section-hint">Agent nomi va qisqacha tavsifi.</div>

          <div class="field">
            <label class="label">Nom *</label>
            <input type="text" name="name" class="input" maxlength="80" required
                   value="{{ old('name', $agent->name) }}" placeholder="Masalan: Sotuv yordamchisi">
          </div>
          <div class="field" style="margin-bottom:0;">
            <label class="label">Tavsif</label>
            <input type="text" name="description" class="input" maxlength="300"
                   value="{{ old('description', $agent->description) }}" placeholder="Agent nima qiladi?">
          </div>
        </div>

        {{-- Behavior --}}
        <div class="section-card">
          <div class="section-title">Xarakter</div>
          <div class="section-hint">Agent qanday maqsad uchun ishlashini tanlang, so'ng ko'rsatmalar bering.</div>

          @php
            $presets = [
              'general' => ['label' => 'Umumiy',   'icon' => 'chat'],
              'coder'   => ['label' => 'Dasturchi','icon' => 'code'],
              'support' => ['label' => 'Qo\'llab-quvvatlash', 'icon' => 'support_agent'],
              'sales'   => ['label' => 'Sotuv',    'icon' => 'sell'],
              'tutor'   => ['label' => 'O\'qituvchi', 'icon' => 'school'],
              'custom'  => ['label' => 'Maxsus',   'icon' => 'tune'],
            ];
            $curPreset = old('behavior_preset', $agent->behavior_preset ?: 'general');
          @endphp
          <div class="preset-grid" id="presetGrid">
            @foreach($presets as $key => $p)
              <label class="preset-opt {{ $curPreset === $key ? 'on' : '' }}">
                <input type="radio" name="behavior_preset" value="{{ $key }}" {{ $curPreset === $key ? 'checked' : '' }}>
                <span class="material-icons-round">{{ $p['icon'] }}</span>
                <div class="p-label">{{ $p['label'] }}</div>
              </label>
            @endforeach
          </div>

          <div class="field" style="margin-top:18px;">
            <label class="label">System prompt — ko'rsatma</label>
            <textarea name="system_prompt" class="textarea" rows="5" maxlength="8000"
              placeholder="Agentga rol va qoidalar bering. Masalan: Sen X kompaniyasining sotuv yordamchisisan. Faqat mahsulotlar haqida gapir...">{{ old('system_prompt', $agent->system_prompt) }}</textarea>
            <div class="help">Bu matn har suhbatda modelga «system» sifatida yuboriladi.</div>
          </div>

          <div class="field" style="margin-bottom:0;">
            <label class="label">Salomlashish (/start)</label>
            <textarea name="greeting" class="textarea" rows="2" maxlength="1000"
              placeholder="Foydalanuvchi /start bosganda ko'rsatiladigan xabar">{{ old('greeting', $agent->greeting) }}</textarea>
          </div>
        </div>

        {{-- Model --}}
        <div class="section-card">
          <div class="section-title">Model</div>
          <div class="section-hint">Agent qaysi model bilan javob beradi.</div>

          <div class="field">
            <label class="label">Model *</label>
            <select name="model_slug" class="select" required>
              @php $curModel = old('model_slug', $agent->model_slug ?: 'gpt-4o-mini'); @endphp
              @foreach($models as $m)
                <option value="{{ $m->slug }}" {{ $curModel === $m->slug ? 'selected' : '' }}>
                  {{ $m->display_name }} · {{ ucfirst($m->provider) }}{{ $m->is_free ? ' · bepul' : '' }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="field">
            <label class="label">Temperature — <span id="tempOut">{{ old('temperature', $agent->temperature ?? 0.7) }}</span></label>
            <div class="range-row">
              <input type="range" name="temperature" min="0" max="2" step="0.1"
                     value="{{ old('temperature', $agent->temperature ?? 0.7) }}"
                     oninput="document.getElementById('tempOut').textContent=this.value">
              <span class="range-val" id="tempVal">{{ old('temperature', $agent->temperature ?? 0.7) }}</span>
            </div>
            <div class="help">0 — aniq/barqaror, 2 — ijodiy/tasodifiy.</div>
          </div>

          <div class="flex gap-4">
            <div class="field" style="flex:1;">
              <label class="label">Max tokens</label>
              <input type="number" name="max_tokens" class="input" min="64" max="32000"
                     value="{{ old('max_tokens', $agent->max_tokens) }}" placeholder="cheksiz">
            </div>
            <div class="field" style="flex:1;">
              <label class="label">Xotira (xabar soni)</label>
              <input type="number" name="memory_limit" class="input" min="2" max="100" required
                     value="{{ old('memory_limit', $agent->memory_limit ?? 20) }}">
            </div>
          </div>
          <div class="help" style="margin-top:-6px;">Xotira — modelga yuboriladigan oxirgi xabarlar soni (kontekst).</div>
        </div>

        {{-- Limits --}}
        <div class="section-card" style="margin-bottom:0;">
          <div class="section-title">Xarajat limiti</div>
          <div class="section-hint">Har javob egangizning hamyonidan yechiladi. Suiiste'moldan himoya uchun kunlik chegara qo'ying.</div>
          <div class="field" style="margin-bottom:0;">
            <label class="label">Kunlik limit (so'm)</label>
            <input type="number" name="spend_cap_daily_uzs" class="input" min="0" step="100"
                   value="{{ old('spend_cap_daily_uzs', $agent->spend_cap_daily_uzs) }}" placeholder="limitsiz">
            <div class="help">Bo'sh qoldirilsa — limit yo'q. Limitdan oshsa, agent shu kun javob bermaydi.</div>
          </div>
        </div>

        <div class="sticky-actions">
          <a href="{{ route('agents.index') }}" class="btn btn-ghost">Bekor qilish</a>
          <button type="submit" class="btn btn-primary btn-lg">
            <span class="material-icons-round">check</span> {{ $isNew ? 'Yaratish' : 'Saqlash' }}
          </button>
        </div>
      </form>

      {{-- RIGHT: channels — asosiy formadan TASHQARIDA (o'z formalari nested emas) --}}
      <div>
        <div class="side-card">
          <div class="section-title" style="margin-bottom:14px;">Telegram</div>

          @if($isNew)
            <div class="text-muted" style="font-size:12.5px;line-height:1.6;">
              Agentni Telegram botga ulash uchun avval uni <b>saqlang</b>. So'ng shu yerda @BotFather tokenini kiritasiz.
            </div>
          @elseif($agent->telegramChannel && $agent->telegramChannel->isActive())
            @php $ch = $agent->telegramChannel; @endphp
            <div class="tg-connected">
              <div class="tg-logo"><span class="material-icons-round">send</span></div>
              <div style="min-width:0;">
                <div style="font-weight:700;font-size:13px;color:var(--text-strong);">{{ '@'.($ch->config['bot_username'] ?? 'bot') }}</div>
                <div style="font-size:11px;color:var(--success);font-weight:600;">Ulangan</div>
              </div>
            </div>
            <a href="https://t.me/{{ $ch->config['bot_username'] ?? '' }}" target="_blank" rel="noopener"
               class="btn btn-secondary w-full" style="margin-bottom:8px;">
              <span class="material-icons-round">open_in_new</span> Botni ochish
            </a>
            <form method="POST" action="{{ route('agents.telegram.disconnect', $agent) }}"
                  onsubmit="return confirm('Telegram uziladi. Davom etilsinmi?')">
              @csrf @method('DELETE')
              <button class="btn btn-ghost w-full"><span class="material-icons-round">link_off</span> Uzish</button>
            </form>

            {{-- Webhook diagnostika --}}
            <div id="tgDiag" data-url="{{ route('agents.telegram.status', $agent) }}"
                 style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);">
              <div class="flex justify-between items-center" style="margin-bottom:8px;">
                <span class="section-title" style="margin:0;">Webhook holati</span>
                <button type="button" id="tgDiagRefresh" class="btn btn-ghost btn-sm" title="Yangilash">
                  <span class="material-icons-round">refresh</span>
                </button>
              </div>
              <div id="tgDiagBody" class="text-muted" style="font-size:12px;">Tekshirilmoqda…</div>
              <form method="POST" action="{{ route('agents.telegram.reset', $agent) }}" style="margin-top:10px;">
                @csrf
                <button class="btn btn-secondary btn-sm w-full">
                  <span class="material-icons-round">sync</span> Webhookni qayta o'rnatish
                </button>
              </form>
            </div>
          @else
            <div class="text-muted" style="font-size:12.5px;line-height:1.6;margin-bottom:14px;">
              <b>@BotFather</b> orqali bot yarating, <code>/newbot</code> buyrug'i bergan tokenni bu yerga joylashtiring.
            </div>
            <form method="POST" action="{{ route('agents.telegram.connect', $agent) }}">
              @csrf
              <div class="field">
                <input type="text" name="bot_token" class="input mono" autocomplete="off"
                       placeholder="123456789:AAE...token" value="{{ old('bot_token') }}">
              </div>
              <button class="btn btn-primary w-full"><span class="material-icons-round">link</span> Botni ulash</button>
            </form>
          @endif
        </div>

        @unless($isNew)
        <div class="side-card" style="margin-bottom:0;">
          <div class="section-title" style="margin-bottom:12px;">Statistika</div>
          <div class="flex justify-between" style="font-size:13px;padding:6px 0;">
            <span class="text-muted">Javoblar</span><b>{{ number_format($agent->total_replies) }}</b>
          </div>
          <div class="flex justify-between" style="font-size:13px;padding:6px 0;border-top:1px solid var(--border);">
            <span class="text-muted">Jami sarf</span><b>{{ number_format($agent->total_spent_uzs, 0) }} so'm</b>
          </div>
          <div class="flex justify-between" style="font-size:13px;padding:6px 0;border-top:1px solid var(--border);">
            <span class="text-muted">Bugun</span><b>{{ number_format($agent->daily_spend_uzs, 0) }} so'm</b>
          </div>
        </div>
        @endunless
      </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  var grid = document.getElementById('presetGrid');
  if (grid) {
    grid.addEventListener('change', function(e){
      if (e.target.name === 'behavior_preset') {
        grid.querySelectorAll('.preset-opt').forEach(function(o){ o.classList.remove('on'); });
        e.target.closest('.preset-opt').classList.add('on');
      }
    });
  }
  var range = document.querySelector('input[name=temperature]');
  var val = document.getElementById('tempVal');
  if (range && val) range.addEventListener('input', function(){ val.textContent = this.value; });

  // Telegram webhook diagnostika
  var diag = document.getElementById('tgDiag');
  if (diag) {
    var body = document.getElementById('tgDiagBody');
    var esc = function(s){ var d=document.createElement('div'); d.textContent=s==null?'':String(s); return d.innerHTML; };
    var row = function(label, value, ok){
      var color = ok===true ? 'var(--success)' : (ok===false ? 'var(--danger)' : 'var(--text)');
      return '<div style="display:flex;justify-content:space-between;gap:10px;padding:4px 0;">'
        + '<span style="color:var(--text-subtle)">'+label+'</span>'
        + '<span style="color:'+color+';text-align:right;word-break:break-all;max-width:190px;">'+value+'</span></div>';
    };
    var load = function(){
      body.textContent = 'Tekshirilmoqda…';
      fetch(diag.dataset.url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){ return r.json(); })
        .then(function(d){
          if (!d.connected) { body.textContent = 'Ulanmagan.'; return; }
          var html = '';
          html += row('Manzil mos', d.match ? 'Ha' : 'Yo\'q', d.match);
          if (!d.match && d.current_url) html += row('Hozirgi URL', esc(d.current_url), false);
          html += row('Kutilayotgan', esc(d.expected_url), null);
          html += row('Navbatda', d.pending, d.pending>0?false:true);
          if (d.last_error) html += row('Oxirgi xato', esc(d.last_error)+(d.last_error_at?(' · '+esc(d.last_error_at)):''), false);
          else html += row('Xato', 'Yo\'q', true);
          body.innerHTML = html;
        })
        .catch(function(){ body.textContent = 'Holatni olishda xato.'; });
    };
    var btn = document.getElementById('tgDiagRefresh');
    if (btn) btn.addEventListener('click', load);
    load();
  }
})();
</script>
@endpush
