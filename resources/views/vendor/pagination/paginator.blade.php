@if ($paginator->hasPages())
    <div class="flex">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <div class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <span class="p-2 border rounded mr-2 text-xl" aria-hidden="true">&lsaquo;</span>
            </div>
        @else
            <div>
                <a class="p-2 border rounded mr-2 text-xl hover:bg-gray-700 hover:text-white"
                   href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
            </div>
        @endif

        <div class="flex pagination">
            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <div class="disabled" aria-disabled="true"><span>{{ $element }}</span></div>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <div aria-current="page">
                                <span class="page-marker bg-blue-400 text-white border m-1/2 p-2 text-xl">{{ $page }}</span>
                            </div>
                        @else
                            <div><a class="page-marker hover:bg-gray-700 hover:text-white border m-1/2 p-2 text-xl" href="{{ $url }}">{{ $page }}</a></div>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <div>
                <a class="p-2 border rounded ml-2 text-xl hover:bg-gray-700 hover:text-white"
                   href="{{ $paginator->nextPageUrl() }}" rel="next"
                   aria-label="@lang('pagination.next')">&rsaquo;</a>
            </div>
        @else
            <div class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                <span class="p-2 border rounded ml-2 text-xl" aria-hidden="true">&rsaquo;</span>
            </div>
        @endif
    </div>
@endif