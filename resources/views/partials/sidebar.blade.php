<?php
$navGroups = [
    [
        'label' => app()->getLocale() === 'uz' ? 'Asosiy' : (app()->getLocale() === 'ru' ? 'Основное' : 'Main'),
        'items' => [
            ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => __('common.nav.dashboard'), 'pattern' => 'dashboard'],
            ['route' => 'dashboard.chat.index', 'icon' => 'forum', 'label' => 'Chat', 'pattern' => 'dashboard.chat.*'],
            ['route' => 'media.index', 'icon' => 'perm_media', 'label' => 'Kutubxona', 'pattern' => 'media.*'],
            ['route' => 'models.index', 'icon' => 'memory', 'label' => __('common.nav.models'), 'pattern' => 'models.*'],
            ['route' => 'playground.index', 'icon' => 'play_arrow', 'label' => __('common.nav.playground'), 'pattern' => 'playground.*'],
        ],
    ],
    [
        'label' => app()->getLocale() === 'uz' ? 'Boshqaruv' : (app()->getLocale() === 'ru' ? 'Управление' : 'Manage'),
        'items' => [
            ['route' => 'keys.index', 'icon' => 'key', 'label' => __('common.nav.keys'), 'pattern' => 'keys.*'],
            ['route' => 'activity.index', 'icon' => 'analytics', 'label' => __('common.nav.activity'), 'pattern' => 'activity.*'],
            ['route' => 'logs.index', 'icon' => 'description', 'label' => __('common.nav.logs'), 'pattern' => 'logs.*'],
            ['route' => 'billing.index', 'icon' => 'account_balance_wallet', 'label' => __('common.nav.billing'), 'pattern' => 'billing.*'],
            ['route' => 'notifications.index', 'icon' => 'notifications', 'label' => app()->getLocale() === 'uz' ? 'Bildirishnomalar' : (app()->getLocale() === 'ru' ? 'Уведомления' : 'Notifications'), 'pattern' => 'notifications.*'],
        ],
    ],
    [
        'label' => app()->getLocale() === 'uz' ? 'Boshqalar' : (app()->getLocale() === 'ru' ? 'Прочее' : 'Other'),
        'items' => [
            ['route' => 'settings.index', 'icon' => 'settings', 'label' => __('common.nav.settings'), 'pattern' => 'settings.*'],
            ['route' => 'docs', 'icon' => 'menu_book', 'label' => __('common.nav.docs'), 'pattern' => 'docs'],
        ],
    ],
];
?>

<aside class="cloud-sidebar" id="cloudSidebar">
  <div class="cs-top">
    <a href="{{ route('home') }}" class="cs-brand">
      <div class="cs-brand-mark">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="30" height="25" fill="currentColor">
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
      <span class="cs-brand-text">cloud<span style="font-weight:500;opacity:0.6;">api</span></span>
    </a>
    <button class="cs-collapse-btn" onclick="toggleSidebar()" title="Toggle sidebar">
      <span class="material-icons-round">chevron_left</span>
    </button>
  </div>

  <nav class="cs-nav">
    @foreach($navGroups as $group)
      <div class="cs-nav-group">
        <div class="cs-nav-label">{{ $group['label'] }}</div>
        @foreach($group['items'] as $item)
          <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
             class="cs-nav-item {{ request()->routeIs($item['pattern']) ? 'active' : '' }}"
             title="{{ $item['label'] }}">
            <span class="material-icons-round cs-nav-icon">{{ $item['icon'] }}</span>
            <span class="cs-nav-text">{{ $item['label'] }}</span>
          </a>
        @endforeach
      </div>
    @endforeach
  </nav>

  <div class="cs-bottom">
    <a href="{{ route('dashboard.tickets.index') }}" class="cs-nav-item" title="{{ __('common.nav.help') }}">
      <span class="material-icons-round cs-nav-icon">support_agent</span>
      <span class="cs-nav-text">{{ __('common.nav.help') }}</span>
    </a>
  </div>
</aside>

<div class="cs-mobile-overlay" onclick="closeMobileSidebar()"></div>