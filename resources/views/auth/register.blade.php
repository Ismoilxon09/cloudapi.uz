@extends('layouts.app')

@section('title', __('auth.register.title') . ' — CloudAPI')

@push('styles')
<style>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: var(--bg);
}

.auth-card {
  width: 100%;
  max-width: 460px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 36px;
  box-shadow: var(--shadow-lg);
}

.auth-brand {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  margin-bottom: 28px; font-weight: 700; font-size: 16px; letter-spacing: -0.02em;
}

.auth-brand-mark {
  width: 32px; height: 32px;
  background: var(--primary); color: var(--bg-elevated);
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
}

.auth-brand-mark .material-icons-round { font-size: 18px; }

.auth-title {
  font-size: 24px; font-weight: 800; letter-spacing: -0.02em;
  text-align: center; margin-bottom: 6px; color: var(--text-strong);
}

.auth-subtitle {
  font-size: 13px; color: var(--text-muted);
  text-align: center; margin-bottom: 28px;
}

.auth-toolbar {
  position: absolute; top: 20px; right: 20px;
  display: flex; gap: 8px;
}

.auth-footer {
  text-align: center; font-size: 13px;
  color: var(--text-muted); margin-top: 20px;
}

.auth-footer a {
  color: var(--text-strong); font-weight: 600; text-decoration: none;
}

.auth-footer a:hover { text-decoration: underline; }

.auth-terms {
  font-size: 11px; color: var(--text-subtle);
  text-align: center; margin-top: 16px; line-height: 1.5;
}

.auth-terms a { color: var(--text-muted); text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="auth-page">
  <div class="auth-toolbar">
    @include('partials.lang-switcher')
    <button class="icon-btn" onclick="toggleTheme()" style="border:1px solid var(--border);background:var(--bg-elevated)">
      <span class="material-icons-round" id="themeIcon">dark_mode</span>
    </button>
  </div>

  <div class="auth-card">
    <a href="{{ route('home') }}" class="auth-brand">
      <div class="auth-brand-mark">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="32" height="27" fill="currentColor">
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

    <h1 class="auth-title">{{ __('auth.register.title') }}</h1>
    <p class="auth-subtitle">{{ __('auth.register.subtitle') }}</p>

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

    <form action="{{ route('register') }}" method="POST">
      @csrf

      <div class="field">
        <label class="label">{{ __('auth.register.name') }}</label>
        <input type="text" name="name" class="input" value="{{ old('name') }}" required autofocus>
      </div>

      <div class="field">
        <label class="label">{{ __('auth.register.email') }}</label>
        <input type="email" name="email" class="input" value="{{ old('email') }}" required autocomplete="email">
      </div>

      <div class="field">
        <label class="label">{{ __('auth.register.password') }}</label>
        <input type="password" name="password" class="input" required autocomplete="new-password" minlength="8">
      </div>

      <div class="field">
        <label class="label">{{ __('auth.register.password_confirm') }}</label>
        <input type="password" name="password_confirmation" class="input" required autocomplete="new-password">
      </div>

      <div class="field">
        <label class="label">{{ __('auth.register.referral') }}</label>
        <input type="text" name="referral_code" class="input" value="{{ request('ref', old('referral_code')) }}" maxlength="12">
      </div>

      <button type="submit" class="btn btn-primary w-full btn-lg">
        {{ __('auth.register.submit') }}
        <span class="material-icons-round">arrow_forward</span>
      </button>

      <div class="auth-terms">
        {!! str_replace(
          [':terms', ':privacy'],
          ['<a href="' . route('terms') . '">' . __('auth.register.terms_link') . '</a>', '<a href="' . route('privacy') . '">' . __('auth.register.privacy_link') . '</a>'],
          __('auth.register.terms')
        ) !!}
      </div>
    </form>

    <!-- OAuth -->
    <div class="oauth-divider"><span>yoki davom etish</span></div>

    <div class="oauth-buttons">
      <a href="{{ route('telegram.login') }}" class="oauth-btn oauth-telegram" title="Telegram orqali ro'yxatdan o'tish" aria-label="Telegram orqali ro'yxatdan o'tish">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="#229ED9">
          <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.643.135-.953l11.566-4.458c.538-.196 1.006.128.832.94z"/>
        </svg>
      </a>

      <a href="{{ route('oauth.redirect', 'google') }}" class="oauth-btn oauth-google" title="Google orqali ro'yxatdan o'tish" aria-label="Google orqali ro'yxatdan o'tish">
        <svg viewBox="0 0 24 24" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
          <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
          <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
          <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
          <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
      </a>

      <a href="{{ route('oauth.redirect', 'github') }}" class="oauth-btn oauth-github" title="GitHub orqali ro'yxatdan o'tish" aria-label="GitHub orqali ro'yxatdan o'tish">
        <svg viewBox="0 0 24 24" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
          <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.4 3-.405 1.02.005 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
        </svg>
      </a>
    </div>

    <div class="auth-footer">
      {{ __('auth.register.has_account') }}
      <a href="{{ route('login') }}">{{ __('auth.register.sign_in') }}</a>
    </div>
  </div>
</div>

<style>
.oauth-divider {
  display: flex; align-items: center; gap: 12px;
  margin: 20px 0 16px; color: var(--text-subtle);
  font-size: 12px; text-transform: uppercase;
  letter-spacing: 0.1em; font-weight: 600;
}
.oauth-divider::before, .oauth-divider::after {
  content: ""; flex: 1; height: 1px; background: var(--border);
}
.oauth-buttons { display: flex; flex-direction: row; gap: 8px; margin-bottom: 16px; }
.oauth-btn {
  flex: 1;
  display: flex; align-items: center; justify-content: center;
  height: 42px;
  background: var(--bg-elevated); color: var(--text-strong);
  border: 1px solid var(--border); border-radius: 10px;
  text-decoration: none; transition: all .15s;
  position: relative; cursor: pointer;
}
.oauth-btn:hover {
  background: var(--bg-subtle); border-color: var(--border-strong);
  transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.oauth-btn svg { display: block; transition: transform .15s; }
.oauth-btn:hover svg { transform: scale(1.08); }
.oauth-btn::after {
  content: attr(title);
  position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%);
  background: var(--text-strong); color: var(--bg-elevated);
  font-size: 11px; font-weight: 600; padding: 5px 9px; border-radius: 6px;
  white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity .15s;
}
.oauth-btn:hover::after { opacity: 1; }
.oauth-github:hover { background: #24292E; border-color: #24292E; }
.oauth-github:hover svg { fill: white; }
.oauth-telegram:hover { background: #229ED9; border-color: #229ED9; }
.oauth-telegram:hover svg { fill: white !important; }
.oauth-google:hover { background: #FFFFFF; border-color: #DADCE0; }
</style>
@endsection