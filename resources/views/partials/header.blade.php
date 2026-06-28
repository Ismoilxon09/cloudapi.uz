@php
$user = auth()->user();
$balance = $user->wallet?->balance_uzs ?? 0;
$languages = [
    'en' => ['English', '🇬🇧'],
    'uz' => ["O'zbek", '🇺🇿'],
    'ru' => ['Русский', '🇷🇺'],
];
@endphp

<header class="cloud-header">
  <!-- Mobile menu toggle -->
  <button class="ch-mobile-toggle" onclick="openMobileSidebar()">
    <span class="material-icons-round">menu</span>
  </button>

  <!-- Page title slot (auto from route) -->
  <div class="ch-page-info">
    @yield('page_title')
  </div>

  <!-- Spacer -->
  <div style="flex:1"></div>

  <!-- Right tools -->
  <div class="ch-tools">
    <!-- Balance card -->
    <a href="{{ route('billing.index') }}" class="ch-balance" title="{{ __('common.balance') }}">
      <div class="ch-balance-icon">
        <span class="material-icons-round">account_balance_wallet</span>
      </div>
      <div class="ch-balance-info">
        <div class="ch-balance-label">{{ __('common.balance') }}</div>
        <div class="ch-balance-value">
          {{ number_format($balance, 0, '.', ' ') }}
          <span class="ch-balance-currency">{{ __('common.currency') }}</span>
        </div>
      </div>
    </a>

    <a href="{{ route('billing.topup') }}" class="ch-topup-btn" title="{{ __('dashboard.quick_actions.topup') }}">
      <span class="material-icons-round">add</span>
      <span class="ch-topup-text">{{ app()->getLocale() === 'uz' ? "To'ldirish" : (app()->getLocale() === 'ru' ? 'Пополнить' : 'Top up') }}</span>
    </a>

    <div class="ch-divider"></div>

    <!-- Language switcher -->
    <div class="ch-dropdown" id="chLang">
      <button class="ch-icon-btn ch-lang-btn" onclick="toggleChDropdown('chLang')" title="Language">
        <span>{{ strtoupper(app()->getLocale()) }}</span>
        <span class="material-icons-round" style="font-size:14px">expand_more</span>
      </button>
      <div class="ch-dropdown-menu">
        @foreach($languages as $code => $lang)
          <a href="?lang={{ $code }}" class="ch-dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}">
            <span style="font-size:16px">{{ $lang[1] }}</span>
            <span>{{ $lang[0] }}</span>
            @if(app()->getLocale() === $code)
              <span class="material-icons-round" style="margin-left:auto;font-size:14px;color:var(--accent)">check</span>
            @endif
          </a>
        @endforeach
      </div>
    </div>

    <!-- Notifications bell -->
    <div class="ch-dropdown ch-notif-wrap" id="chNotif">
      <button class="ch-icon-btn ch-notif-btn" onclick="toggleNotifications()" title="Bildirishnomalar">
        <span class="material-icons-round">notifications</span>
        <span class="ch-notif-badge" id="chNotifBadge" style="display:none">0</span>
      </button>
      <div class="ch-dropdown-menu ch-notif-menu" id="chNotifMenu">
        <div class="ch-notif-header">
          <div class="ch-notif-title">Bildirishnomalar</div>
          <button class="ch-notif-markall" id="chMarkAllBtn" onclick="markAllNotifsRead()" style="display:none">
            <span class="material-icons-round">done_all</span>
          </button>
        </div>
        <div class="ch-notif-body" id="chNotifBody">
          <div class="ch-notif-loading">
            <span class="material-icons-round">hourglass_empty</span>
            Yuklanmoqda...
          </div>
        </div>
        <a href="{{ route('notifications.index') }}" class="ch-notif-footer">
          Hammasini ko'rish
          <span class="material-icons-round">arrow_forward</span>
        </a>
      </div>
    </div>

    <!-- Theme toggle -->
    <button class="ch-icon-btn" onclick="toggleTheme()" title="Theme">
      <span class="material-icons-round" id="themeIcon">dark_mode</span>
    </button>

    <!-- User menu -->
    <div class="ch-dropdown" id="chUser">
      <button class="ch-user-btn" onclick="toggleChDropdown('chUser')">
        <div class="ch-user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
      </button>
      <div class="ch-dropdown-menu ch-user-menu">
        <div class="ch-user-header">
          <div class="ch-user-avatar" style="width:36px;height:36px;font-size:14px">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
          <div style="flex:1;min-width:0">
            <div class="ch-user-name">{{ $user->name }}</div>
            <div class="ch-user-email">{{ $user->email }}</div>
          </div>
        </div>

        <div class="ch-user-balance">
          <div>
            <div style="font-size:11px;color:var(--text-muted)">{{ __('common.balance') }}</div>
            <div style="font-size:16px;font-weight:700;font-family:'JetBrains Mono',monospace">
              {{ number_format($balance, 0, '.', ' ') }} <span style="font-size:11px;color:var(--text-muted)">{{ __('common.currency') }}</span>
            </div>
          </div>
          <a href="{{ route('billing.topup') }}" class="btn btn-primary btn-sm">
            <span class="material-icons-round">add</span>
            {{ app()->getLocale() === 'uz' ? "To'ldirish" : (app()->getLocale() === 'ru' ? 'Пополнить' : 'Top up') }}
          </a>
        </div>

        <a href="{{ route('billing.index') }}" class="ch-dropdown-item">
          <span class="material-icons-round">account_balance_wallet</span>
          {{ __('common.nav.wallet') }}
        </a>
        <a href="{{ route('keys.index') }}" class="ch-dropdown-item">
          <span class="material-icons-round">key</span>
          {{ __('common.nav.keys') }}
        </a>
        <div class="ch-dropdown-divider"></div>
        <form action="{{ route('logout') }}" method="POST" style="margin:0">
          @csrf
          <button type="submit" class="ch-dropdown-item" style="width:100%;text-align:left;color:var(--danger)">
            <span class="material-icons-round">logout</span>
            {{ __('common.nav.sign_out') }}
          </button>
        </form>
      </div>
    </div>
  </div>
</header>