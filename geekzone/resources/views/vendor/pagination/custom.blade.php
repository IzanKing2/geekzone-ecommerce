@if ($paginator->hasPages())
    <div class="pagination">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <button class="page-btn" disabled style="opacity:.4;cursor:default;">‹</button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="page-btn">‹</a>
        @endif

        {{-- Páginas --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <button class="page-btn" disabled>…</button>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <button class="page-btn active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="page-btn">›</a>
        @else
            <button class="page-btn" disabled style="opacity:.4;cursor:default;">›</button>
        @endif
    </div>
@endif
