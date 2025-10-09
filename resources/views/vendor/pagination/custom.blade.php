@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="pagination-custom">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="page-item disabled" aria-disabled="true" aria-label="&laquo; Previous">
                <span class="page-link" aria-hidden="true">&lsaquo;</span>
            </span>
        @else
            <a class="page-item" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="&laquo; Previous">
                <span class="page-link">&lsaquo;</span>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="page-item disabled"><span class="page-link">{{ $element }}</span></span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></span>
                    @else
                        <a class="page-item" href="{{ $url }}"><span class="page-link">{{ $page }}</span></a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="page-item" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next &raquo;"><span class="page-link">&rsaquo;</span></a>
        @else
            <span class="page-item disabled" aria-disabled="true" aria-label="Next &raquo;"><span class="page-link" aria-hidden="true">&rsaquo;</span></span>
        @endif
    </nav>
@endif
