@if ($paginator->hasPages())
    <div class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled">
                <span class="pagination-icon">←</span>
                Previous
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn">
                <span class="pagination-icon">←</span>
                Previous
            </a>
        @endif

        {{-- Page Numbers --}}
        <div class="pagination-pages">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-dots">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-page active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-page">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn">
                Next
                <span class="pagination-icon">→</span>
            </a>
        @else
            <span class="pagination-btn disabled">
                Next
                <span class="pagination-icon">→</span>
            </span>
        @endif
    </div>
@endif