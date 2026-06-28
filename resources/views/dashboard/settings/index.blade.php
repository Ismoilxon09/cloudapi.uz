@extends('layouts.app')

@section('title', __('settings.title') . ' — CloudAPI')

@push('styles')
<style>
.settings-page { max-width: 800px; margin: 0 auto; padding: 24px; }
.settings-header { margin-bottom: 24px; }
.settings-title { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); margin-bottom: 4px; }
.settings-subtitle { font-size: 13px; color: var(--text-muted); }

.settings-card {
  background: var(--bg-elevated); border: 1px solid var(--border);
  border-radius: 14px; padding: 24px; margin-bottom: 16px;
}

.settings-card-title { font-size: 15px; font-weight: 700; color: var(--text-strong); margin-bottom: 4px; }
.settings-card-subtitle { font-size: 12px; color: var(--text-muted); margin-bottom: 18px; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
.field-full { grid-column: 1 / -1; }

.info-row {
  display: flex; justify-content: space-between;
  padding: 10px 0; border-bottom: 1px solid var(--border);
  font-size: 13px;
}
.info-row:last-child { border-bottom: none; }
.info-key { color: var(--text-muted); }
.info-value { color: var(--text-strong); font-weight: 600; }
</style>
@endpush

@section('content')
<div class="settings-page">
  <div class="settings-header">
    <h1 class="settings-title">{{ __('settings.title') }}</h1>
    <p class="settings-subtitle">{{ __('settings.subtitle') }}</p>
  </div>

  @if(session('success'))
    <div class="alert alert-success">
      <span class="material-icons-round">check_circle</span>
      <div>{{ session('success') }}</div>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <span class="material-icons-round">error</span>
      <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
  @endif

  <!-- Profile -->
  <div class="settings-card">
    <div class="settings-card-title">{{ __('settings.profile.title') }}</div>
    <div class="settings-card-subtitle">{{ __('settings.profile.subtitle') }}</div>

    <form action="{{ route('settings.profile') }}" method="POST">
      @csrf
      <div class="form-grid">
        <div class="field">
          <label class="label">{{ __('settings.profile.name') }}</label>
          <input type="text" name="name" class="input" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="field">
          <label class="label">{{ __('settings.profile.email') }}</label>
          <input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="field">
          <label class="label">{{ __('settings.profile.phone') }}</label>
          <input type="text" name="phone" class="input" value="{{ old('phone', $user->phone) }}">
        </div>
        <div class="field">
          <label class="label">{{ __('settings.profile.country') }}</label>
          <input type="text" name="country" class="input" value="{{ old('country', $user->country) }}" maxlength="2">
        </div>
        <div class="field field-full">
          <label class="label">{{ __('settings.profile.language') }}</label>
          <select name="language" class="input">
            <option value="en" {{ ($user->language ?? 'en') === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
            <option value="uz" {{ ($user->language ?? 'en') === 'uz' ? 'selected' : '' }}>🇺🇿 O'zbek</option>
            <option value="ru" {{ ($user->language ?? 'en') === 'ru' ? 'selected' : '' }}>🇷🇺 Русский</option>
          </select>
        </div>
      </div>

      <button type="submit" class="btn btn-primary mt-4">
        <span class="material-icons-round">save</span>
        {{ __('settings.profile.save') }}
      </button>
    </form>
  </div>

  <!-- Password -->
  <div class="settings-card">
    <div class="settings-card-title">{{ __('settings.password.title') }}</div>
    <div class="settings-card-subtitle">{{ __('settings.password.subtitle') }}</div>

    <form action="{{ route('settings.password') }}" method="POST">
      @csrf
      <div class="field">
        <label class="label">{{ __('settings.password.current') }}</label>
        <input type="password" name="current_password" class="input" required>
      </div>
      <div class="form-grid">
        <div class="field">
          <label class="label">{{ __('settings.password.new') }}</label>
          <input type="password" name="password" class="input" required minlength="8">
        </div>
        <div class="field">
          <label class="label">{{ __('settings.password.confirm') }}</label>
          <input type="password" name="password_confirmation" class="input" required minlength="8">
        </div>
      </div>

      <button type="submit" class="btn btn-primary mt-4">
        <span class="material-icons-round">lock</span>
        {{ __('settings.password.save') }}
      </button>
    </form>
  </div>

  <!-- Account info -->
  <div class="settings-card">
    <div class="settings-card-title">{{ __('settings.account.title') }}</div>
    <div class="info-row">
      <span class="info-key">{{ __('settings.account.created') }}</span>
      <span class="info-value">{{ $user->created_at?->format('M d, Y') }}</span>
    </div>
    <div class="info-row">
      <span class="info-key">{{ __('settings.account.role') }}</span>
      <span class="info-value">{{ ucfirst($user->role) }}</span>
    </div>
    <div class="info-row">
      <span class="info-key">{{ __('settings.account.status') }}</span>
      <span class="info-value" style="color:var(--success)">● {{ ucfirst($user->status) }}</span>
    </div>
    @if($user->referral_code)
    <div class="info-row">
      <span class="info-key">Referral code</span>
      <span class="info-value" style="font-family:'JetBrains Mono',monospace">{{ $user->referral_code }}</span>
    </div>
    @endif
  </div>
</div>

@endsection