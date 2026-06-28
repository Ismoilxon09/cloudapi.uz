@extends('layouts.app')

@section('title', __('billing.title') . ' — CloudAPI')

@push('styles')
<style>
.billing-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 32px 24px;
}

.billing-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}

.billing-title {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.billing-subtitle {
  font-size: 13px;
  color: var(--text-muted);
}

/* Stats */
.billing-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 14px;
  margin-bottom: 28px;
}

.billing-stat {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 22px;
  position: relative;
}

.billing-stat.primary {
  background: var(--primary);
  color: var(--bg-elevated);
  border-color: var(--primary);
}

.billing-stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  background: var(--bg-subtle);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14px;
}

.billing-stat.primary .billing-stat-icon {
  background: rgba(255, 255, 255, .12);
}

.billing-stat-icon .material-icons-round {
  font-size: 18px;
  color: var(--text-muted);
}

.billing-stat.primary .billing-stat-icon .material-icons-round {
  color: rgba(255, 255, 255, .9);
}

.billing-stat-label {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 6px;
}

.billing-stat.primary .billing-stat-label {
  color: rgba(255, 255, 255, .65);
}

.billing-stat-value {
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -0.02em;
  font-family: 'JetBrains Mono', monospace;
}

.billing-stat-currency {
  font-size: 14px;
  font-weight: 600;
  opacity: 0.7;
  margin-left: 4px;
}

.billing-stat-action {
  margin-top: 14px;
}

/* Transactions */
.tx-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
}

.tx-card-header {
  padding: 18px 20px;
  border-bottom: 1px solid var(--border);
}

.tx-card-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-strong);
}

.tx-card-subtitle {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 2px;
}

.tx-table {
  width: 100%;
  border-collapse: collapse;
}

.tx-table th {
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 12px 18px;
  border-bottom: 1px solid var(--border);
  background: var(--bg-subtle);
}

.tx-table td {
  padding: 14px 18px;
  border-bottom: 1px solid var(--border);
  font-size: 13px;
}

.tx-table tr:last-child td { border-bottom: none; }
.tx-table tr:hover td { background: var(--bg-subtle); }

.tx-type {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  font-size: 11px;
  font-weight: 600;
  border-radius: 99px;
  background: var(--bg-subtle);
  color: var(--text-muted);
}

.tx-type.deposit { background: rgba(16, 185, 129, .1); color: var(--success); }
.tx-type.usage { background: rgba(107, 114, 128, .15); color: var(--text-muted); }
.tx-type.refund { background: rgba(37, 99, 235, .1); color: var(--accent); }
.tx-type.bonus { background: rgba(245, 158, 11, .1); color: var(--warning); }

.tx-type .material-icons-round { font-size: 11px; }

.tx-amount {
  font-family: 'JetBrains Mono', monospace;
  font-weight: 700;
}

.tx-amount.positive { color: var(--success); }
.tx-amount.negative { color: var(--text-strong); }

.tx-status {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 99px;
}

.tx-status.completed { background: rgba(16, 185, 129, .1); color: var(--success); }
.tx-status.pending { background: rgba(245, 158, 11, .1); color: var(--warning); }
.tx-status.failed { background: rgba(239, 68, 68, .1); color: var(--danger); }

.tx-date {
  font-size: 12px;
  color: var(--text-muted);
  font-family: 'JetBrains Mono', monospace;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-muted);
}

