@extends('layouts.app')

@push('styles')
<style>
.dashboard-grid {
  display: grid;
  grid-template-columns: 240px 1fr;
  min-height: calc(100vh - 60px);
  max-width: 1400px;
  margin: 0 auto;
}

@media (max-width: 1024px) {
  .dashboard-grid { grid-template-columns: 64px 1fr; }
}

@media (max-width: 640px) {
  .dashboard-grid { grid-template-columns: 1fr; }
  .dashboard-grid .sidebar { display: none; }
}
</style>
@endpush

@section('content')
<div class="dashboard-grid">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Workspace</div>
      <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="material-icons-round">dashboard</span>
        <span>Overview</span>
      </a>
      <a href="{{ route('keys.index') }}" class="sidebar-item {{ request()->routeIs('keys.*') ? 'active' : '' }}">
        <span class="material-icons-round">key</span>
        <span>API Keys</span>
      </a>
      <a href="{{ route('playground.index') }}" class="sidebar-item {{ request()->routeIs('playground.*') ? 'active' : '' }}">
        <span class="material-icons-round">code</span>
        <span>Playground</span>
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label">Billing</div>
      <a href="{{ route('billing.index') }}" class="sidebar-item {{ request()->routeIs('billing.index') ? 'active' : '' }}">
        <span class="material-icons-round">account_balance_wallet</span>
        <span>Wallet</span>
      </a>
      <a href="{{ route('billing.topup') }}" class="sidebar-item {{ request()->routeIs('billing.topup') ? 'active' : '' }}">
        <span class="material-icons-round">add_circle</span>
        <span>Top up</span>
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-label">Resources</div>
      <a href="{{ route('docs') }}" class="sidebar-item">
        <span class="material-icons-round">menu_book</span>
        <span>Documentation</span>
      </a>
      <a href="#" class="sidebar-item">
        <span class="material-icons-round">help_outline</span>
        <span>Support</span>
      </a>
    </div>
  </aside>

  <div class="main-content">
    @yield('dashboard')
  </div>
</div>
@endsection