@if ($paginator->hasPages())
<div class="adm-pagination">
  <!-- Info -->
  <div class="adm-pag-info">
    <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
    <span style="color:var(--text-muted)">dan</span>
    <strong>{{ number_format($paginator->total()) }}</strong>
  </div>

  <!-- Buttons -->
  <div class="adm-pag-btns">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
      <span class="adm-pag-btn disabled">
        <span class="material-icons-round">chevron_left</span>
      </span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="adm-pag-btn">
        <span class="material-icons-round">chevron_left</span>
      </a>
    @endif

    {{-- Pages --}}
    @php
      $current = $paginator->currentPage();
      $last = $paginator->lastPage();
      $start = max(1, $current - 2);
      $end = min($last, $current + 2);
    @endphp

    @if ($start > 1)
      <a href="{{ $paginator->url(1) }}" class="adm-pag-btn">1</a>
      @if ($start > 2)<span class="adm-pag-dots">…</span>@endif
    @endif

    @for ($i = $start; $i <= $end; $i++)
      @if ($i === $current)
        <span class="adm-pag-btn active">{{ $i }}</span>
      @else
        <a href="{{ $paginator->url($i) }}" class="adm-pag-btn">{{ $i }}</a>
      @endif
    @endfor

    @if ($end < $last)
      @if ($end < $last - 1)<span class="adm-pag-dots">…</span>@endif
      <a href="{{ $paginator->url($last) }}" class="adm-pag-btn">{{ $last }}</a>
    @endif

    {{-- Next --}}
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="adm-pag-btn">
        <span class="material-icons-round">chevron_right</span>
      </a>
    @else
      <span class="adm-pag-btn disabled">
        <span class="material-icons-round">chevron_right</span>
      </span>
    @endif
  </div>
</div>
@endif