@php
$currentLocale = app()->getLocale();
$languages = [
    'en' => ['name' => 'English', 'flag' => '🇬🇧'],
    'uz' => ['name' => "O'zbek",  'flag' => '🇺🇿'],
    'ru' => ['name' => 'Русский', 'flag' => '🇷🇺'],
];
@endphp

<div class="lang-switcher" id="langSwitcher">
  <button class="lang-btn" onclick="document.getElementById('langSwitcher').classList.toggle('open')">
    <span>{{ strtoupper($currentLocale) }}</span>
    <span class="material-icons-round">expand_more</span>
  </button>
  <div class="lang-dropdown">
    @foreach($languages as $code => $lang)
      <a href="?lang={{ $code }}" class="lang-option {{ $currentLocale === $code ? 'active' : '' }}">
        <span class="lang-flag">{{ $lang['flag'] }}</span>
        <span>{{ $lang['name'] }}</span>
        @if($currentLocale === $code)
          <span class="material-icons-round" style="margin-left:auto;font-size:14px;color:var(--accent)">check</span>
        @endif
      </a>
    @endforeach
  </div>
</div>