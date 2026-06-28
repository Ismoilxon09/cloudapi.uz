@extends('layouts.app')

@section('title', $model->display_name . ' — CloudAPI')

@push('styles')
<style>
.model-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px;
  position: relative;
  z-index: 2;
}

/* Breadcrumb */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 20px;
}

.breadcrumb a { color: var(--text-muted); transition: color .15s; }
.breadcrumb a:hover { color: var(--text-strong); }
.breadcrumb .material-icons-round { font-size: 14px; color: var(--text-subtle); }

/* Header */
.model-header {
  display: flex;
  align-items: flex-start;
  gap: 18px;
  margin-bottom: 28px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--border);
  flex-wrap: wrap;
}

.model-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  padding: 10px;
}

.model-icon svg { width: 100%; height: 100%; }

.model-info { flex: 1; min-width: 0; }

.model-display-name {
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 6px;
}

.model-id-pill {
  font-size: 12px;
  color: var(--text-muted);
  font-family: 'JetBrains Mono', monospace;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 4px 10px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 6px;
  cursor: pointer;
  transition: all .15s;
}

.model-id-pill:hover { background: var(--bg-hover); border-color: var(--border-strong); }
.model-id-pill .material-icons-round { font-size: 13px; color: var(--text-subtle); }

.model-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 12px;
}

.model-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  font-size: 11px;
  font-weight: 700;
  border-radius: 99px;
  background: var(--bg-elevated);
  color: var(--text-muted);
  border: 1px solid var(--border);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.model-badge .material-icons-round { font-size: 12px; }

.model-badge.free {
  background: rgba(16, 185, 129, .12);
  color: var(--success);
  border-color: rgba(16, 185, 129, .3);
}

.model-badge.featured {
  background: rgba(37, 99, 235, .12);
  color: var(--accent);
  border-color: rgba(37, 99, 235, .3);
}

.model-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 180px;
}

/* Layout */
.model-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 24px;
  align-items: start;
}

.model-main { min-width: 0; }

.model-section {
  margin-bottom: 28px;
}

.model-section-title {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 14px;
}

.model-description {
  font-size: 14px;
  line-height: 1.7;
  color: var(--text);
}

.capabilities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 10px;
}

.capability-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
}

.capability-icon {
  width: 30px;
  height: 30px;
  border-radius: 7px;
  background: var(--gray-deep);
  color: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

[data-theme="dark"] .capability-icon { background: var(--text-strong); color: var(--bg); }

.capability-icon .material-icons-round { font-size: 16px; }

.capability-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
}

.capability-desc {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 1px;
}

/* Code example */
.code-example {
  background: #0A0A0A;
  border: 1px solid #1F2937;
  border-radius: 12px;
  overflow: hidden;
  margin-top: 12px;
}

[data-theme="dark"] .code-example { background: #000; border-color: #1F2937; }

.code-example-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: #111111;
  border-bottom: 1px solid #1F2937;
}

.code-example-tabs { display: flex; gap: 4px; }

.code-example-tab {
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 500;
  color: #9CA3AF;
  border-radius: 4px;
  cursor: pointer;
  background: transparent;
  border: none;
}

.code-example-tab.active {
  background: #1F2937;
  color: #F9FAFB;
}

