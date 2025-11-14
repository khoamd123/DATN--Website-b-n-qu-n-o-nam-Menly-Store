@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">
            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @php
                            // Bỏ qua các phần tử Previous/Next nếu Laravel tự động thêm chúng
                            $isPreviousNext = false;
                            if (is_string($page)) {
                                $pageLower = strtolower($page);
                                if (strpos($pageLower, 'previous') !== false || 
                                    strpos($pageLower, 'next') !== false ||
                                    $page === '«' || $page === '»' || 
                                    $page === '‹' || $page === '›' ||
                                    $page === '&laquo;' || $page === '&raquo;' ||
                                    $page === '&lsaquo;' || $page === '&rsaquo;') {
                                    $isPreviousNext = true;
                                }
                            }
                        @endphp
                        
                        @if (!$isPreviousNext)
                            @if ($page == $paginator->currentPage())
                                <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>
    </nav>
@endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
