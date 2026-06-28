@if ($paginator->hasPages())
<nav class="pagination">
    @if ($paginator->onFirstPage())
        <span class="disabled"><span class="material-icons-round" style="font-size:18px">chevron_left</span></span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"><span class="material-icons-round" style="font-size:18px">chevron_left</span></a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="disabled">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"><span class="material-icons-round" style="font-size:18px">chevron_right</span></a>
    @else
        <span class="disabled"><span class="material-icons-round" style="font-size:18px">chevron_right</span></span>
    @endif
</nav>
@endif