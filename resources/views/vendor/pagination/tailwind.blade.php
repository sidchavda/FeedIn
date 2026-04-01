@if ($paginator->hasPages())
    <div class="d-flex align-items-center">
        <div>
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </div>

        <div class="ms-auto">
            <nav aria-label="Page navigation" class="pagination-style-4">
                <ul class="pagination mb-0">
                    @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <a class="page-link" href="javascript:void(0);">
                            Prev
                        </a>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link text-primary" href="{{ $paginator->previousPageUrl() }}">
                            Prev
                        </a>
                    </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item"><a class="page-link" href="javascript:void(0);">{{ $element }}</a></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active"><a class="page-link" href="javascript:void(0);">{{ $page }}</a></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link text-primary" href="{{ $paginator->nextPageUrl() }}">
                                next
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <a class="page-link" href="javascript:void(0);">
                                next
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>


@endif