.code-example-copy {
  padding: 4px 10px;
  font-size: 11px;
  color: #9CA3AF;
  border: 1px solid #1F2937;
  border-radius: 5px;
  background: transparent;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.code-example-copy:hover { color: white; border-color: #374151; }
.code-example-copy .material-icons-round { font-size: 12px; }

.code-example-body {
  padding: 18px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  line-height: 1.7;
  color: #D1D5DB;
  overflow-x: auto;
  white-space: pre;
}

.code-example-body .kw { color: #93C5FD; }
.code-example-body .str { color: #86EFAC; }
.code-example-body .com { color: #6B7280; font-style: italic; }
.code-example-body .var { color: #FCD34D; }
.code-example-body .fn { color: #F0ABFC; }

/* Sidebar */
.model-sidebar {
  position: sticky;
  top: 80px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.pricing-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 22px;
}

.pricing-card-title {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 14px;
}

.pricing-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 0;
  border-bottom: 1px solid var(--border);
}

.pricing-row:first-of-type { padding-top: 0; }
.pricing-row:last-of-type { border-bottom: none; padding-bottom: 0; }

.pricing-label-strong {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
}

.pricing-label-meta {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 2px;
}

.pricing-value-block { text-align: right; }

.pricing-value-uzs {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-strong);
  font-family: 'JetBrains Mono', monospace;
}

.pricing-value-uzs.free { color: var(--success); }

.pricing-value-currency {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 500;
  margin-left: 2px;
  font-family: 'Inter', sans-serif;
}

.pricing-value-usd {
  font-size: 11px;
  color: var(--text-subtle);
  font-family: 'JetBrains Mono', monospace;
  margin-top: 2px;
}

/* Spec card */
.spec-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
}

.spec-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid var(--border);
  font-size: 13px;
}

.spec-row:first-of-type { padding-top: 0; }
.spec-row:last-of-type { border-bottom: none; padding-bottom: 0; }

.spec-key { color: var(--text-muted); }
.spec-value { color: var(--text-strong); font-weight: 600; }
.spec-value.success { color: var(--success); }

/* CTA / Key creation block */
.cta-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 20px;
}

.cta-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.cta-desc {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.5;
  margin-bottom: 14px;
}

.balance-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 12px;
  margin-bottom: 12px;
}

.balance-line .label { color: var(--text-muted); }
.balance-line .value {
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  color: var(--text-strong);
}

/* New key alert */
.new-key-alert {
  background: var(--bg-elevated);
  border: 2px solid var(--success);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 20px;
}

.new-key-alert-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.new-key-alert-icon {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  background: rgba(16, 185, 129, .15);
  color: var(--success);
  display: flex;
  align-items: center;
  justify-content: center;
}

.new-key-alert-icon .material-icons-round { font-size: 16px; }

.new-key-alert-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-strong);
}

.new-key-alert-desc {
  font-size: 11px;
  color: var(--text-muted);
  line-height: 1.5;
  margin-top: 2px;
}

.new-key-display {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--text-strong);
  word-break: break-all;
}

.new-key-copy {
  margin-left: auto;
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.new-key-copy:hover { color: var(--text-strong); border-color: var(--border-strong); }
.new-key-copy .material-icons-round { font-size: 14px; }

/* Related */
.related-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 10px;
}

.related-tile {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  transition: all .15s;
}

.related-tile:hover {
  border-color: var(--text-muted);
  transform: translateY(-1px);
}

.related-tile-logo {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5px;
  flex-shrink: 0;
}

.related-tile-logo svg { width: 100%; height: 100%; }

.related-tile-name {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-strong);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.related-tile-meta {
  font-size: 10px;
  color: var(--text-muted);
  margin-top: 1px;
}

/* Modal */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0, 0, 0, .55);
  z-index: 100;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.modal-overlay.open { display: flex; }

.modal {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px;
  width: 100%;
  max-width: 440px;
  box-shadow: var(--shadow-lg);
  animation: modalIn .25s var(--ease-spring) both;
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(.95) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-strong);
  margin-bottom: 18px;
}

.modal-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  margin-top: 18px;
}

@media (max-width: 900px) {
  .model-layout { grid-template-columns: 1fr; }
  .model-sidebar { position: static; }
  .model-header { flex-direction: column; }
  .model-actions { width: 100%; }
}
</style>
@endpush

@section('content')

