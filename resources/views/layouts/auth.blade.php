@extends('layouts.app')

@push('styles')
<style>
.auth-container {
  min-height: calc(100vh - 60px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 32px;
  box-shadow: var(--shadow-md);
}

.auth-header { text-align: center; margin-bottom: 28px; }
.auth-title { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; color: var(--text-strong); margin-bottom: 6px; }
.auth-subtitle { font-size: 13px; color: var(--text-muted); line-height: 1.5; }
.auth-form { display: flex; flex-direction: column; gap: 14px; }
.auth-field { display: flex; flex-direction: column; gap: 6px; }
.auth-label { font-size: 12px; font-weight: 600; color: var(--text); }
.auth-input {
  width: 100%; padding: 10px 14px; font-size: 14px;
  background: var(--bg); border: 1px solid var(--border);
  border-radius: 9px; outline: none; transition: all .15s;
}
.auth-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
.auth-input[readonly] { background: var(--bg-subtle); color: var(--text-muted); }
.auth-hint { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.auth-btn {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; padding: 11px 16px; font-size: 14px; font-weight: 600;
  background: var(--text-strong); color: var(--bg-elevated);
  border: none; border-radius: 10px; cursor: pointer; transition: all .15s;
}
.auth-btn:hover { opacity: 0.9; transform: translateY(-1px); }
.auth-btn .material-icons-round { font-size: 18px; }
.auth-footer {
  text-align: center; margin-top: 20px; padding-top: 20px;
  border-top: 1px solid var(--border); font-size: 13px; color: var(--text-muted);
}
.auth-footer a { color: var(--accent); font-weight: 600; text-decoration: none; }
.auth-footer a:hover { text-decoration: underline; }
.auth-alert {
  display: flex; gap: 10px; padding: 12px 14px;
  border-radius: 10px; font-size: 13px; margin-bottom: 16px;
  border: 1px solid;
}
.auth-alert .material-icons-round { font-size: 18px; flex-shrink: 0; }
.auth-alert-success { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.25); color: var(--success); }
.auth-alert-danger { background: rgba(239,68,68,.08); border-color: rgba(239,68,68,.25); color: var(--danger); }
.auth-alert-info { background: rgba(37,99,235,.08); border-color: rgba(37,99,235,.25); color: var(--accent); }
</style>
@endpush

@section('content')
<div class="auth-container">
  @yield('content')
</div>
@endsection