.empty-state .material-icons-round {
  font-size: 40px;
  color: var(--text-subtle);
  margin-bottom: 12px;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.empty-state p { font-size: 12px; }

/* ============ DUAL WALLET ============ */
.dual-wallet {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 14px;
  margin-bottom: 20px;
}

.wallet-card {
  border-radius: 14px;
  padding: 18px;
  position: relative;
  overflow: hidden;
  transition: transform .15s, box-shadow .15s;
}

.wallet-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.wallet-main {
  background: linear-gradient(135deg, #0A0A0A 0%, #2563EB 100%);
  color: white;
}

.wallet-bonus {
  background: linear-gradient(135deg, #7C3AED 0%, #EC4899 100%);
  color: white;
}

.wallet-total {
  background: var(--bg-elevated);
  color: var(--text-strong);
  border: 1px solid var(--border);
}

.wallet-card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.wallet-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(255,255,255,.18);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.wallet-total .wallet-icon {
  background: var(--bg-subtle);
}

.wallet-icon .material-icons-round { font-size: 18px; }

.wallet-card-label {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  opacity: 0.85;
}

.wallet-amount {
  font-size: 32px;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 14px;
  line-height: 1.1;
}

.wallet-currency {
  font-size: 14px;
  font-weight: 600;
  opacity: 0.85;
  margin-left: 2px;
}

.wallet-card-footer {
  margin-top: auto;
}

.wallet-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 13px;
  background: rgba(255,255,255,.2);
  color: white;
  border: 1px solid rgba(255,255,255,.25);
  border-radius: 8px;
  font-size: 12.5px;
  font-weight: 600;
  text-decoration: none;
  transition: all .15s;
}

.wallet-action-btn:hover {
  background: rgba(255,255,255,.3);
  transform: translateY(-1px);
}

.wallet-action-btn .material-icons-round { font-size: 15px; }

.wallet-total .wallet-action-btn {
  background: transparent;
  color: inherit;
  border: none;
  padding: 0;
}

.wallet-info-small {
  display: flex;
  flex-direction: column;
  gap: 5px;
  font-size: 11.5px;
  color: var(--text-muted);
}

.wallet-info-small > div {
  display: flex;
  align-items: center;
  gap: 5px;
}

.wallet-info-small .material-icons-round { font-size: 14px; }

/* Bonus info banner */
.bonus-info-banner {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 14px 16px;
  background: rgba(124, 58, 237, .06);
  border: 1px solid rgba(124, 58, 237, .15);
  border-radius: 12px;
  margin-bottom: 24px;
  font-size: 13px;
  line-height: 1.55;
  color: var(--text);
}

.bonus-info-banner .material-icons-round {
  color: #7C3AED;
  font-size: 22px;
  flex-shrink: 0;
}

.bonus-info-banner strong { color: var(--text-strong); }

.bonus-info-text { color: var(--text-muted); }

.bonus-info-banner a {
  color: #7C3AED;
  font-weight: 700;
  text-decoration: none;
}

.bonus-info-banner a:hover { text-decoration: underline; }

@media (max-width: 900px) {
  .dual-wallet { grid-template-columns: 1fr 1fr; }
  .wallet-total { grid-column: 1 / -1; }
}

@media (max-width: 540px) {
  .dual-wallet { grid-template-columns: 1fr; }
  .wallet-total { grid-column: auto; }
  .wallet-amount { font-size: 28px; }
}
</style>
@endpush

@section('content')

<div class="billing-page">
  <div class="billing-header">
    <div>
      <h1 class="billing-title">{{ __('billing.title') }}</h1>
      <p class="billing-subtitle">{{ __('billing.subtitle') }}</p>
    </div>
    <a href="{{ route('billing.topup') }}" class="btn btn-primary">
      <span class="material-icons-round">add</span>
      {{ __('billing.add_funds') }}
    </a>
  </div>

  <!-- Dual Wallet -->
  <div class="dual-wallet">
    <!-- Main wallet -->
    <div class="wallet-card wallet-main">
      <div class="wallet-card-header">
        <div class="wallet-icon">
          <span class="material-icons-round">account_balance_wallet</span>
        </div>
        <div class="wallet-card-label">Asosiy hamyon</div>
      </div>
      <div class="wallet-amount">
        {{ number_format($wallet?->balance_uzs ?? 0, 0, '.', ' ') }}
        <span class="wallet-currency">so'm</span>
      </div>
      <div class="wallet-card-footer">
        <a href="{{ route('billing.topup') }}" class="wallet-action-btn">
          <span class="material-icons-round">add</span>
          To'ldirish
        </a>
      </div>
    </div>

    <!-- Bonus wallet -->
    <div class="wallet-card wallet-bonus">
      <div class="wallet-card-header">
        <div class="wallet-icon">
          <span class="material-icons-round">card_giftcard</span>
        </div>
        <div class="wallet-card-label">Bonus hamyon</div>
      </div>
      <div class="wallet-amount">
        {{ number_format($wallet?->bonus_balance_uzs ?? 0, 0, '.', ' ') }}
        <span class="wallet-currency">GP</span>
      </div>
      <div class="wallet-card-footer">
        @if(auth()->user()->telegram_id)
          <a href="https://t.me/{{ env('TELEGRAM_BOT_USERNAME', 'cloudapiuzbot') }}" target="_blank" class="wallet-action-btn">
            <span class="material-icons-round">task_alt</span>
            Vazifalar
          </a>
        @else
          <a href="{{ route('telegram.login') }}" class="wallet-action-btn">
            <span class="material-icons-round">link</span>
            Telegram ulash
          </a>
        @endif
      </div>
    </div>

    <!-- Total -->
    <div class="wallet-card wallet-total">
      <div class="wallet-card-header">
        <div class="wallet-icon">
          <span class="material-icons-round">savings</span>
        </div>
        <div class="wallet-card-label">Jami balans</div>
      </div>
      <div class="wallet-amount">
        {{ number_format(($wallet?->balance_uzs ?? 0) + ($wallet?->bonus_balance_uzs ?? 0), 0, '.', ' ') }}
        <span class="wallet-currency">so'm</span>
      </div>
      <div class="wallet-card-footer">
        <div class="wallet-info-small">
          <div>
            <span class="material-icons-round">trending_up</span>
            Kirim: {{ number_format($wallet?->total_deposited ?? 0, 0, '.', ' ') }} so'm
          </div>
          <div>
            <span class="material-icons-round">trending_down</span>
            Sarf: {{ number_format($wallet?->total_spent ?? 0, 0, '.', ' ') }} so'm
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Info banner about bonus -->
  <div class="bonus-info-banner">
    <span class="material-icons-round">lightbulb</span>
    <div>
      <strong>Bonus hamyon nima?</strong><br>
      <span class="bonus-info-text">
        1 GP = 1 so'm. Vazifalar, referral va kunlik bonuslardan to'planadi.
        API ishlatganda <strong>avval bonus hamyon</strong>dan sarflanadi.
        Telegram bot orqali ko'paytirib boring 👉
        <a href="https://t.me/cloudapiuzbot" target="_blank">cloudapiuzbot</a>
      </span>
    </div>
  </div>

  <!-- Transactions -->
  <div class="tx-card">
    <div class="tx-card-header">
      <div class="tx-card-title">{{ __('billing.history.title') }}</div>
      <div class="tx-card-subtitle">{{ __('billing.history.subtitle') }}</div>
    </div>

    @if(!isset($transactions) || $transactions->isEmpty())
      <div class="empty-state">
        <span class="material-icons-round">receipt_long</span>
        <h3>{{ __('billing.history.empty') }}</h3>
        <p>{{ __('billing.subtitle') }}</p>
      </div>
    @else
      <table class="tx-table">
        <thead>
          <tr>
            <th>{{ __('billing.history.type') }}</th>
            <th>{{ __('billing.history.description') }}</th>
            <th>{{ __('billing.history.method') }}</th>
            <th>{{ __('billing.history.amount') }}</th>
            <th>{{ __('billing.history.balance_after') }}</th>
            <th>{{ __('billing.history.status') }}</th>
            <th>{{ __('billing.history.date') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($transactions as $tx)
          <tr>
            <td>
              @php
                $typeIcons = [
                  'deposit' => 'arrow_downward',
                  'withdrawal' => 'arrow_upward',
                  'usage' => 'data_usage',
                  'refund' => 'undo',
                  'bonus' => 'card_giftcard',
                  'transfer' => 'swap_horiz',
                ];
                $icon = $typeIcons[$tx->type] ?? 'circle';
              @endphp
              <span class="tx-type {{ $tx->type }}">
                <span class="material-icons-round">{{ $icon }}</span>
                {{ __('billing.types.' . $tx->type) }}
              </span>
            </td>
            <td style="color:var(--text-muted)">{{ $tx->description ?? '—' }}</td>
            <td style="color:var(--text-muted);font-size:12px">{{ $tx->payment_method ? ucfirst($tx->payment_method) : '—' }}</td>
            <td>
              <span class="tx-amount {{ $tx->amount_uzs > 0 ? 'positive' : 'negative' }}">
                {{ $tx->amount_uzs > 0 ? '+' : '' }}{{ number_format($tx->amount_uzs, 2, '.', ' ') }} {{ __('common.currency') }}
              </span>
            </td>
            <td>
              <span class="tx-amount">{{ number_format($tx->balance_after ?? 0, 0, '.', ' ') }} {{ __('common.currency') }}</span>
            </td>
            <td>
              <span class="tx-status {{ $tx->status }}">
                {{ __('common.' . $tx->status) }}
              </span>
            </td>
            <td>
              <span class="tx-date">{{ $tx->created_at->format('M d, H:i') }}</span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      @if(method_exists($transactions, 'links'))
        <div style="padding:16px 20px;border-top:1px solid var(--border)">
          {{ $transactions->links('vendor.pagination.cloudapi') }}
        </div>
      @endif
    @endif
  </div>
</div>

@endsection