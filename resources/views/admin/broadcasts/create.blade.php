@extends('admin.layout')
@section('title', 'Yangi xabar yuborish')
@section('page_title', 'Yangi broadcast')

@push('styles')
<style>
.bc-create { padding: 24px; max-width: 800px; margin: 0 auto; }
.bc-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 14px; padding: 24px; margin-bottom: 16px; }
.section-title { font-size: 14px; font-weight: 700; color: var(--text-strong); margin-bottom: 14px; }

.option-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.option {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  padding: 16px; background: var(--bg-elevated); border: 2px solid var(--border);
  border-radius: 10px; cursor: pointer; transition: all .15s; text-align: center;
}
.option:hover { border-color: var(--border-strong); }
.option.active { border-color: var(--primary); background: var(--bg-subtle); }
.option input { display: none; }
.option-icon { width: 36px; height: 36px; border-radius: 9px; background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; }
.option-icon .material-icons-round { font-size: 18px; color: var(--text-strong); }
.option-title { font-size: 13px; font-weight: 700; color: var(--text-strong); }
.option-meta { font-size: 11px; color: var(--text-muted); }

.stat-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px solid var(--border); }
.stat-row:last-child { border-bottom: none; }
.stat-label { color: var(--text-muted); }
.stat-value { font-weight: 700; color: var(--text-strong); font-family: 'JetBrains Mono', monospace; }

textarea.input { min-height: 140px; resize: vertical; font-family: inherit; }
</style>
@endpush

@section('content')
<div class="bc-create">
  <a href="{{ route('admin.broadcasts.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);margin-bottom:16px">
    <span class="material-icons-round" style="font-size:16px">arrow_back</span>
    Orqaga
  </a>

  <div class="page-header">
    <div>
      <h1 class="page-title">Yangi xabar</h1>
      <p class="page-subtitle">Foydalanuvchilarga broadcast yuborish</p>
    </div>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <span class="material-icons-round">error</span>
      <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
  @endif

  <form action="{{ route('admin.broadcasts.store') }}" method="POST">
    @csrf

    <!-- Channel -->
    <div class="bc-card">
      <div class="section-title">Kanal tanlang</div>
      <div class="option-grid">
        <label class="option active" onclick="selectOption(this, 'channel', 'telegram')">
          <input type="radio" name="channel" value="telegram" checked>
          <div class="option-icon"><span class="material-icons-round">send</span></div>
          <div class="option-title">Telegram</div>
          <div class="option-meta">{{ $telegramUsers }} user ulangan</div>
        </label>
        <label class="option" onclick="selectOption(this, 'channel', 'in_app')">
          <input type="radio" name="channel" value="in_app">
          <div class="option-icon"><span class="material-icons-round">notifications</span></div>
          <div class="option-title">In-app</div>
          <div class="option-meta">Sayt ichi xabar</div>
        </label>
      </div>
    </div>

    <!-- Target -->
    <div class="bc-card">
      <div class="section-title">Kimga yuborish</div>
      <div class="option-grid">
        <label class="option active" onclick="selectOption(this, 'target', 'all')">
          <input type="radio" name="target" value="all" checked>
          <div class="option-icon"><span class="material-icons-round">group</span></div>
          <div class="option-title">Hammasiga</div>
          <div class="option-meta">{{ $totalUsers }} user</div>
        </label>
        <label class="option" onclick="selectOption(this, 'target', 'active')">
          <input type="radio" name="target" value="active">
          <div class="option-icon"><span class="material-icons-round">verified_user</span></div>
          <div class="option-title">Faqat faollar</div>
          <div class="option-meta">{{ $activeUsers }} user</div>
        </label>
      </div>
    </div>

    <!-- Message -->
    <div class="bc-card">
      <div class="section-title">Xabar matni</div>
      <div class="field">
        <label class="label">Sarlavha (ixtiyoriy)</label>
        <input type="text" name="subject" class="input" placeholder="Masalan: Yangi modellar qo'shildi" value="{{ old('subject') }}">
      </div>
      <div class="field">
        <label class="label">Xabar <span style="color:var(--danger)">*</span></label>
        <textarea name="message" class="input" placeholder="Xabar matni... (Markdown qo'llab-quvvatlanadi)" required>{{ old('message') }}</textarea>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
          *kalin*, _kursiv_, [havola](url) qo'llab-quvvatlanadi
        </div>
      </div>
    </div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <a href="{{ route('admin.broadcasts.index') }}" class="btn btn-secondary">Bekor qilish</a>
      <button type="submit" class="btn btn-primary" onclick="return confirm('Xabarni yuborishga tayyormisiz?')">
        <span class="material-icons-round">send</span>
        Yuborish
      </button>
    </div>
  </form>
</div>

<script>
function selectOption(label, name, value) {
  document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
    input.closest('.option').classList.remove('active');
  });
  label.classList.add('active');
  label.querySelector('input').checked = true;
}
</script>
@endsection