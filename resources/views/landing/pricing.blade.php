@extends('layouts.app')

@section('title', 'Pricing — CloudAPI')

@push('styles')
<style>
.pricing-hero {
  text-align: center;
  padding: 80px 32px 40px;
}

.pricing-hero h1 {
  font-size: 44px;
  font-weight: 800;
  letter-spacing: -0.03em;
  margin-bottom: 12px;
}

.pricing-hero p {
  font-size: 16px;
  color: var(--text-muted);
  max-width: 580px;
  margin: 0 auto;
}

.pricing-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 32px 80px;
}

.pricing-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 60px;
}

@media (max-width: 900px) {
  .pricing-grid { grid-template-columns: 1fr; }
}

.tier-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 32px 28px;
}

.tier-card.featured {
  border-color: var(--primary);
  position: relative;
}

.tier-badge {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--primary);
  color: var(--bg-elevated);
  font-size: 11px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 99px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.tier-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 8px;
}

.tier-price {
  font-size: 36px;
  font-weight: 800;
  letter-spacing: -0.03em;
  margin-bottom: 4px;
}

.tier-price-sub {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 24px;
}

.tier-features {
  list-style: none;
  margin-bottom: 24px;
}

.tier-features li {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 0;
  font-size: 13px;
}

.tier-features .material-icons-round {
  font-size: 16px;
  color: var(--success);
}

.pricing-models {
  margin-top: 60px;
}

.pricing-table {
  width: 100%;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
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
    <div class="nav-links" style="margin-left:48px;flex:1">
      <a href="{{ route('home') }}#features" class="nav-link">Features</a>
      <a href="{{ route('pricing') }}" class="nav-link active">Pricing</a>
      <a href="{{ route('docs') }}" class="nav-link">Documentation</a>
    </div>
    <div class="topbar-actions">
      <button class="icon-btn" onclick="toggleTheme()"><span class="material-icons-round" id="themeIcon">dark_mode</span></button>
      <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Sign in</a>
      <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get started</a>
    </div>
  </div>
</header>
@endguest

<div class="pricing-hero">
  <h1>Simple, transparent pricing</h1>
  <p>Pay only for what you use. No hidden fees, no surprises.</p>
</div>

<div class="pricing-container">
  <div class="pricing-grid">
    <div class="tier-card">
      <div class="tier-name">Free</div>
      <div class="tier-price">0 <span style="font-size:16px;color:var(--text-muted);font-weight:500">UZS</span></div>
      <div class="tier-price-sub">Perfect for testing</div>
      <ul class="tier-features">
        <li><span class="material-icons-round">check</span>Free models access</li>
        <li><span class="material-icons-round">check</span>100 requests/day</li>
        <li><span class="material-icons-round">check</span>Community support</li>
        <li><span class="material-icons-round">check</span>1 API key</li>
      </ul>
      <a href="{{ route('register') }}" class="btn btn-secondary w-full">Get started</a>
    </div>

    <div class="tier-card featured">
      <div class="tier-badge">Recommended</div>
      <div class="tier-name">Pay as you go</div>
      <div class="tier-price">Token <span style="font-size:16px;color:var(--text-muted);font-weight:500">pricing</span></div>
      <div class="tier-price-sub">OpenRouter price + 30% margin</div>
      <ul class="tier-features">
        <li><span class="material-icons-round">check</span>100+ models</li>
        <li><span class="material-icons-round">check</span>Unlimited requests</li>
        <li><span class="material-icons-round">check</span>Email support</li>
        <li><span class="material-icons-round">check</span>10 API keys</li>
        <li><span class="material-icons-round">check</span>Detailed analytics</li>
      </ul>
      <a href="{{ route('register') }}" class="btn btn-primary w-full">Start building</a>
    </div>

    <div class="tier-card">
      <div class="tier-name">Business</div>
      <div class="tier-price">Custom</div>
      <div class="tier-price-sub">For teams and enterprises</div>
      <ul class="tier-features">
        <li><span class="material-icons-round">check</span>Volume discounts</li>
        <li><span class="material-icons-round">check</span>Priority support</li>
        <li><span class="material-icons-round">check</span>Unlimited keys</li>
        <li><span class="material-icons-round">check</span>SLA guarantees</li>
        <li><span class="material-icons-round">check</span>Dedicated account manager</li>
      </ul>
      <a href="#" class="btn btn-secondary w-full">Contact sales</a>
    </div>
  </div>

  <div class="pricing-models">
    <h2 style="font-size:24px;font-weight:700;letter-spacing:-0.02em;margin-bottom:8px">Model pricing</h2>
    <p class="text-muted text-sm mb-4">Prices include our 30% margin. All amounts in UZS per 1M tokens.</p>

    <div class="pricing-table">
      <table class="table">
        <thead>
          <tr>
            <th>Model</th>
            <th>Input</th>
            <th>Output</th>
            <th>Context</th>
          </tr>
        </thead>
        <tbody>
          @foreach(\App\Models\AiModel::where('active', true)->orderBy('sort_order')->get() as $m)
          <tr>
            <td>
              <div class="font-semibold">{{ $m->display_name }}</div>
              <div class="text-xs text-muted">{{ $m->category }}</div>
            </td>
            <td class="text-sm">
              @if($m->is_free)
                <span class="badge badge-success">Free</span>
              @else
                {{ number_format($m->getFinalPriceInput() * $m->usd_to_uzs) }} UZS
              @endif
            </td>
            <td class="text-sm">
              @if($m->is_free)
                <span class="badge badge-success">Free</span>
              @else
                {{ number_format($m->getFinalPriceOutput() * $m->usd_to_uzs) }} UZS
              @endif
            </td>
            <td class="text-sm text-muted">{{ $m->context_length ? number_format($m->context_length) : '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection