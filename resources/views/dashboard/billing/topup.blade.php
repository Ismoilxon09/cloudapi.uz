@extends('layouts.app')

@section('title', __('billing.topup.title') . ' — CloudAPI')

@push('styles')
<style>
.topup-page {
  max-width: 720px;
  margin: 0 auto;
  padding: 32px 24px;
}

.topup-header { margin-bottom: 28px; }

.topup-title {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-strong);
  margin-bottom: 4px;
}

.topup-subtitle {
  font-size: 13px;
  color: var(--text-muted);
}

.topup-card {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 24px;
}

.section-label {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 10px;
}

/* Amount input */
.amount-input-wrap {
  position: relative;
  margin-bottom: 12px;
}

.amount-input {
  width: 100%;
  padding: 18px 16px 18px 16px;
  font-size: 24px;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 10px;
  outline: none;
  transition: all .15s;
}

.amount-input:focus {
  border-color: var(--accent);
  background: var(--bg-elevated);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.amount-currency {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 600;
}

.help-text {
  font-size: 11px;
  color: var(--text-subtle);
}

/* Quick amounts */
.quick-amounts {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  margin-bottom: 24px;
}

.quick-amount {
  padding: 10px 8px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
  cursor: pointer;
  transition: all .15s;
}

.quick-amount:hover {
  border-color: var(--text-muted);
  background: var(--bg-subtle);
}

.quick-amount.active {
  border-color: var(--primary);
  background: var(--primary);
  color: var(--bg-elevated);
}

/* Methods */
.methods-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 24px;
}

.method-option {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  cursor: pointer;
  transition: all .15s;
}

.method-option:hover {
  border-color: var(--text-muted);
  background: var(--bg-subtle);
}

.method-option.active {
  border-color: var(--primary);
  background: var(--bg-subtle);
}

.method-option.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.method-option input { accent-color: var(--primary); flex-shrink: 0; }

.method-content { flex: 1; }

.method-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-strong);
  margin-bottom: 2px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.method-badge {
  font-size: 9px;
  padding: 2px 6px;
  border-radius: 99px;
  background: var(--bg-subtle);
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.method-badge.coming-soon {
  background: rgba(245, 158, 11, .12);
  color: var(--warning);
}

.method-badge.recommended {
  background: rgba(16, 185, 129, .12);
  color: var(--success);
}

.method-desc {
  font-size: 12px;
  color: var(--text-muted);
}

.method-icon {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.method-icon .material-icons-round {
  font-size: 18px;
  color: var(--text-strong);
}

/* Card info block (manual) */
.card-info-block {
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 16px;
}

.card-info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  font-size: 13px;
}

.card-info-row + .card-info-row { border-top: 1px solid var(--border); }

.card-info-label {
  color: var(--text-muted);
}

.card-info-value {
  font-family: 'JetBrains Mono', monospace;
  font-weight: 700;
  color: var(--text-strong);
  display: flex;
  align-items: center;
  gap: 6px;
}

.copy-btn {
  background: transparent;
  border: 1px solid var(--border);
  width: 24px;
  height: 24px;
  border-radius: 5px;
  cursor: pointer;
  color: var(--text-muted);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.copy-btn:hover { color: var(--text-strong); border-color: var(--border-strong); }
.copy-btn .material-icons-round { font-size: 12px; }

/* File upload */
.file-upload {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border: 2px dashed var(--border);
  border-radius: 10px;
  cursor: pointer;
  transition: all .15s;
  background: var(--bg-elevated);
}

.file-upload:hover {
  border-color: var(--text-muted);
  background: var(--bg-subtle);
}

.file-upload.has-file {
  border-style: solid;
  border-color: var(--success);
  background: rgba(16, 185, 129, .05);
}

.file-upload-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--bg-subtle);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.file-upload.has-file .file-upload-icon {
  background: rgba(16, 185, 129, .12);
  color: var(--success);
}

.file-upload-text { flex: 1; }

.file-upload-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-strong);
}

.file-upload-help {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 2px;
}

.file-upload input { display: none; }
</style>
@endpush

@section('content')

