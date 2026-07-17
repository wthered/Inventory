@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination Navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled" aria-disabled="true">
                <span class="pagination-icon">←</span>
                {{ __('Προηγούμενη') }}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev">
                <span class="pagination-icon">←</span>
                {{ __('Προηγούμενη') }}
            </a>
        @endif

        {{-- Page Numbers --}}
        <div class="pagination-pages">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-dots" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-page">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" rel="next">
                {{ __('Επόμενη') }}
                <span class="pagination-icon">→</span>
            </a>
        @else
            <span class="pagination-btn disabled" aria-disabled="true">
                {{ __('Επόμενη') }}
                <span class="pagination-icon">→</span>
            </span>
        @endif
    </nav>
@endif