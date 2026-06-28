@extends('layouts.auth')

@section('title', "Parolni tiklash — CloudAPI")

@section('content')
<div class="auth-card">
  <div class="auth-header">
    <h1 class="auth-title">Parolni tiklash</h1>
    <p class="auth-subtitle">Emailingizni kiriting, parol tiklash xati yuboramiz</p>
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

  <form action="{{ route('password.email') }}" method="POST" class="auth-form">
    @csrf

    <div class="auth-field">
      <label class="auth-label">Email</label>
      <input type="email" name="email" class="auth-input" placeholder="siz@email.com" required autofocus value="{{ old('email') }}">
    </div>

    <button type="submit" class="auth-btn">
      <span class="material-icons-round">send</span>
      Tiklash linkini yuborish
    </button>
  </form>

  <div class="auth-footer">
    Esladingizmi? <a href="{{ route('login') }}">Kirish</a>
  </div>
</div>
@endsection