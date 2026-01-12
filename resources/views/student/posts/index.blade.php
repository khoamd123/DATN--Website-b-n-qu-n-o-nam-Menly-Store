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
                <div class="d-flex justify-content-between align-items-center mb-4">
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
                
                @if($posts->count() > 0)
                    {{-- Bố cục 2/3 - 1/3 cho bài viết đầu tiên - chỉ hiển thị ở trang đầu tiên --}}
                    @if(request('page', 1) == 1)
                        <div class="row g-4 mb-4">
                            @php
                                $firstPost = $posts->first();
                            @endphp
                            @if($firstPost)
                            {{-- Bài viết lớn - 2/3 --}}
                            <div class="col-lg-8">
                                @php
                                    $imageUrl = null;
                                    if ($firstPost->type !== 'announcement') {
                                        $imageField = $firstPost->image;
                                        if (empty($imageField) && isset($firstPost->attachments) && $firstPost->attachments->count() > 0) {
                                            $firstImageAttachment = $firstPost->attachments->firstWhere('file_type', 'image') ?? $firstPost->attachments->first();
                                            $imageField = $firstImageAttachment->file_url ?? null;
                                        }
                                        if (empty($imageField) && !empty($firstPost->content)) {
                                            if (preg_match('/<img[^>]+src=[\\\"\\\']([^\\\"\\\']+)/i', $firstPost->content, $m)) {
                                                $imageField = $m[1] ?? null;
                                            }
                                        }
                                        if (!empty($imageField)) {
                                            if (\Illuminate\Support\Str::startsWith($imageField, ['http://', 'https://'])) {
                                                $imageUrl = $imageField;
                                            } elseif (\Illuminate\Support\Str::startsWith($imageField, ['/storage/', 'storage/'])) {
                                                $imageUrl = asset(ltrim($imageField, '/'));
                                            } elseif (\Illuminate\Support\Str::startsWith($imageField, ['uploads/', '/uploads/'])) {
                                                $imageUrl = asset(ltrim($imageField, '/'));
                                            } else {
                                                $imageUrl = asset('storage/' . ltrim($imageField, '/'));
                                            }
                                        }
                                    }
                                    
                                    $raw = html_entity_decode($firstPost->content ?? '', ENT_QUOTES, 'UTF-8');
                                    $text = strip_tags($raw);
                                    $text = str_replace("\xc2\xa0", ' ', $text);
                                    $text = preg_replace('/\s+/u', ' ', $text);
                                    $text = preg_replace('/\b[\w\-]+\.(?:jpg|jpeg|png|gif|webp)\b/i', '', $text);
                                    $postExcerpt = trim($text);
                                @endphp
                                <div class="post-item-card-featured rounded overflow-hidden h-100" style="background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onclick="window.location.href='{{ route('student.posts.show', $firstPost->id) }}'">
                                    {{-- Image --}}
                                    @if($imageUrl)
                                        <div class="post-image-container-featured" style="width: 100%; height: 400px; overflow: hidden; background: #f0f0f0;">
                                            <img src="{{ $imageUrl }}" alt="{{ $firstPost->title }}" class="w-100 h-100" style="object-fit: cover; display: block;" onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div style=\'width: 100%; height: 400px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;\'><i class=\'fas fa-newspaper text-white fa-4x\'></i></div>';">
                                        </div>
                                    @else
                                        <div class="post-icon-featured" style="width: 100%; height: 400px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-newspaper text-white fa-4x"></i>
                                        </div>
                                    @endif
                                    
                                    {{-- Content --}}
                                    <div class="p-4">
                                        {{-- Header --}}
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0 me-2">
                                                <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-users text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold text-dark">{{ $firstPost->club->name ?? 'UniClubs' }}</div>
                                                <div class="text-muted" style="font-size: 0.8rem;">
                                                    <i class="fas fa-user-circle me-1"></i>{{ $firstPost->user->name ?? 'Ban quản trị' }}
                                                    <span class="mx-1">•</span>
                                                    <i class="far fa-clock me-1"></i>{{ $firstPost->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Title --}}
                                        <h4 class="fw-bold mb-3 text-dark" style="font-size: 1.5rem; line-height: 1.3;">{{ $firstPost->title }}</h4>
                                        
                                        {{-- Excerpt --}}
                                        <p class="text-muted mb-3" style="font-size: 1rem; line-height: 1.6;">{{ Str::words($postExcerpt, 50, '...') }}</p>
                                        
                                        {{-- Engagement Bar --}}
                                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                            <div class="d-flex align-items-center text-muted" style="font-size: 0.9rem;">
                                                <i class="far fa-eye me-1"></i>
                                                <span>{{ number_format($firstPost->views ?? 0) }} lượt xem</span>
                                                @if(isset($firstPost->comments_count) && $firstPost->comments_count > 0)
                                                    <span class="mx-2">•</span>
                                                    <i class="far fa-comments me-1"></i>
                                                    <span>{{ $firstPost->comments_count }} bình luận</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('student.posts.show', $firstPost->id) }}#comments" class="btn btn-sm btn-outline-teal text-decoration-none" onclick="event.stopPropagation();">
                                                <i class="far fa-comment me-1"></i>
                                                Bình luận
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Các bài viết nhỏ - 1/3 --}}
                            <div class="col-lg-4">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($posts->skip(1)->take(4) as $post)
                                        @php
                                            $imageUrl = null;
                                            if ($post->type !== 'announcement') {
                                                $imageField = $post->image;
                                                if (empty($imageField) && isset($post->attachments) && $post->attachments->count() > 0) {
                                                    $firstImageAttachment = $post->attachments->firstWhere('file_type', 'image') ?? $post->attachments->first();
                                                    $imageField = $firstImageAttachment->file_url ?? null;
                                                }
                                                if (empty($imageField) && !empty($post->content)) {
                                                    if (preg_match('/<img[^>]+src=[\\\"\\\']([^\\\"\\\']+)/i', $post->content, $m)) {
                                                        $imageField = $m[1] ?? null;
                                                    }
                                                }
                                                if (!empty($imageField)) {
                                                    if (\Illuminate\Support\Str::startsWith($imageField, ['http://', 'https://'])) {
                                                        $imageUrl = $imageField;
                                                    } elseif (\Illuminate\Support\Str::startsWith($imageField, ['/storage/', 'storage/'])) {
                                                        $imageUrl = asset(ltrim($imageField, '/'));
                                                    } elseif (\Illuminate\Support\Str::startsWith($imageField, ['uploads/', '/uploads/'])) {
                                                        $imageUrl = asset(ltrim($imageField, '/'));
                                                    } else {
                                                        $imageUrl = asset('storage/' . ltrim($imageField, '/'));
                                                    }
                                                }
                                            }
                                            
                                            $raw = html_entity_decode($post->content ?? '', ENT_QUOTES, 'UTF-8');
                                            $text = strip_tags($raw);
                                            $text = str_replace("\xc2\xa0", ' ', $text);
                                            $text = preg_replace('/\s+/u', ' ', $text);
                                            $text = preg_replace('/\b[\w\-]+\.(?:jpg|jpeg|png|gif|webp)\b/i', '', $text);
                                            $postExcerpt = trim($text);
                                        @endphp
                                        <div class="post-item-card-small rounded overflow-hidden" style="background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onclick="window.location.href='{{ route('student.posts.show', $post->id) }}'">
                                            <div class="d-flex">
                                                {{-- Image --}}
                                                <div class="flex-shrink-0" style="width: 120px; height: 120px; overflow: hidden; background: #f0f0f0;">
                                                    @if($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $post->title }}" class="w-100 h-100" style="object-fit: cover;" onerror="this.onerror=null; this.src=''; this.parentElement.innerHTML='<div style=\'width: 120px; height: 120px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;\'><i class=\'fas fa-newspaper text-white fa-2x\'></i></div>';">
                                                    @else
                                                        <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-newspaper text-white fa-2x"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                {{-- Content --}}
                                                <div class="flex-grow-1 p-3 d-flex flex-column">
                                                    <div class="mb-2">
                                                        <div class="fw-semibold text-dark small mb-1">{{ $post->club->name ?? 'UniClubs' }}</div>
                                                        <div class="text-muted" style="font-size: 0.7rem;">
                                                            <i class="far fa-clock me-1"></i>{{ $post->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-bold mb-2 text-dark" style="font-size: 0.9rem; line-height: 1.3; flex-grow: 1;">{{ Str::limit($post->title, 60) }}</h6>
                                                    @if(isset($post->comments_count) && $post->comments_count > 0)
                                                        <div class="text-muted small mt-auto">
                                                            <i class="far fa-comments me-1"></i>
                                                            <span>{{ $post->comments_count }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    @endif
                    
                    {{-- Danh sách bài viết - từ trang 2 trở đi hoặc các bài viết còn lại ở trang 1 --}}
                    @php
                        $currentPage = request('page', 1);
                        $skipCount = ($currentPage == 1) ? 5 : 0; // Ở trang 1 skip 5 bài đầu (1 featured + 4 sidebar), từ trang 2 trở đi không skip
                    @endphp
                    @if($currentPage > 1 || $posts->count() > 5)
                        <div class="row g-4">
                            <div class="col-12">
                                @foreach($posts->skip($skipCount) as $post)
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
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
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
                @endif

                        @if($posts->hasPages())
                            <div class="pagination-wrapper mt-4">
                                <nav aria-label="Page navigation" class="d-flex justify-content-center">
                                    <ul class="pagination mb-0">
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
                                        @foreach ($posts->appends($queryParams)->getUrlRange(1, $posts->lastPage()) as $page => $url)
                                            @if ($page == $posts->currentPage())
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
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
                        @else
                            <div class="pagination-info mt-3" style="display: flex; align-items: center; gap: 0.5rem; color: #495057; font-size: 0.9rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                                <i class="fas fa-info-circle" style="color: #6c757d;"></i>
                                <span>
                                    Hiển thị <strong>{{ $posts->firstItem() ?? 0 }}</strong> - <strong>{{ $posts->lastItem() ?? 0 }}</strong> 
                                    trong tổng <strong>{{ $posts->total() }}</strong> kết quả
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* Filter buttons - smaller size */
    .btn-group[role="group"] .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1.5rem 0;
    }
    
    .pagination {
        gap: 0.5rem;
        margin: 0;
        display: flex;
        align-items: center;
    }
    
    .pagination .page-item {
        margin: 0;
    }
    
    .pagination .page-link {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        color: #374151;
        padding: 0;
        transition: all 0.2s ease;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    
    .pagination .page-link:hover {
        background-color: #14b8a6;
        border-color: #14b8a6;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(20, 184, 166, 0.25);
    }
    
    .pagination .page-item.active .page-link {
        background-color: #14b8a6;
        border-color: #14b8a6;
        color: white;
        box-shadow: 0 2px 6px rgba(20, 184, 166, 0.35);
        font-weight: 600;
    }
    
    .pagination .page-item.disabled .page-link {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
        opacity: 0.7;
        box-shadow: none;
    }
    
    .pagination .page-item.disabled .page-link:hover {
        transform: none;
        box-shadow: none;
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #9ca3af;
    }
</style>
@endpush

@push('scripts')
@endpush
