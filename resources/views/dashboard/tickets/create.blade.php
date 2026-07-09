@extends('layouts.dashboard')

@section('title', 'Yangi ticket')

@push('styles')
<style>
.ticket-create-page { max-width: 700px; margin: 0 auto; padding: 24px; }
.ticket-form-card { background: var(--bg-elevated); border: 1px solid var(--border); border-radius: 14px; padding: 32px; }
.ticket-form-title { font-size: 20px; font-weight: 800; letter-spacing: -.02em; color: var(--text-strong); margin: 0 0 6px; }
.ticket-form-subtitle { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }

.ticket-field { margin-bottom: 18px; }
.ticket-field label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
.ticket-field .input, .ticket-field textarea, .ticket-field select {
  width: 100%; padding: 12px 14px;
  background: var(--bg-subtle); border: 1.5px solid var(--border);
  border-radius: 10px; font-size: 14px; color: var(--text-strong);
  font-family: inherit; transition: all .15s;
}
.ticket-field .input:focus, .ticket-field textarea:focus, .ticket-field select:focus {
  outline: none; border-color: var(--text-strong); background: var(--bg-elevated);
}
.ticket-field textarea { resize: vertical; min-height: 140px; }
.ticket-field .field-help { font-size: 11.5px; color: var(--text-subtle); margin-top: 4px; }

.ticket-form-actions { display: flex; gap: 10px; margin-top: 24px; }
</style>
@endpush

@section('content')
<div class="ticket-create-page">
  <div style="margin-bottom: 20px;">
    <a href="{{ route('dashboard.tickets.index') }}" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-size: 13px;">
      <span class="material-icons-round" style="font-size: 16px">arrow_back</span>
      Ticketlar
    </a>
  </div>

  <div class="ticket-form-card">
    <h1 class="ticket-form-title">Yangi ticket yaratish</h1>
    <p class="ticket-form-subtitle">Muammo yoki savolingizni yozing. Tez orada javob olasiz.</p>

    @if($errors->any())
      <div class="alert alert-danger" style="margin-bottom: 16px">
        @foreach($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <form action="{{ route('dashboard.tickets.store') }}" method="POST">
      @csrf

      <div class="ticket-field">
        <label>Sarlavha</label>
        <input type="text" name="subject" class="input" value="{{ old('subject') }}" required maxlength="255" placeholder="Muammo yoki savol qisqacha">
      </div>

      <div class="ticket-field">
        <label>Muhimlik darajasi</label>
        <select name="priority" class="input">
          <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Odatiy</option>
          <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Past</option>
          <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Yuqori</option>
          <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Shoshilinch</option>
        </select>
      </div>

      <div class="ticket-field">
        <label>Xabar</label>
        <textarea name="message" required minlength="10" maxlength="2000" placeholder="Muammoingizni batafsil tushuntiring...">{{ old('message') }}</textarea>
        <div class="field-help">Kamida 10, maksimum 2000 belgi</div>
      </div>

      <div class="ticket-form-actions">
        <button type="submit" class="btn btn-primary">
          <span class="material-icons-round">send</span>
          Yuborish
        </button>
        <a href="{{ route('dashboard.tickets.index') }}" class="btn btn-secondary">Bekor qilish</a>
      </div>
    </form>
  </div>
</div>
@endsection