@guest
<header class="landing-nav">
  <div class="landing-nav-inner">
    <a href="{{ route('home') }}" class="brand">
      <div class="brand-mark">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="30" height="25" fill="currentColor">
          <rect x="0" y="0" width="36" height="36" rx="8"/>
          <rect x="0" y="82" width="36" height="36" rx="8"/>
          <rect x="0" y="164" width="36" height="36" rx="8"/>
          <path d="M 36 18 C 90 18, 110 60, 135 90" stroke="currentColor" stroke-width="14" fill="none" stroke-linecap="round"/>
          <path d="M 36 182 C 90 182, 110 140, 135 110" stroke="currentColor" stroke-width="14" fill="none" stroke-linecap="round"/>
          <rect x="36" y="93" width="100" height="14" rx="3"/>
          <rect x="130" y="65" width="70" height="70" rx="14"/>
          <line x1="200" y1="100" x2="230" y2="100" stroke="currentColor" stroke-width="10" stroke-linecap="round"/>
          <polygon points="225,90 240,100 225,110"/>
        </svg>
      </div>
      <span>CloudAPI</span>
    </a>
    <nav class="nav-links" style="margin-left:48px;flex:1">
      <a href="{{ route('models.index') }}" class="nav-link active">{{ __('landing.nav.models') }}</a>
      <a href="{{ route('pricing') }}" class="nav-link">{{ __('landing.nav.pricing') }}</a>
      <a href="{{ route('docs') }}" class="nav-link">{{ __('landing.nav.docs') }}</a>
    </nav>
    <div class="topbar-actions">
      @include('partials.lang-switcher')
      <button class="icon-btn" onclick="toggleTheme()"><span class="material-icons-round" id="themeIcon">dark_mode</span></button>
      <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">{{ __('landing.nav.sign_in') }}</a>
      <a href="{{ route('register') }}" class="btn btn-primary btn-sm">{{ __('landing.nav.get_started') }}</a>
    </div>
  </div>
</header>
<div style="height:64px"></div>
@endguest

@php
  $provider = explode('/', $model->model_id)[0];
  $finalInput = $model->cost_input_usd * (1 + $model->margin_percent / 100);
  $finalOutput = $model->cost_output_usd * (1 + $model->margin_percent / 100);
  $inputUzs = $finalInput * $model->usd_to_uzs;
  $outputUzs = $finalOutput * $model->usd_to_uzs;
@endphp

