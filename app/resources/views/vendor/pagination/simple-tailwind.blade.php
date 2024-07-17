@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="w-full flex justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span>
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="hover:underline">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="hover:underline">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span>
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
