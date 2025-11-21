@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mb-0">
            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $element }}</span>
                    </li>
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
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>
    </nav>
@endif



