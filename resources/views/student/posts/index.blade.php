@extends('layouts.student')

@section('title', 'Bài viết')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-newspaper text-teal"></i> Bài viết
                        </h2>
                        <p class="text-muted mb-0">Khám phá các bài viết và thông báo mới nhất từ các câu lạc bộ</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            @php
                                $currentFilter = request('filter', 'all');
                                $queryParams = request()->except('filter', 'page');
                            @endphp
                            <a href="{{ route('student.posts', array_merge($queryParams, ['filter' => 'all'])) }}" 
                               class="btn btn-outline-primary {{ $currentFilter == 'all' ? 'active' : '' }}">
                                Tất cả
                            </a>
                            <a href="{{ route('student.posts', array_merge($queryParams, ['filter' => 'latest'])) }}" 
                               class="btn btn-outline-primary {{ $currentFilter == 'latest' ? 'active' : '' }}">
                                Mới nhất
                            </a>
                            <a href="{{ route('student.posts', array_merge($queryParams, ['filter' => 'popular'])) }}" 
                               class="btn btn-outline-primary {{ $currentFilter == 'popular' ? 'active' : '' }}">
                                Phổ biến
                            </a>
                </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="row">
                    <!-- Cột bài viết (chiếm toàn bộ) -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-newspaper text-teal me-2"></i>
                                @if(isset($search) && !empty($search))
                                    Kết quả tìm kiếm cho "{{ $search }}"
                                @else
                                    Bài viết
                                @endif
                            </h5>
                            @if(isset($search) && !empty($search))
                                <span class="text-muted small">{{ $posts->total() }} kết quả</span>
                            @endif
                        </div>
                @forelse($posts as $post)
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="row g-0">
                            @php
                                $imageUrl = null;
                                // Không hiển thị ảnh cho bài viết loại thông báo
                                if ($post->type !== 'announcement') {
                                $imageField = $post->image;
                                // Fallback: first attachment if image column is empty
                                if (empty($imageField) && isset($post->attachments) && $post->attachments->count() > 0) {
                                    // Ưu tiên attachment có file_type là image
                                    $firstImageAttachment = $post->attachments->firstWhere('file_type', 'image') ?? $post->attachments->first();
                                    $imageField = $firstImageAttachment->file_url ?? null;
                                }
                                // Fallback: lấy ảnh đầu tiên trong nội dung HTML nếu có
                                if (empty($imageField) && !empty($post->content)) {
                                    if (preg_match('/<img[^>]+src=[\\\"\\\']([^\\\"\\\']+)/i', $post->content, $m)) {
                                        $imageField = $m[1] ?? null;
                                    }
                                }
                                if (!empty($imageField)) {
                                    // Absolute URL
                                    if (\Illuminate\Support\Str::startsWith($imageField, ['http://', 'https://'])) {
                                        $imageUrl = $imageField;
                                    } elseif (\Illuminate\Support\Str::startsWith($imageField, ['/storage/', 'storage/'])) {
                                        $imageUrl = asset(ltrim($imageField, '/'));
                                    } elseif (\Illuminate\Support\Str::startsWith($imageField, ['uploads/', '/uploads/'])) {
                                        // If saved path is like "uploads/..." use public path
                                        $imageUrl = asset(ltrim($imageField, '/'));
                                    } else {
                                        // Otherwise assume stored via Storage (public disk)
                                        $imageUrl = asset('storage/' . ltrim($imageField, '/'));
                                        }
                                    }
                                }
                            @endphp
                            @if($post->type !== 'announcement' && $imageUrl)
                            <div class="col-md-4">
                                <div class="w-100" style="height: 180px; overflow: hidden; border-top-left-radius: .5rem; border-bottom-left-radius: .5rem; background:#f0fdfa; display:flex; align-items:center; justify-content:center;">
                                        <img src="{{ $imageUrl }}" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="{{ $post->title }}">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title mb-2">
                                                <a href="{{ route('student.posts.show', $post->id) }}" class="text-decoration-none text-dark">
                                                    {{ $post->title }}
                                                </a>
                                            </h5>
                                            <span class="badge bg-teal text-white" style="background-color:#14b8a6;">{{ $post->club->name ?? 'UniClubs' }}</span>
                                        </div>
                                        <p class="card-text text-muted mb-2">
                                            @php
                                                $content = strip_tags($post->content ?? '');
                                                $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
                                                $content = preg_replace('/\s+/u', ' ', $content);
                                                $content = trim($content);
                                                $len = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
                                                if ($len > 160) {
                                                    $content = function_exists('mb_substr') 
                                                        ? mb_substr($content, 0, 157) . '...'
                                                        : substr($content, 0, 157) . '...';
                                                }
                                            @endphp
                                            {{ $content }}
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="far fa-user me-1"></i>{{ $post->user->name ?? 'Hệ thống' }}
                                                <span class="mx-2">•</span>
                                                <i class="far fa-clock me-1"></i>{{ $post->created_at->format('d/m/Y H:i') }}
                                                <span class="mx-2">•</span>
                                                <i class="far fa-eye me-1"></i>{{ number_format($post->views ?? 0) }}
                                                @if($post->status === 'members_only')
                                                    <span class="mx-2">•</span>
                                                    <i class="fas fa-lock me-1"></i> Chỉ thành viên
                                                @endif
                                            </small>
                                        </p>
                                        <a href="{{ route('student.posts.show', $post->id) }}" class="btn btn-outline-primary btn-sm">
                                            Đọc tiếp
                                        </a>
                                    </div>
                                </div>
                            @elseif($post->type !== 'announcement')
                                <div class="col-md-4">
                                    <div class="w-100" style="height: 180px; overflow: hidden; border-top-left-radius: .5rem; border-bottom-left-radius: .5rem; background:#f0fdfa; display:flex; align-items:center; justify-content:center;">
                                        <i class="far fa-image" style="font-size:42px;color:#0d9488;opacity:.6;"></i>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title mb-2">
                                            <a href="{{ route('student.posts.show', $post->id) }}" class="text-decoration-none text-dark">
                                                {{ $post->title }}
                                            </a>
                                        </h5>
                                        <span class="badge bg-teal text-white" style="background-color:#14b8a6;">{{ $post->club->name ?? 'UniClubs' }}</span>
                                    </div>
                                    <p class="card-text text-muted mb-2">
                                        @php
                                            $content = strip_tags($post->content ?? '');
                                            $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
                                            $content = preg_replace('/\s+/u', ' ', $content);
                                            $content = trim($content);
                                                $len = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
                                                if ($len > 160) {
                                                    $content = function_exists('mb_substr') 
                                                        ? mb_substr($content, 0, 157) . '...'
                                                        : substr($content, 0, 157) . '...';
                                                }
                                            @endphp
                                            {{ $content }}
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="far fa-user me-1"></i>{{ $post->user->name ?? 'Hệ thống' }}
                                                <span class="mx-2">•</span>
                                                <i class="far fa-clock me-1"></i>{{ $post->created_at->format('d/m/Y H:i') }}
                                                <span class="mx-2">•</span>
                                                <i class="far fa-eye me-1"></i>{{ number_format($post->views ?? 0) }}
                                                @if($post->status === 'members_only')
                                                    <span class="mx-2">•</span>
                                                    <i class="fas fa-lock me-1"></i> Chỉ thành viên
                                                @endif
                                            </small>
                                        </p>
                                        <a href="{{ route('student.posts.show', $post->id) }}" class="btn btn-outline-primary btn-sm">
                                            Đọc tiếp
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="col-12">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title mb-2">
                                                <a href="{{ route('student.posts.show', $post->id) }}" class="text-decoration-none text-dark">
                                                    {{ $post->title }}
                                                </a>
                                            </h5>
                                            <span class="badge bg-teal text-white" style="background-color:#14b8a6;">{{ $post->club->name ?? 'UniClubs' }}</span>
                                        </div>
                                        <p class="card-text text-muted mb-2">
                                            @php
                                                $content = strip_tags($post->content ?? '');
                                                $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
                                                $content = preg_replace('/\s+/u', ' ', $content);
                                                $content = trim($content);
                                            $len = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
                                            if ($len > 160) {
                                                $content = function_exists('mb_substr') 
                                                    ? mb_substr($content, 0, 157) . '...'
                                                    : substr($content, 0, 157) . '...';
                                            }
                                        @endphp
                                        {{ $content }}
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="far fa-user me-1"></i>{{ $post->user->name ?? 'Hệ thống' }}
                                            <span class="mx-2">•</span>
                                            <i class="far fa-clock me-1"></i>{{ $post->created_at->format('d/m/Y H:i') }}
                                            <span class="mx-2">•</span>
                                            <i class="far fa-eye me-1"></i>{{ number_format($post->views ?? 0) }}
                                            @if($post->status === 'members_only')
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-lock me-1"></i> Chỉ thành viên
                                            @endif
                                        </small>
                                    </p>
                                    <a href="{{ route('student.posts.show', $post->id) }}" class="btn btn-outline-primary btn-sm">
                                        Đọc tiếp
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        @if(isset($search) && !empty($search))
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy bài viết nào</h5>
                            <p class="text-muted">Không có kết quả cho từ khóa "<strong>{{ $search }}</strong>"</p>
                            <a href="{{ route('student.posts') }}" class="btn btn-outline-primary mt-2">Xem tất cả bài viết</a>
                        @else
                            <i class="far fa-newspaper fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Chưa có bài viết nào.</p>
                        @endif
                    </div>
                @endforelse

                        @if($posts->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0">
                                        @php
                                            $queryParams = request()->except('page');
                                            $previousUrl = $posts->onFirstPage() ? '#' : $posts->appends($queryParams)->previousPageUrl();
                                            $nextUrl = $posts->hasMorePages() ? $posts->appends($queryParams)->nextPageUrl() : '#';
                                        @endphp
                                        {{-- Previous Page Link --}}
                                        @if ($posts->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="fas fa-chevron-left"></i>
                                                </span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $previousUrl }}" rel="prev">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                                            @php
                                                $pageUrl = $page == $posts->currentPage() ? '#' : $posts->appends($queryParams)->url($page);
                                            @endphp
                                            @if ($page == $posts->currentPage())
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $pageUrl }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        @if ($posts->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $nextUrl }}" rel="next">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="fas fa-chevron-right"></i>
                                                </span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    Hiển thị {{ $posts->firstItem() }} đến {{ $posts->lastItem() }} trong tổng số {{ $posts->total() }} kết quả
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal thông báo đã được ẩn --}}
@endsection

@push('styles')
<style>
    /* Filter buttons - smaller size */
    .btn-group[role="group"] .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .pagination {
        gap: 0.25rem;
    }
    
    .pagination .page-link {
        border-radius: 4px;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 0.375rem 0.75rem;
        transition: all 0.3s ease;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
    }
    
    .pagination .page-link:hover {
        background-color: #14b8a6;
        border-color: #14b8a6;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(20, 184, 166, 0.3);
    }
    
    .pagination .page-item.active .page-link {
        background-color: #14b8a6;
        border-color: #14b8a6;
        color: white;
        box-shadow: 0 2px 4px rgba(20, 184, 166, 0.3);
    }
    
    .pagination .page-item.disabled .page-link {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .pagination .page-item.disabled .page-link:hover {
        transform: none;
        box-shadow: none;
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
{{-- Script modal thông báo đã được ẩn --}}
@endpush
