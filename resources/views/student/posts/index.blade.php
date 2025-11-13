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
                        <div class="btn-group" role="group">
                            @php
                                $currentFilter = request('filter', 'all');
                                $queryParams = request()->except('filter', 'page');
                            @endphp
                            <a href="{{ route('student.posts', array_merge($queryParams, ['filter' => 'all'])) }}" 
                               class="btn {{ $currentFilter == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Tất cả
                            </a>
                            <a href="{{ route('student.posts', array_merge($queryParams, ['filter' => 'latest'])) }}" 
                               class="btn {{ $currentFilter == 'latest' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Mới nhất
                            </a>
                            <a href="{{ route('student.posts', array_merge($queryParams, ['filter' => 'popular'])) }}" 
                               class="btn {{ $currentFilter == 'popular' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Phổ biến
                            </a>
                </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <form method="GET" action="{{ route('student.posts') }}" class="mb-3">
                    @if(request('filter'))
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                    @endif
                    <div class="row g-3">
                    <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       class="form-control" 
                                       placeholder="Tìm kiếm bài viết...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Câu lạc bộ</label>
                        <select name="club_id" class="form-select">
                            <option value="">Tất cả CLB</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ (string)request('club_id') === (string)$club->id ? 'selected' : '' }}>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Loại bài viết</label>
                            <select name="type" class="form-select">
                                <option value="">Tất cả loại</option>
                                <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>Bài viết thường</option>
                                <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                                <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Tài liệu</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-teal w-100" style="background-color: #14b8a6; color: white;">
                                <i class="fas fa-filter me-1"></i> Lọc
                        </button>
                        </div>
                    </div>
                    @if(request('search') || request('club_id') || request('type'))
                        <div class="mt-2">
                            <a href="{{ route('student.posts', ['filter' => request('filter')]) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Xóa bộ lọc
                            </a>
                        </div>
                    @endif
                </form>

                <div class="row">
                    <!-- Cột trái: Bài viết (chiếm 8/12) -->
                    <div class="col-lg-8">
                        <h5 class="mb-3">
                            <i class="fas fa-newspaper text-teal me-2"></i>Bài viết
                        </h5>
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
                        <i class="far fa-newspaper fa-2x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Chưa có bài viết nào.</p>
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

                    <!-- Cột phải: Thông báo (chiếm 4/12) -->
                    <div class="col-lg-4">
                        <div>
                            <h5 class="mb-3">
                                <i class="fas fa-bullhorn text-warning me-2"></i>Thông báo
                            </h5>
                            @if(isset($announcements) && $announcements->count() > 0)
                                <div class="list-group">
                                    @foreach($announcements as $announcement)
                                        <a href="{{ route('student.posts.show', $announcement->id) }}" 
                                           class="list-group-item list-group-item-action border-0 shadow-sm mb-2 rounded">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold" style="font-size: 14px;">
                                                        {{ \Illuminate\Support\Str::limit($announcement->title, 50) }}
                                                    </h6>
                                                    <p class="mb-1 text-muted small" style="font-size: 12px; line-height: 1.4;">
                                                        @php
                                                            $content = strip_tags($announcement->content ?? '');
                                                            $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
                                                            $content = preg_replace('/\s+/u', ' ', $content);
                                                            $content = trim($content);
                                                            $len = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
                                                            if ($len > 80) {
                                                                $content = function_exists('mb_substr') 
                                                                    ? mb_substr($content, 0, 77) . '...'
                                                                    : substr($content, 0, 77) . '...';
                                                            }
                                                        @endphp
                                                        {{ $content }}
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock me-1"></i>{{ $announcement->created_at->format('d/m/Y') }}
                                                        @if($announcement->club)
                                                            <span class="mx-1">•</span>
                                                            <i class="fas fa-users me-1"></i>{{ $announcement->club->name }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-bullhorn fa-2x text-muted mb-2"></i>
                                    <p class="text-muted small mb-0">Chưa có thông báo nào.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcement Modal -->
    @if(isset($latestAnnouncement) && $latestAnnouncement && isset($shouldShowModal) && $shouldShowModal)
            <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="border: 1px solid #dee2e6; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 16px 24px;">
                            <h5 class="modal-title fw-bold mb-0" id="announcementModalLabel" style="color: #333; font-size: 18px;">
                                THÔNG BÁO
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="padding: 24px; max-height: 70vh; overflow-y: auto;">
                            <h6 class="mb-3 fw-bold" style="color: #0d6efd; font-size: 16px; text-transform: uppercase;">
                                {{ $latestAnnouncement->title }}
                            </h6>
                            <div style="line-height: 1.8; color: #333; font-size: 14px;">
                                @php
                                    // Format content để hiển thị đẹp hơn
                                    $content = $latestAnnouncement->content;
                                    // Chuyển đổi các thẻ HTML thành text đẹp hơn
                                    $content = strip_tags($content, '<p><br><strong><b><em><i><ul><ol><li><a>');
                                    // Thêm spacing cho các thẻ
                                    $content = str_replace(['</p>', '</div>'], ['</p><br>', '</div><br>'], $content);
                                @endphp
                                {!! $content !!}
                            </div>
                            @if($latestAnnouncement->club)
                                <div class="mt-4 pt-3 border-top">
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>{{ $latestAnnouncement->club->name }}
                                        <span class="mx-2">•</span>
                                        <i class="far fa-clock me-1"></i>{{ $latestAnnouncement->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
    @endif
@endsection

@push('styles')
<style>
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
@if(isset($latestAnnouncement) && $latestAnnouncement && isset($shouldShowModal) && $shouldShowModal)
<script>
document.addEventListener('DOMContentLoaded', function() {
    var announcementModal = new bootstrap.Modal(document.getElementById('announcementModal'));
    announcementModal.show();
});

function markAnnouncementAsViewed(announcementId) {
    // Gửi request để đánh dấu đã xem - chỉ cập nhật nếu thông báo mới hơn
    // Điều này cho phép modal hiển thị lại mỗi lần vào trang
    fetch('{{ route("student.posts.mark-announcement-viewed") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            announcement_id: announcementId
        })
    }).catch(function(error) {
        console.error('Error marking announcement as viewed:', error);
    });
}

// Không tự động đánh dấu khi đóng modal
// Modal sẽ tiếp tục hiển thị mỗi lần vào trang cho đến khi có thông báo mới
</script>
@endif
@endpush
