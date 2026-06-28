@extends('layouts.auth')

@section('title', "Telegram orqali kirish — CloudAPI")

@section('content')
<div class="auth-card">
  <div style="text-align:center;margin-bottom:24px">
    <div style="width:64px;height:64px;background:#229ED9;border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px">
      <svg viewBox="0 0 24 24" width="36" height="36" fill="#FFFFFF">
        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.643.135-.953l11.566-4.458c.538-.196 1.006.128.832.94z"/>
      </svg>
    </div>
    <h1 class="auth-title">Telegram orqali kirish</h1>
    <p class="auth-subtitle">Telefon raqam yoki Telegram username kiriting</p>
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

  <form action="{{ route('telegram.send-code') }}" method="POST" class="auth-form">
    @csrf

    <div class="auth-field">
      <label class="auth-label">Telefon raqam yoki username</label>
      <input type="text" name="identifier" class="auth-input"
             placeholder="+998 90 123 45 67 yoki @username"
             required autofocus value="{{ old('identifier') }}">
      <div class="auth-hint">
        Botga avval <strong>/start</strong> bosgan bo'lishingiz kerak
      </div>
    </div>

    <button type="submit" class="auth-btn" style="background:#229ED9">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="#FFFFFF">
        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.643.135-.953l11.566-4.458c.538-.196 1.006.128.832.94z"/>
      </svg>
      Kod yuborish
    </button>
  </form>

  <div style="margin:24px 0;padding:14px;background:var(--bg-subtle);border-radius:10px;font-size:12.5px;color:var(--text-muted);line-height:1.6">
    <strong style="color:var(--text-strong);display:block;margin-bottom:4px">📌 Qanday ishlaydi?</strong>
    1. Telegram bot @cloudapiuzbot ga <code>/start</code> bosing<br>
    2. Telefon raqamingizni botga yuboring<br>
    3. Bu sahifaga qaytib, raqamingizni kiriting<br>
    4. Botga 6 raqamli kod keladi → kiriting → kirib oldingiz!
  </div>

  <div class="auth-footer">
    <a href="{{ route('login') }}">← Email orqali kirish</a>
  </div>
</div>
@endsection