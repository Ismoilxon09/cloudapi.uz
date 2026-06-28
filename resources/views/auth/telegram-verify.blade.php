@extends('layouts.auth')

@section('title', "Kodni kiriting — CloudAPI")

@section('content')
<div class="auth-card">
  <div style="text-align:center;margin-bottom:24px">
    <div style="width:64px;height:64px;background:rgba(34,158,217,.12);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px">
      <span class="material-icons-round" style="font-size:32px;color:#229ED9">message</span>
    </div>
    <h1 class="auth-title">Kodni kiriting</h1>
    <p class="auth-subtitle">Botga 6 raqamli kod yuborildi</p>
  </div>

  @if(session('success'))
    <div class="auth-alert auth-alert-success">
      <span class="material-icons-round">check_circle</span>
      <div>{{ session('success') }}</div>
    </div>
  @endif

  @if($errors->any())
    <div class="auth-alert auth-alert-danger">
      <span class="material-icons-round">error</span>
      <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
  @endif

  <form action="{{ route('telegram.verify') }}" method="POST" class="auth-form" id="verifyForm">
    @csrf

    <div class="code-input-group">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-input" data-index="0" autofocus>
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-input" data-index="1">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-input" data-index="2">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-input" data-index="3">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-input" data-index="4">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="code-input" data-index="5">
    </div>

    <input type="hidden" name="code" id="codeHidden">

    <button type="submit" class="auth-btn" id="submitBtn" disabled>
      <span class="material-icons-round">check</span>
      Tasdiqlash
    </button>
  </form>

  <div style="text-align:center;margin-top:20px;font-size:13px;color:var(--text-muted)">
    Kod kelmadimi?
    <a href="{{ route('telegram.login') }}" style="color:var(--accent);font-weight:600">Qayta yuborish</a>
  </div>

  <div class="auth-footer">
    <a href="{{ route('telegram.login') }}">← Boshqa raqam</a>
  </div>
</div>

<style>
.code-input-group {
  display: flex;
  gap: 8px;
  justify-content: center;
  margin-bottom: 20px;
}

.code-input {
  width: 48px;
  height: 56px;
  border: 2px solid var(--border);
  border-radius: 10px;
  background: var(--bg);
  text-align: center;
  font-size: 24px;
  font-weight: 700;
  color: var(--text-strong);
  outline: none;
  font-family: 'JetBrains Mono', monospace;
  transition: all .15s;
}

.code-input:focus {
  border-color: #229ED9;
  box-shadow: 0 0 0 3px rgba(34,158,217,.15);
}

.code-input.filled {
  border-color: var(--accent);
  background: rgba(37,99,235,.04);
}

#submitBtn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

@media (max-width: 480px) {
  .code-input { width: 40px; height: 48px; font-size: 20px; }
}
</style>

<script>
const inputs = document.querySelectorAll('.code-input');
const hidden = document.getElementById('codeHidden');
const submitBtn = document.getElementById('submitBtn');
const form = document.getElementById('verifyForm');

function updateCode() {
  const code = Array.from(inputs).map(i => i.value).join('');
  hidden.value = code;
  submitBtn.disabled = code.length !== 6;
  if (code.length === 6) {
    form.submit();
  }
}

inputs.forEach((input, idx) => {
  input.addEventListener('input', (e) => {
    let val = e.target.value.replace(/[^0-9]/g, '');
    e.target.value = val;

    if (val.length === 1) {
      input.classList.add('filled');
      if (idx < inputs.length - 1) inputs[idx + 1].focus();
    } else {
      input.classList.remove('filled');
    }
    updateCode();
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && !input.value && idx > 0) {
      inputs[idx - 1].focus();
    }
  });

  input.addEventListener('paste', (e) => {
    e.preventDefault();
    const pasted = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
    inputs.forEach((inp, i) => {
      inp.value = pasted[i] || '';
      if (pasted[i]) inp.classList.add('filled');
    });
    updateCode();
    const lastFilled = pasted.length - 1;
    if (lastFilled >= 0 && lastFilled < inputs.length) {
      inputs[Math.min(lastFilled + 1, 5)].focus();
    }
  });
});
</script>
@endsection