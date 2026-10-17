@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Navigasi halaman">
        <span>
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }}
        </span>
        <div>
            @if ($paginator->onFirstPage())
                <span class="page disabled" aria-disabled="true">
                    ← Sebelumnya
                </span>
            @else
                <a
                    class="page"
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                >
                    ← Sebelumnya
                </a>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page">
                        {{ $element }}
                    </span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page current" aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <a
                                class="page"
                                href="{{ $url }}"
                                aria-label="Halaman {{ $page }}"
                            >
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a
                    class="page"
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                >
                    Berikutnya →
                </a>
            @else
                <span class="page disabled" aria-disabled="true">
                    Berikutnya →
                </span>
            @endif
        </div>
    </nav>
@endif