<div class="model-page">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="{{ route('home') }}">CloudAPI</a>
    <span class="material-icons-round">chevron_right</span>
    <a href="{{ route('models.index') }}">{{ __('models.title') }}</a>
    <span class="material-icons-round">chevron_right</span>
    <span style="color:var(--text-strong)">{{ $model->display_name }}</span>
  </div>

  <!-- Header -->
  <div class="model-header">
    <div class="model-icon">
      @include('models.partials.logo', ['modelId' => $model->model_id])
    </div>
    <div class="model-info">
      <div class="model-display-name">{{ $model->display_name }}</div>
      <div class="model-id-pill" onclick="navigator.clipboard.writeText('{{ $model->model_id }}'); this.querySelector('.material-icons-round').textContent='check'; setTimeout(()=>this.querySelector('.material-icons-round').textContent='content_copy',1500)">
        {{ $model->model_id }}
        <span class="material-icons-round">content_copy</span>
      </div>

      <div class="model-badges">
        @if($model->is_free)
          <span class="model-badge free">
            <span class="material-icons-round">free_breakfast</span>
            {{ __('models.pricing.free') }}
          </span>
        @endif
        @if($model->is_featured)
          <span class="model-badge featured">
            <span class="material-icons-round">star</span>
            {{ __('models.filters.featured') }}
          </span>
        @endif
        @if($model->category)
          <span class="model-badge">{{ ucfirst($model->category) }}</span>
        @endif
        @if(is_array($model->capabilities))
          @foreach($model->capabilities as $cap)
            <span class="model-badge">{{ str_replace('_', ' ', $cap) }}</span>
          @endforeach
        @endif
      </div>
    </div>

    <div class="model-actions">
      @auth
        <button class="btn btn-primary" onclick="document.getElementById('createKeyModal').classList.add('open')">
          <span class="material-icons-round">key</span>
          {{ __('keys.create') }}
        </button>
        <a href="{{ route('playground.index') }}?model={{ urlencode($model->model_id) }}" class="btn btn-secondary">
          <span class="material-icons-round">play_arrow</span>
          {{ __('models.details.try_playground') }}
        </a>
      @else
        <a href="{{ route('register') }}" class="btn btn-primary">
          <span class="material-icons-round">arrow_forward</span>
          {{ __('models.details.use_api') }}
        </a>
        <a href="{{ route('login') }}" class="btn btn-secondary">
          {{ __('landing.nav.sign_in') }}
        </a>
      @endauth
    </div>
  </div>

  <!-- New key alert -->
  @if(session('new_key'))
  <div class="new-key-alert">
    <div class="new-key-alert-header">
      <div class="new-key-alert-icon">
        <span class="material-icons-round">check</span>
      </div>
      <div>
        <div class="new-key-alert-title">{{ __('keys.created_success') }}</div>
        <div class="new-key-alert-desc">{{ __('keys.created_warning') }}</div>
      </div>
    </div>
    <div class="new-key-display">
      <span>{{ session('new_key') }}</span>
      <button class="new-key-copy" onclick="navigator.clipboard.writeText('{{ session('new_key') }}');this.querySelector('.material-icons-round').textContent='check';setTimeout(()=>this.querySelector('.material-icons-round').textContent='content_copy',1500)">
        <span class="material-icons-round">content_copy</span>
      </button>
    </div>
  </div>
  @endif

  <div class="model-layout">
    <!-- Main -->
    <main class="model-main">
      @if($model->description)
      <section class="model-section">
        <div class="model-section-title">{{ __('models.details.overview') }}</div>
        <div class="model-description">{{ $model->description }}</div>
      </section>
      @endif

      @if(is_array($model->capabilities) && count($model->capabilities))
      <section class="model-section">
        <div class="model-section-title">{{ __('models.details.capabilities') }}</div>
        <div class="capabilities-grid">
          @foreach($model->capabilities as $cap)
          @php
            $capInfo = [
              'vision' => ['icon' => 'visibility', 'desc' => 'Image input support'],
              'tools' => ['icon' => 'build', 'desc' => 'Function/tool calling'],
              'json_mode' => ['icon' => 'code', 'desc' => 'Structured output'],
              'reasoning' => ['icon' => 'psychology', 'desc' => 'Step-by-step thinking'],
            ][$cap] ?? ['icon' => 'check', 'desc' => ''];
          @endphp
          <div class="capability-card">
            <div class="capability-icon">
              <span class="material-icons-round">{{ $capInfo['icon'] }}</span>
            </div>
            <div>
              <div class="capability-name">{{ ucfirst(str_replace('_', ' ', $cap)) }}</div>
              <div class="capability-desc">{{ $capInfo['desc'] }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </section>
      @endif

      <!-- Code Examples -->
      <section class="model-section">
        <div class="model-section-title">{{ app()->getLocale() === 'uz' ? "Foydalanish namunasi" : (app()->getLocale() === 'ru' ? 'Пример использования' : 'Code example') }}</div>
        <div class="code-example">
          <div class="code-example-header">
            <div class="code-example-tabs">
              <button class="code-example-tab active" data-lang="curl">curl</button>
              <button class="code-example-tab" data-lang="python">Python</button>
              <button class="code-example-tab" data-lang="node">Node.js</button>
            </div>
            <button class="code-example-copy" onclick="copyExampleCode(this)">
              <span class="material-icons-round">content_copy</span>
              <span class="copy-label">Copy</span>
            </button>
          </div>
          <div class="code-example-body" id="exampleBlock"></div>
        </div>
      </section>

      @if($related->count())
      <section class="model-section">
        <div class="model-section-title">{{ __('models.details.related') }}</div>
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">{{ __('models.details.related_subtitle') }}</p>
        <div class="related-grid">
          @foreach($related as $rel)
          <a href="{{ route('models.show', $rel->model_id) }}" class="related-tile">
            <div class="related-tile-logo">
              @include('models.partials.logo', ['modelId' => $rel->model_id])
            </div>
            <div style="flex:1;min-width:0">
              <div class="related-tile-name">{{ $rel->display_name }}</div>
              <div class="related-tile-meta">
                @if($rel->is_free)
                  {{ __('models.pricing.free') }}
                @else
                  {{ number_format($rel->cost_input_usd * (1 + $rel->margin_percent / 100) * $rel->usd_to_uzs, 0, '.', ' ') }} so'm/M
                @endif
              </div>
            </div>
          </a>
          @endforeach
        </div>
      </section>
      @endif
    </main>

    <!-- Sidebar -->
    <aside class="model-sidebar">
      @auth
      <!-- CTA -->
      <div class="cta-card">
        <div class="cta-title">{{ app()->getLocale() === 'uz' ? "Ushbu modelni ishlatish" : (app()->getLocale() === 'ru' ? 'Использовать модель' : 'Use this model') }}</div>
        <div class="cta-desc">{{ app()->getLocale() === 'uz' ? "API kalit yarating va so'rovlarni boshlang. Tokenlar balansingizdan yechiladi." : (app()->getLocale() === 'ru' ? 'Создайте API ключ и начните делать запросы. Токены списываются с баланса.' : 'Create an API key and start making requests. Tokens deducted from your balance.') }}</div>

        <div class="balance-line">
          <span class="label">{{ __('common.balance') }}</span>
          <span class="value">{{ number_format(auth()->user()->wallet?->balance_uzs ?? 0, 0, '.', ' ') }} {{ __('common.currency') }}</span>
        </div>

        <button class="btn btn-primary w-full" onclick="document.getElementById('createKeyModal').classList.add('open')">
          <span class="material-icons-round">add</span>
          {{ __('keys.create') }}
        </button>
      </div>
      @endauth

      <!-- Pricing -->
      <div class="pricing-card">
        <div class="pricing-card-title">{{ __('models.details.pricing') }}</div>

        <div class="pricing-row">
          <div>
            <div class="pricing-label-strong">{{ __('models.pricing.input') }}</div>
            <div class="pricing-label-meta">{{ __('models.pricing.per_million') }}</div>
          </div>
          <div class="pricing-value-block">
            @if($model->is_free)
              <div class="pricing-value-uzs free">Free</div>
            @else
              <div class="pricing-value-uzs">
                {{ number_format($inputUzs, 0, '.', ' ') }}
                <span class="pricing-value-currency">{{ __('common.currency') }}</span>
              </div>
              <div class="pricing-value-usd">${{ number_format($finalInput, 4) }}</div>
            @endif
          </div>
        </div>

        <div class="pricing-row">
          <div>
            <div class="pricing-label-strong">{{ __('models.pricing.output') }}</div>
            <div class="pricing-label-meta">{{ __('models.pricing.per_million') }}</div>
          </div>
          <div class="pricing-value-block">
            @if($model->is_free)
              <div class="pricing-value-uzs free">Free</div>
            @else
              <div class="pricing-value-uzs">
                {{ number_format($outputUzs, 0, '.', ' ') }}
                <span class="pricing-value-currency">{{ __('common.currency') }}</span>
              </div>
              <div class="pricing-value-usd">${{ number_format($finalOutput, 4) }}</div>
            @endif
          </div>
        </div>
      </div>

      <!-- Specs -->
      <div class="spec-card">
        <div class="pricing-card-title">Specifications</div>
        <div class="spec-row">
          <span class="spec-key">Provider</span>
          <span class="spec-value">{{ ucfirst($provider) }}</span>
        </div>
        @if($model->context_length)
        <div class="spec-row">
          <span class="spec-key">{{ __('models.details.context') }}</span>
          <span class="spec-value">{{ number_format($model->context_length) }}</span>
        </div>
        @endif
        <div class="spec-row">
          <span class="spec-key">Category</span>
          <span class="spec-value">{{ ucfirst($model->category) }}</span>
        </div>
        @if(is_array($model->capabilities))
        <div class="spec-row">
          <span class="spec-key">Features</span>
          <span class="spec-value">{{ count($model->capabilities) }}</span>
        </div>
        @endif
        <div class="spec-row">
          <span class="spec-key">Status</span>
          <span class="spec-value success">● Active</span>
        </div>
      </div>
    </aside>
  </div>
</div>

@auth
<!-- Create Key Modal -->
<div class="modal-overlay" id="createKeyModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title">{{ __('keys.create_modal.title') }}</div>
    <form action="{{ route('keys.store') }}" method="POST">
      @csrf
      <div class="field">
        <label class="label">{{ __('keys.create_modal.name') }}</label>
        <input type="text" name="name" class="input"
               placeholder="{{ __('keys.create_modal.name_placeholder') }}"
               value="{{ $model->display_name }}" required autofocus>
        <div class="help-text">{{ __('keys.create_modal.help') }}</div>
      </div>

      <div style="font-size:12px;color:var(--text-muted);padding:10px;background:var(--bg-subtle);border-radius:8px;margin-bottom:12px">
        <strong style="color:var(--text-strong)">{{ app()->getLocale() === 'uz' ? "Eslatma:" : (app()->getLocale() === 'ru' ? 'Заметка:' : 'Note:') }}</strong>
        {{ app()->getLocale() === 'uz' ? "API kalitlar hamyon balansidan ishlaydi. Alohida balans qo'shish shart emas." : (app()->getLocale() === 'ru' ? 'API ключи работают с балансом кошелька. Отдельный баланс не нужен.' : 'API keys use your wallet balance. No separate balance needed.') }}
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('createKeyModal').classList.remove('open')">
          {{ __('keys.create_modal.cancel') }}
        </button>
        <button type="submit" class="btn btn-primary">
          <span class="material-icons-round">add</span>
          {{ __('keys.create_modal.create') }}
        </button>
      </div>
    </form>
  </div>
</div>
@endauth

@endsection

@push('scripts')
<script>
(function() {
  const examples = {
    curl: `<span class="com"># OpenAI-compatible request</span>
curl https://api.cloudapi.uz/v1/chat/completions \\
  -H <span class="str">"Authorization: Bearer cap-..."</span> \\
  -H <span class="str">"Content-Type: application/json"</span> \\
  -d <span class="str">'{
    "model": "{{ $model->model_id }}",
    "messages": [
      {"role": "user", "content": "Salom!"}
    ]
  }'</span>`,

    python: `<span class="kw">from</span> openai <span class="kw">import</span> OpenAI

client = <span class="fn">OpenAI</span>(
    api_key=<span class="str">"cap-..."</span>,
    base_url=<span class="str">"https://api.cloudapi.uz/v1"</span>
)

response = client.chat.completions.<span class="fn">create</span>(
    model=<span class="str">"{{ $model->model_id }}"</span>,
    messages=[
        {<span class="str">"role"</span>: <span class="str">"user"</span>, <span class="str">"content"</span>: <span class="str">"Salom!"</span>}
    ]
)

<span class="fn">print</span>(response.choices[<span class="var">0</span>].message.content)`,

    node: `<span class="kw">import</span> OpenAI <span class="kw">from</span> <span class="str">'openai'</span>;

<span class="kw">const</span> client = <span class="kw">new</span> <span class="fn">OpenAI</span>({
  apiKey: <span class="str">'cap-...'</span>,
  baseURL: <span class="str">'https://api.cloudapi.uz/v1'</span>,
});

<span class="kw">const</span> response = <span class="kw">await</span> client.chat.completions.<span class="fn">create</span>({
  model: <span class="str">'{{ $model->model_id }}'</span>,
  messages: [{ role: <span class="str">'user'</span>, content: <span class="str">'Salom!'</span> }],
});

console.<span class="fn">log</span>(response.choices[<span class="var">0</span>].message.content);`,
  };

  const block = document.getElementById('exampleBlock');
  const tabs = document.querySelectorAll('.code-example-tab');

  function setLang(lang) {
    tabs.forEach(t => t.classList.toggle('active', t.dataset.lang === lang));
    block.innerHTML = examples[lang];
  }

  tabs.forEach(t => t.addEventListener('click', () => setLang(t.dataset.lang)));
  setLang('curl');
})();

function copyExampleCode(btn) {
  const code = document.getElementById('exampleBlock').textContent;
  navigator.clipboard.writeText(code);
  const label = btn.querySelector('.copy-label');
  const icon = btn.querySelector('.material-icons-round');
  const oldLabel = label.textContent;
  label.textContent = 'Copied!';
  icon.textContent = 'check';
  setTimeout(() => {
    label.textContent = oldLabel;
    icon.textContent = 'content_copy';
  }, 1500);
}
</script>
@endpush