<div class="topup-page">
  <div class="topup-header">
    <h1 class="topup-title">{{ __('billing.topup.title') }}</h1>
    <p class="topup-subtitle">{{ __('billing.topup.subtitle') }}</p>
  </div>

  @if(session('info'))
    <div class="alert alert-info">
      <span class="material-icons-round">info</span>
      <div>{{ session('info') }}</div>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <span class="material-icons-round">error</span>
      <div>
        @foreach($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    </div>
  @endif

  <div class="topup-card">
    <form action="{{ route('billing.topup.submit') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- Amount -->
      <div class="section-label">{{ __('billing.topup.amount') }}</div>
      <div class="amount-input-wrap">
        <input type="number" name="amount" id="amountInput" class="amount-input"
               placeholder="{{ __('billing.topup.amount_placeholder') }}" min="10000" required>
        <span class="amount-currency">{{ __('common.currency') }}</span>
      </div>
      <div class="help-text" style="margin-bottom:16px">
        {{ __('billing.topup.min', ['amount' => '10 000 ' . __('common.currency')]) }}
      </div>

      <!-- Quick amounts -->
      <div class="section-label">{{ __('billing.topup.quick') }}</div>
      <div class="quick-amounts">
        @foreach([50000, 100000, 500000, 1000000] as $amt)
          <button type="button" class="quick-amount" onclick="setAmount({{ $amt }}, this)">
            @if($amt >= 1000000)
              {{ $amt / 1000000 }}M
            @else
              {{ $amt / 1000 }}K
            @endif
          </button>
        @endforeach
      </div>

      <!-- Payment method -->
      <div class="section-label">{{ __('billing.topup.method') }}</div>
      <div class="methods-list">
        <!-- Manual transfer (active) -->
        <label class="method-option active" onclick="selectMethod(this, 'manual')">
          <input type="radio" name="method" value="manual" checked>
          <div class="method-icon"><span class="material-icons-round">credit_card</span></div>
          <div class="method-content">
            <div class="method-title">
              {{ __('billing.topup.methods.manual') }}
              <span class="method-badge recommended">Recommended</span>
            </div>
            <div class="method-desc">{{ __('billing.topup.methods.manual_desc') }}</div>
          </div>
        </label>

        <!-- Payme (coming soon) -->
        <label class="method-option disabled">
          <input type="radio" name="method" value="payme" disabled>
          <div class="method-icon"><span class="material-icons-round">payments</span></div>
          <div class="method-content">
            <div class="method-title">
              {{ __('billing.topup.methods.payme') }}
              <span class="method-badge coming-soon">Soon</span>
            </div>
            <div class="method-desc">{{ __('billing.topup.methods.payme_desc') }}</div>
          </div>
        </label>

        <!-- Click (coming soon) -->
        <label class="method-option disabled">
          <input type="radio" name="method" value="click" disabled>
          <div class="method-icon"><span class="material-icons-round">smartphone</span></div>
          <div class="method-content">
            <div class="method-title">
              {{ __('billing.topup.methods.click') }}
              <span class="method-badge coming-soon">Soon</span>
            </div>
            <div class="method-desc">{{ __('billing.topup.methods.click_desc') }}</div>
          </div>
        </label>
      </div>

      <!-- Manual transfer details -->
      <div id="manualBlock">
        <div class="section-label">{{ __('billing.topup.card_info') }}</div>
        <div class="card-info-block">
          <div class="card-info-row">
            <span class="card-info-label">{{ __('billing.topup.card_number') }}</span>
            <span class="card-info-value">
              <span id="cardNumber">8600 1234 5678 9012</span>
              <button type="button" class="copy-btn" onclick="copyText('cardNumber', this)">
                <span class="material-icons-round">content_copy</span>
              </button>
            </span>
          </div>
          <div class="card-info-row">
            <span class="card-info-label">{{ __('billing.topup.card_holder') }}</span>
            <span class="card-info-value">ISMOILXON NURMATOV</span>
          </div>
        </div>

        <div class="section-label">{{ __('billing.topup.receipt') }}</div>
        <label class="file-upload" id="fileUpload">
          <div class="file-upload-icon">
            <span class="material-icons-round" id="uploadIcon">cloud_upload</span>
          </div>
          <div class="file-upload-text">
            <div class="file-upload-title" id="uploadTitle">
              {{ app()->getLocale() === 'uz' ? 'Faylni tanlang yoki tashlang' : (app()->getLocale() === 'ru' ? 'Выберите файл или перетащите' : 'Choose file or drop here') }}
            </div>
            <div class="file-upload-help">{{ __('billing.topup.receipt_help') }}</div>
          </div>
          <input type="file" name="receipt" accept="image/png,image/jpeg,image/jpg" onchange="handleFileSelect(this)">
        </label>

        <div class="help-text" style="margin-top:8px">
          <span class="material-icons-round" style="font-size:12px;vertical-align:middle">info</span>
          {{ __('billing.topup.pending') }}
        </div>
      </div>

      <div style="display:flex;gap:8px;margin-top:24px">
        <a href="{{ route('billing.index') }}" class="btn btn-secondary">
          {{ __('common.cancel') }}
        </a>
        <button type="submit" class="btn btn-primary" style="flex:1">
          <span class="material-icons-round">send</span>
          {{ __('billing.topup.submit') }}
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function setAmount(amount, btn) {
  document.getElementById('amountInput').value = amount;
  document.querySelectorAll('.quick-amount').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

function selectMethod(label, method) {
  document.querySelectorAll('.method-option').forEach(m => m.classList.remove('active'));
  label.classList.add('active');
  document.getElementById('manualBlock').style.display = method === 'manual' ? 'block' : 'none';
}

function copyText(id, btn) {
  const text = document.getElementById(id).textContent.trim();
  navigator.clipboard.writeText(text);
  const icon = btn.querySelector('.material-icons-round');
  const orig = icon.textContent;
  icon.textContent = 'check';
  btn.style.color = 'var(--success)';
  setTimeout(() => {
    icon.textContent = orig;
    btn.style.color = '';
  }, 1500);
}

function handleFileSelect(input) {
  const wrap = document.getElementById('fileUpload');
  const title = document.getElementById('uploadTitle');
  const icon = document.getElementById('uploadIcon');
  if (input.files.length > 0) {
    const file = input.files[0];
    wrap.classList.add('has-file');
    title.textContent = file.name;
    icon.textContent = 'check_circle';
  } else {
    wrap.classList.remove('has-file');
    icon.textContent = 'cloud_upload';
  }
}
</script>
@endpush