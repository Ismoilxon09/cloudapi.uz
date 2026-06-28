@php
$pendingCount = \App\Models\Transaction::where('type', 'deposit')->where('status', 'pending')->count();
$unreadNotifs = \App\Models\AdminNotification::whereNull('read_at')->count();

$navGroups = [
    [
        'label' => 'Asosiy',
        'items' => [
            ['route' => 'admin.dashboard', 'icon' => 'space_dashboard', 'label' => 'Dashboard', 'pattern' => 'admin.dashboard'],
            ['route' => 'admin.stats.index', 'icon' => 'analytics', 'label' => 'Statistika', 'pattern' => 'admin.stats.*'],
        ],
    ],
    [
        'label' => 'Foydalanuvchilar',
        'items' => [
            ['route' => 'admin.users.index', 'icon' => 'group', 'label' => 'Userlar', 'pattern' => 'admin.users.*'],
            ['route' => 'admin.broadcasts.index', 'icon' => 'campaign', 'label' => 'Yuborish', 'pattern' => 'admin.broadcasts.*'],
        ],
    ],
    [
        'label' => "To'lovlar",
        'items' => [
            ['route' => 'admin.payments.index', 'icon' => 'payments', 'label' => "To'lovlar", 'pattern' => 'admin.payments.*', 'badge' => $pendingCount > 0 ? $pendingCount : null],
            ['route' => 'admin.transactions.index', 'icon' => 'receipt_long', 'label' => 'Tranzaksiyalar', 'pattern' => 'admin.transactions.*'],
        ],
    ],
    [
        'label' => 'API',
        'items' => [
            ['route' => 'admin.keys.index', 'icon' => 'key', 'label' => 'Kalitlar', 'pattern' => 'admin.keys.*'],
            ['route' => 'admin.logs.index', 'icon' => 'description', 'label' => 'API loglar', 'pattern' => 'admin.logs.*'],
            ['route' => 'admin.models.index', 'icon' => 'memory', 'label' => 'Modellar', 'pattern' => 'admin.models.*'],
        ],
    ],
    [
        'label' => 'Tizim',
        'items' => [
            ['route' => 'admin.notifications.index', 'icon' => 'notifications', 'label' => 'Bildirishnomalar', 'pattern' => 'admin.notifications.*', 'badge' => $unreadNotifs > 0 ? $unreadNotifs : null],
            ['route' => 'admin.audit.index', 'icon' => 'fact_check', 'label' => 'Audit log', 'pattern' => 'admin.audit.*'],
            ['route' => 'admin.settings.index', 'icon' => 'settings', 'label' => 'Sozlamalar', 'pattern' => 'admin.settings.*'],
        ],
    ],
];
@endphp

<aside class="admin-sidebar" id="adminSidebar">
  <div class="adm-top">
    <a href="{{ route('admin.dashboard') }}" class="adm-brand">
      <div class="adm-brand-mark">
        <span class="material-icons-round">shield</span>
      </div>
      <span class="adm-brand-text">Admin</span>
    </a>
    <button class="adm-collapse-btn" onclick="toggleAdminSidebar()" title="Toggle">
      <span class="material-icons-round">chevron_left</span>
    </button>
  </div>

  <nav class="adm-nav">
    @foreach($navGroups as $group)
      <div class="adm-nav-group">
        <div class="adm-nav-label">{{ $group['label'] }}</div>
        @foreach($group['items'] as $item)
          <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
             class="adm-nav-item {{ request()->routeIs($item['pattern']) ? 'active' : '' }}"
             title="{{ $item['label'] }}">
            <span class="material-icons-round adm-nav-icon">{{ $item['icon'] }}</span>
            <span class="adm-nav-text">{{ $item['label'] }}</span>
            @if(!empty($item['badge']))
              <span class="adm-nav-badge">{{ $item['badge'] }}</span>
            @endif
          </a>
        @endforeach
      </div>
    @endforeach
  </nav>

  <div class="adm-bottom">
    <a href="{{ route('dashboard') }}" class="adm-nav-item" title="User panel">
      <span class="material-icons-round adm-nav-icon">arrow_back</span>
      <span class="adm-nav-text">User panel</span>
    </a>
  </div>
</aside>