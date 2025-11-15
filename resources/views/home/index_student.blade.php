@extends('layouts.student')

@section('title', 'UniClubs - Trang chủ')
@section('page_title', 'Trang chủ')

@php
    use Illuminate\Support\Str;
    $statColors = [
        'clubs' => '#0ea5e9',
        'members' => '#f97316',
        'events' => '#6366f1',
        'posts' => '#22c55e',
    ];
    $statIcons = [
        'clubs' => 'fa-users',
        'members' => 'fa-user-friends',
        'events' => 'fa-calendar-days',
        'posts' => 'fa-newspaper',
    ];
@endphp

@section('content')
    {{-- Removed summary stats section per request --}}

    {{-- Đưa khung tìm kiếm lên trên --}}
    <div id="club-search" class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('home') }}" class="row gy-3 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Tìm kiếm CLB</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Tên hoặc mô tả CLB" value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3">
                <label for="field" class="form-label">Lĩnh vực</label>
                <select name="field" id="field" class="form-select">
                    <option value="">Tất cả lĩnh vực</option>
                    @foreach($fields as $field)
                        <option value="{{ $field->id }}" @selected($fieldId == $field->id)>{{ $field->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="sort" class="form-label">Sắp xếp</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="popular" @selected($sort === 'popular')>Phổ biến</option>
                    <option value="newest" @selected($sort === 'newest')>Mới nhất</option>
                    <option value="name" @selected($sort === 'name')>Theo tên</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">
                    Lọc kết quả
                </button>
            </div>
        </form>
    </div>

    @php
        $joinedClubIds = $user && $user->clubs ? $user->clubs->pluck('id')->toArray() : [];
    @endphp

    @if($featuredClubs->count())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Câu lạc bộ nổi bật</h5>
            <span class="text-muted small">{{ $clubs->total() }} CLB</span>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 mb-4">
            @foreach($featuredClubs as $club)
                @php $joined = in_array($club->id, $joinedClubIds); @endphp
                <div class="col">
                    <div class="card h-100 shadow-sm club-featured-card">
                        @php
                            $logoUrl = null;
                            $hasLogo = false;
                            if ($club->logo) {
                                $logoPath = $club->logo;
                                // Kiểm tra nếu là URL đầy đủ
                                if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
                                    $logoUrl = $logoPath;
                                    $hasLogo = true;
                                } else {
                                    // Logo được lưu trong public/uploads/clubs/logos/...
                                    $fullPath = public_path($logoPath);
                                    if (file_exists($fullPath)) {
                                        $logoUrl = asset($logoPath);
                                        $hasLogo = true;
                                    }
                                }
                            }
                        @endphp
                        @if($hasLogo && $logoUrl)
                            <div class="club-image-container" style="height: 180px; overflow: hidden; background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%); position: relative;">
                                <img src="{{ $logoUrl }}" alt="{{ $club->name }}" class="w-100 h-100" style="object-fit: cover;" 
                                     onerror="this.onerror=null; this.src=''; this.parentElement.innerHTML='<div style=\'height: 180px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;\'><div class=\'text-white text-center\'><i class=\'fas fa-users fa-3x mb-2\'></i><div class=\'fw-bold\' style=\'font-size: 1.5rem;\'>{{ substr($club->name, 0, 2) }}</div></div></div>';">
                            </div>
                        @else
                            <div class="club-image-placeholder" style="height: 180px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;">
                                <div class="text-white text-center">
                                    <i class="fas fa-users fa-3x mb-2"></i>
                                    <div class="fw-bold" style="font-size: 1.5rem;">{{ substr($club->name, 0, 2) }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-tags me-1"></i>{{ $club->field->name ?? 'Lĩnh vực khác' }}
                                </span>
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>{{ number_format($club->active_members_count) }} thành viên
                                </small>
                            </div>
                            <h6 class="fw-bold mb-2">
                                {{ $club->name }}
                                @if($joined)
                                    <span class="badge bg-success ms-1">Đã gia nhập</span>
                                @endif
                            </h6>
                            @php $clubDescription = strip_tags(html_entity_decode($club->description ?? '', ENT_QUOTES, 'UTF-8')); @endphp
                            <p class="text-muted small flex-grow-1 mb-3">{{ Str::words($clubDescription, 24, '...') }}</p>
                            <div class="d-flex gap-2">
                                @if(!$joined)
                                    <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-primary">
                                        Tham gia ngay
                                    </a>
                                @else
                                    <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-outline-secondary">
                                        Xem CLB của tôi
                                    </a>
                                @endif
                                <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-outline-secondary">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Khung tìm kiếm đã được đưa lên trước --}}

    <div class="row g-4 align-items-stretch" id="events">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100 event-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-calendar-alt text-teal me-2"></i>Sự kiện sắp diễn ra
                        </h4>
                        <a href="{{ route('student.events.index') }}" class="text-decoration-none text-teal fw-semibold">
                            Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    @if($upcomingEvents->count())
                        <div class="event-list">
                            @foreach($upcomingEvents->take(3) as $event)
                                @php
                                    $hasImages = $event->images && $event->images->count() > 0;
                                    $hasOldImage = !empty($event->image);
                                @endphp
                                <a href="{{ route('student.events.show', $event->id) }}" class="event-item-link text-decoration-none">
                                    <div class="event-item-home mb-4 rounded overflow-hidden">
                                        <div class="d-flex">
                                            @if($hasImages || $hasOldImage)
                                                <div class="event-image-thumb">
                                                    @if($hasImages)
                                                        <img src="{{ $event->images->first()->image_url }}" 
                                                             alt="{{ $event->title }}" 
                                                             class="w-100 h-100" 
                                                             style="object-fit: cover;">
                                                    @elseif($hasOldImage)
                                                        <img src="{{ asset('storage/' . $event->image) }}" 
                                                             alt="{{ $event->title }}" 
                                                             class="w-100 h-100" 
                                                             style="object-fit: cover;">
                                                    @endif
                                                </div>
                                            @else
                                                <div class="event-date-badge-home">
                                                    <div class="date-day">{{ optional($event->start_time)->translatedFormat('d') }}</div>
                                                    <div class="date-month">{{ optional($event->start_time)->translatedFormat('M') }}</div>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1 p-4">
                                                <h5 class="fw-bold mb-2 text-dark">{{ $event->title }}</h5>
                                                <div class="text-muted mb-2">
                                                    <i class="fas fa-users me-1 text-teal"></i>{{ $event->club->name ?? 'CLB' }}
                                                    <span class="mx-2">•</span>
                                                    <i class="fas fa-clock me-1 text-teal"></i>{{ optional($event->start_time)->format('H:i d/m/Y') }}
                                                </div>
                                                @if($event->location)
                                                    <div class="text-muted">
                                                        <i class="fas fa-location-dot me-1 text-teal"></i>{{ Str::limit($event->location, 35) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Hiện chưa có sự kiện nào sắp diễn ra.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5" id="posts">
            <div class="card border-0 shadow-sm h-100 post-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-newspaper text-teal me-2"></i>Bài viết mới
                        </h4>
                        <a href="{{ route('student.posts') }}" class="text-decoration-none text-teal fw-semibold">
                            Xem thêm <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    @if($recentPosts->count())
                        <div class="post-list">
                            @foreach($recentPosts->take(3) as $post)
                                @php
                                    $imageUrl = null;
                                    $imageField = $post->image;
                                    
                                    // Xử lý ảnh từ trường image
                                    if (!empty($imageField)) {
                                        if (\Illuminate\Support\Str::startsWith($imageField, ['http://', 'https://'])) {
                                            $imageUrl = $imageField;
                                        } elseif (\Illuminate\Support\Str::startsWith($imageField, ['uploads/', '/uploads/'])) {
                                            $imageUrl = asset(ltrim($imageField, '/'));
                                        } elseif (\Illuminate\Support\Str::startsWith($imageField, ['storage/', '/storage/'])) {
                                            $imageUrl = asset(ltrim($imageField, '/'));
                                        } else {
                                            $imageUrl = asset('storage/' . ltrim($imageField, '/'));
                                        }
                                    }
                                    
                                    // Fallback: lấy ảnh từ attachments nếu không có trong image field
                                    if (empty($imageUrl) && $post->attachments && $post->attachments->count() > 0) {
                                        $firstImageAttachment = $post->attachments->firstWhere('file_type', 'image') ?? $post->attachments->first();
                                        if ($firstImageAttachment && isset($firstImageAttachment->file_url)) {
                                            $fileUrl = $firstImageAttachment->file_url;
                                            if (\Illuminate\Support\Str::startsWith($fileUrl, ['http://', 'https://'])) {
                                                $imageUrl = $fileUrl;
                                            } elseif (\Illuminate\Support\Str::startsWith($fileUrl, ['uploads/', '/uploads/'])) {
                                                $imageUrl = asset(ltrim($fileUrl, '/'));
                                            } else {
                                                $imageUrl = asset(ltrim($fileUrl, '/'));
                                            }
                                        }
                                    }
                                    
                                    $hasImage = !empty($imageUrl);
                                @endphp
                                <a href="{{ route('student.posts.show', $post->id) }}" class="post-item-link text-decoration-none">
                                    <div class="post-item-home mb-4 rounded overflow-hidden border">
                                        <div class="d-flex">
                                            @if($hasImage)
                                                <div class="post-image-thumb">
                                                    <img src="{{ $imageUrl }}" 
                                                         alt="{{ $post->title }}" 
                                                         class="w-100 h-100" 
                                                         style="object-fit: cover;"
                                                         onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div class=\'post-icon-placeholder\'><i class=\'fas fa-newspaper\'></i></div>';">
                                                </div>
                                            @else
                                                <div class="post-icon-placeholder">
                                                    <i class="fas fa-newspaper"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1 p-4 d-flex flex-column">
                                                <h5 class="fw-bold mb-2 text-dark">{{ $post->title }}</h5>
                                                @php
                                                    $raw = html_entity_decode($post->content ?? '', ENT_QUOTES, 'UTF-8');
                                                    $text = strip_tags($raw);
                                                    $text = str_replace("\xc2\xa0", ' ', $text); // &nbsp;
                                                    $text = preg_replace('/\s+/u', ' ', $text);
                                                    $text = preg_replace('/\b[\w\-]+\.(?:jpg|jpeg|png|gif|webp)\b/i', '', $text);
                                                    $postExcerpt = trim($text);
                                                @endphp
                                                <p class="text-muted mb-3 flex-grow-1">{{ Str::words($postExcerpt, 15, '...') }}</p>
                                                <div class="align-self-start">
                                                    <span class="btn btn-sm btn-outline-teal">
                                                        Xem thêm <i class="fas fa-arrow-right ms-1"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-newspaper fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Chưa có bài viết công khai.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bỏ Lĩnh vực hoạt động --}}

    {{-- Modal Thông báo --}}
    @if(isset($latestAnnouncement) && $latestAnnouncement)
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 0; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="border-bottom: 1px solid #e0e0e0; padding: 1.5rem 2rem; position: relative;">
                    <div class="w-100 text-center">
                        <h1 class="modal-title fw-bold m-0" id="announcementModalLabel" style="font-size: 1.3rem; color: #333; text-transform: uppercase; letter-spacing: 1px;">
                            THÔNG BÁO
                        </h1>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 1rem; right: 1rem; font-size: 1rem; opacity: 0.7;"></button>
                </div>
                <div class="modal-body" style="padding: 2rem; max-height: calc(100vh - 60px); overflow-y: auto;">
                    <h2 class="mb-3" style="font-size: 1.3rem; color: #333; font-weight: 600; line-height: 1.4;">
                        {{ $latestAnnouncement->title }}
                    </h2>
                    <div class="announcement-content" style="line-height: 1.8; color: #333; font-size: 1rem;">
                        {!! $latestAnnouncement->content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('styles')
<style>
    .club-featured-card {
        transition: all 0.3s ease;
        border-radius: 16px;
        overflow: hidden;
    }
    
    .club-featured-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
    }
    
    .club-image-container {
        border-radius: 16px 16px 0 0;
    }
    
    .club-image-container img {
        transition: transform 0.3s ease;
    }
    
    .club-featured-card:hover .club-image-container img {
        transform: scale(1.05);
    }
    
    .club-image-placeholder {
        border-radius: 16px 16px 0 0;
    }
    
    /* Event Card Styles */
    .event-card {
        border-radius: 16px;
    }
    
    .event-item-link {
        display: block;
    }
    
    .event-item-home {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .event-item-home:hover {
        background: #f0fdfa;
        border-color: #14b8a6;
        transform: translateX(4px);
        box-shadow: 0 4px 8px rgba(20, 184, 166, 0.15);
    }
    
    .event-image-thumb {
        width: 150px;
        min-width: 150px;
        height: 150px;
        overflow: hidden;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%);
        flex-shrink: 0;
    }
    
    .event-image-thumb img {
        transition: transform 0.3s ease;
    }
    
    .event-item-home:hover .event-image-thumb img {
        transform: scale(1.1);
    }
    
    .event-date-badge-home {
        min-width: 120px;
        width: 120px;
        text-align: center;
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        color: white;
        padding: 1.5rem 0.75rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
    }
    
    .event-date-badge-home .date-day {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
    }
    
    .event-date-badge-home .date-month {
        font-size: 0.875rem;
        text-transform: uppercase;
        margin-top: 0.5rem;
        font-weight: 600;
    }
    
    /* Post Card Styles */
    .post-card {
        border-radius: 16px;
    }
    
    .post-item-link {
        display: block;
    }
    
    .post-item-home {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .post-item-home:hover {
        background: #f0fdfa;
        border-color: #14b8a6;
        transform: translateX(4px);
        box-shadow: 0 4px 8px rgba(20, 184, 166, 0.15);
    }
    
    .post-item-link {
        display: block;
        color: inherit;
    }
    
    .post-item-link:hover {
        color: inherit;
        text-decoration: none;
    }
    
    .post-image-thumb {
        width: 200px;
        min-width: 200px;
        height: 200px;
        overflow: hidden;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%);
        flex-shrink: 0;
    }
    
    .post-image-thumb img {
        transition: transform 0.3s ease;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .post-item-home:hover .post-image-thumb img {
        transform: scale(1.05);
    }
    
    .post-icon-placeholder {
        min-width: 200px;
        width: 200px;
        height: 200px;
        text-align: center;
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
    }
    
    .post-icon-placeholder i {
        font-size: 4rem;
    }
    
    .btn-outline-teal {
        color: #14b8a6;
        border-color: #14b8a6;
    }
    
    .btn-outline-teal:hover {
        background-color: #14b8a6;
        border-color: #14b8a6;
        color: white;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    /* Modal Announcement Styles */
    #announcementModal .modal-content {
        animation: modalFadeIn 0.3s ease-out;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #announcementModal .modal-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: nowrap !important;
        visibility: visible !important;
    }
    
    #announcementModal .modal-title {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-size: 2rem !important;
        color: #333 !important;
        margin: 0 !important;
        padding: 0 !important;
        flex: 1 1 auto !important;
        margin-right: 1rem !important;
    }
    
    #announcementModal .modal-title::before,
    #announcementModal .modal-title::after {
        display: none !important;
    }
    
    #announcementModal .modal-dialog {
        max-width: 85%;
    }
    
    #announcementModal .modal-body {
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }
    
    #announcementModal .announcement-content {
        max-height: none;
        overflow-y: visible;
    }
    
    #announcementModal .announcement-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }
    
    #announcementModal .announcement-content p {
        margin-bottom: 1rem;
    }
    
    #announcementModal .modal-title::after {
        display: none !important;
    }
    
    #announcementModal .modal-dialog {
        max-width: 85%;
    }
    
    #announcementModal .modal-body {
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }
    
    #announcementModal .announcement-content {
        max-height: none;
        overflow-y: visible;
    }
    
    #announcementModal .announcement-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }
</style>
@endpush

@push('scripts')
<script>
    @if(isset($latestAnnouncement) && $latestAnnouncement)
    document.addEventListener('DOMContentLoaded', function() {
        // Hiển thị modal mỗi khi trang load
        var modalElement = document.getElementById('announcementModal');
        if (modalElement) {
            // Thêm delay nhỏ để đảm bảo Bootstrap đã load
            setTimeout(function() {
                var modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            }, 100);
        }
    });
    @endif
</script>
@endpush

