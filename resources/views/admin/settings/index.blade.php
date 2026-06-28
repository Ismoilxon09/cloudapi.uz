@extends('admin.layout')

@section('title', 'Tizim sozlamalari')
@section('page_title', 'Tizim sozlamalari')

@push('styles')
<style>
.settings-page { padding: 24px; max-width: 900px; margin: 0 auto; }

.settings-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 16px;
}

.section-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.section-subtitle {
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 20px;
}

.setting-row {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 16px;
  padding: 14px 0;
  border-bottom: 1px solid var(--border);
  align-items: center;
}

.setting-row:last-child { border-bottom: none; padding-bottom: 0; }
.setting-row:first-child { padding-top: 0; }

.setting-key { font-size: 13px; font-weight: 600; color: var(--text-strong); }
.setting-desc { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.setting-keycode { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--text-subtle); margin-top: 3px; }

.setting-input {
  width: 100%;
  padding: 8px 12px;
  font-size: 13px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 8px;
  outline: none;
  font-family: inherit;
}

.setting-input:focus { border-color: var(--accent); background: var(--bg-elevated); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }

.setting-input.mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; }

.bool-toggle { position: relative; width: 40px; height: 22px; }
.bool-toggle input { opacity: 0; width: 0; height: 0; }
.bool-toggle .slider { position: absolute; cursor: pointer; inset: 0; background: var(--border-strong); border-radius: 99px; transition: .2s; }
.bool-toggle .slider:before { content: ""; position: absolute; height: 18px; width: 18px; left: 2px; bottom: 2px; background: white; border-radius: 50%; transition: .2s; }
.bool-toggle input:checked + .slider { background: var(--success); }
.bool-toggle input:checked + .slider:before { transform: translateX(18px); }

.save-bar {
  position: sticky;
  bottom: 16px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 14px 20px;
  box-shadow: var(--shadow-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
}
</style>
@endpush

@section('content')
<div class="settings-page">
  <div class="page-header">
    <div>
      <h1 class="page-title">Tizim sozlamalari</h1>
      <p class="page-subtitle">Karta raqami, narxlar, limitlar va boshqalar</p>
    </div>
  </div>

  <form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf

    <!-- Payment settings -->
    <div class="settings-card">
      <div class="section-title">💳 To'lov sozlamalari</div>
      <div class="section-subtitle">User'lar shu kartaga ko'chirib screenshot yuboradi</div>

      @foreach($settings->whereIn('key', ['card_number', 'card_holder', 'min_topup_amount']) as $s)
      <div class="setting-row">
        <div>
          <div class="setting-key">{{ $s->description }}</div>
          <div class="setting-keycode">{{ $s->key }}</div>
        </div>
        <div>
          <input type="{{ $s->type === 'number' ? 'number' : 'text' }}"
                 name="{{ $s->key }}"
                 class="setting-input {{ $s->key === 'card_number' ? 'mono' : '' }}"
                 value="{{ $s->value }}">
        </div>
      </div>
      @endforeach
    </div>

    <!-- Pricing -->
    <div class="settings-card">
      <div class="section-title">📊 Narx sozlamalari</div>
      <div class="section-subtitle">Valyuta kursi va marja</div>

      @foreach($settings->whereIn('key', ['usd_to_uzs_rate', 'default_margin_percent', 'low_balance_threshold']) as $s)
      <div class="setting-row">
        <div>
          <div class="setting-key">{{ $s->description }}</div>
          <div class="setting-keycode">{{ $s->key }}</div>
        </div>
        <div>
          <input type="number"
                 name="{{ $s->key }}"
                 class="setting-input mono"
                 value="{{ $s->value }}"
                 step="{{ $s->key === 'default_margin_percent' ? '1' : '100' }}">
        </div>
      </div>
      @endforeach
    </div>

    <!-- Limits -->
    <div class="settings-card">
      <div class="section-title">⚡ Limitlar</div>
      <div class="section-subtitle">API kalitlar va rate limitlar</div>

      @foreach($settings->whereIn('key', ['max_keys_per_user', 'default_rate_limit']) as $s)
      <div class="setting-row">
        <div>
          <div class="setting-key">{{ $s->description }}</div>
          <div class="setting-keycode">{{ $s->key }}</div>
        </div>
        <div>
          <input type="number" name="{{ $s->key }}" class="setting-input mono" value="{{ $s->value }}">
        </div>
      </div>
      @endforeach
    </div>

    <!-- Bonuses -->
    <div class="settings-card">
      <div class="section-title">🎁 Bonuslar</div>
      <div class="section-subtitle">Referral va ro'yxatdan o'tish bonuslari</div>

      @foreach($settings->whereIn('key', ['signup_bonus_uzs', 'referral_bonus_uzs']) as $s)
      <div class="setting-row">
        <div>
          <div class="setting-key">{{ $s->description }}</div>
          <div class="setting-keycode">{{ $s->key }}</div>
        </div>
        <div>
          <input type="number" name="{{ $s->key }}" class="setting-input mono" value="{{ $s->value }}" step="1000">
        </div>
      </div>
      @endforeach
    </div>

    <!-- Telegram -->
    <div class="settings-card">
      <div class="section-title">📱 Telegram bot</div>
      <div class="section-subtitle">Admin bildirishnomalari va user xabarlari uchun</div>

      @foreach($settings->whereIn('key', ['telegram_bot_token', 'telegram_admin_chat_id']) as $s)
      <div class="setting-row">
        <div>
          <div class="setting-key">{{ $s->description }}</div>
          <div class="setting-keycode">{{ $s->key }}</div>
        </div>
        <div>
          <input type="text"
                 name="{{ $s->key }}"
                 class="setting-input mono"
                 value="{{ $s->value }}"
                 placeholder="{{ $s->key === 'telegram_bot_token' ? '1234:ABC...' : '123456789' }}">
        </div>
      </div>
      @endforeach
    </div>

    <!-- System -->
    <div class="settings-card">
      <div class="section-title">⚙️ Tizim</div>
      <div class="section-subtitle">Texnik sozlamalar</div>

      @foreach($settings->whereIn('key', ['maintenance_mode', 'registration_enabled']) as $s)
      <div class="setting-row">
        <div>
          <div class="setting-key">{{ $s->description }}</div>
          <div class="setting-keycode">{{ $s->key }}</div>
        </div>
        <div>
          <label class="bool-toggle">
            <input type="hidden" name="{{ $s->key }}" value="0">
            <input type="checkbox" name="{{ $s->key }}" value="1" {{ $s->value == '1' ? 'checked' : '' }}>
            <span class="slider"></span>
          </label>
        </div>
      </div>
      @endforeach
    </div>

    <div class="save-bar">
      <div style="font-size:13px;color:var(--text-muted)">O'zgartirishlardan keyin saqlashni unutmang</div>
      <button type="submit" class="btn btn-primary">
        <span class="material-icons-round">save</span>
        Sozlamalarni saqlash
      </button>
    </div>
  </form>
</div>
@endsection