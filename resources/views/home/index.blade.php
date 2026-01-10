@extends('layouts.public')

@section('title', 'UniClubs - Khám phá câu lạc bộ & sự kiện sinh viên')

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
@push('styles')
<style>
    /* Minimal overrides for homepage */
    .hero-section { background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); color: white; padding: 1rem 0 2rem 0; }
    .hero-section h1 { font-weight: 800; font-size: 2rem; margin-bottom: .5rem; color: white; }
    .hero-section p.lead { color: rgba(255,255,255,0.9); margin-bottom: 1rem; }
    .btn-minimal { border-radius: 999px; padding: .5rem 1rem; }
    .club-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem; background: #fff; box-shadow: none; }
    .club-card h5 { margin-bottom: .25rem; font-weight: 700; font-size: 1rem; }
    .club-card p { margin-bottom: .75rem; color: #6b7280; font-size: .92rem; }
    .list-group-item { padding-left: 0; padding-right: 0; }
    .section-title { font-weight: 700; font-size: 1.125rem; margin: 0; }
    .nav-link.active { background: rgba(255,255,255,0.2) !important; border-radius: 8px; }
    .bg-teal { background-color: #0f766e !important; }
</style>
@endpush

    <section class="py-5">
        <div class="container">
            {{-- Banner Carousel Section --}}
            <div class="mb-5">
                @php
                    $bannerDir = public_path('images/banners');
                    $existingBanners = [];
                    if (is_dir($bannerDir)) {
                        $files = scandir($bannerDir);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..') {
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                                    $existingBanners[] = $file;
                                }
                            }
                        }
                    }
                @endphp
                
                @if(count($existingBanners) > 0)
                    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                        <div class="carousel-indicators">
                            @foreach($existingBanners as $index => $banner)
                                <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="{{ $index }}" 
                                        class="{{ $index === 0 ? 'active' : '' }}" 
                                        aria-current="{{ $index === 0 ? 'true' : 'false' }}" 
                                        aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                        <div class="carousel-inner rounded-4 overflow-hidden shadow-sm">
                            @foreach($existingBanners as $index => $banner)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img src="{{ asset('images/banners/' . $banner) }}" 
                                         class="d-block w-100" 
                                         alt="Banner {{ $index + 1 }}"
                                         style="height: 400px; object-fit: cover;">
                                    <div class="carousel-caption d-none d-md-block">
                                        <div class="bg-dark bg-opacity-50 rounded p-3">
                                            <h3 class="fw-bold mb-2">Chào mừng đến với UniClubs</h3>
                                            <p class="lead mb-3">Nơi kết nối sinh viên và câu lạc bộ</p>
                                            <a href="{{ route('student.clubs.index') }}" class="btn btn-light btn-lg px-4">
                                                <i class="fas fa-rocket me-2"></i>Khám phá ngay
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if(count($existingBanners) > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        @endif
                    </div>
                @else
                    {{-- Fallback banner nếu chưa có ảnh --}}
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); border-radius: 16px; overflow: hidden;">
                        <div class="card-body p-5 text-white text-center">
                            <h3 class="fw-bold mb-3">Chào mừng đến với UniClubs</h3>
                            <p class="lead mb-4">Nơi kết nối sinh viên và câu lạc bộ</p>
                            <a href="{{ route('student.clubs.index') }}" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-rocket me-2"></i>Khám phá ngay
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            @if(!$search && $featuredClubs->count())
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">Câu lạc bộ nổi bật</h2>
                    <span class="text-muted small">{{ $clubs->total() }} CLB</span>
                </div>
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
                    @foreach($featuredClubs as $club)
                        <div class="col">
                            <div class="club-card h-100 p-4">
                                <h5 class="fw-bold mb-2">{{ $club->name }}</h5>
                                @php $clubDescription = strip_tags(html_entity_decode($club->description ?? '', ENT_QUOTES, 'UTF-8')); @endphp
                                <p class="text-muted mb-3">{{ Str::words($clubDescription, 22, '...') }}</p>
                                <a href="{{ route('student.clubs.index') }}" class="btn btn-outline-teal btn-sm btn-minimal">Xem chi tiết</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h4 fw-bold mb-0">
                        @if($search)
                            Kết quả tìm kiếm
                        @else
                            Danh sách câu lạc bộ
                        @endif
                    </h3>
                    <span class="text-muted small">
                        {{ $clubs->total() }} kết quả
                        @if($search)
                            cho từ khóa "<strong>{{ $search }}</strong>"
                        @endif
                        @if($fieldId)
                            @php $selectedField = $fields->firstWhere('id', $fieldId); @endphp
                            @if($selectedField)
                                trong lĩnh vực "<strong>{{ $selectedField->name }}</strong>"
                            @endif
                        @endif
                    </span>
                </div>

                @if($clubs->count())
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        @foreach($clubs as $club)
                            <div class="col">
                                <div class="club-card h-100 p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-light text-teal fw-semibold">
                                            <i class="fas fa-tags me-1"></i>{{ $club->field->name ?? 'Lĩnh vực khác' }}
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i>{{ number_format($club->active_members_count) }} thành viên
                                        </small>
                                    </div>
                                    <h5 class="fw-bold mb-2">{{ $club->name }}</h5>
                                    @php
                                        $clubExcerpt = strip_tags(html_entity_decode($club->description ?? '', ENT_QUOTES, 'UTF-8'));
                                    @endphp
                                    <p class="text-muted flex-grow-1">
                                        {{ Str::words($clubExcerpt, 28, '...') }}
                                    </p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-teal">
                                            Tham gia
                                        </a>
                                        <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-outline-teal">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $clubs->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="text-center py-5 bg-white rounded-4 border border-dashed">
                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                        <h5 class="fw-semibold mb-2">Không tìm thấy câu lạc bộ phù hợp</h5>
                        <p class="text-muted mb-3">Hãy thử đổi từ khóa hoặc chọn lĩnh vực khác.</p>
                        <a href="{{ route('home') }}" class="btn btn-outline-teal">Xóa bộ lọc</a>
                    </div>
                @endif
            </div>

            {{-- Banner Carousel Section (duplicate removed - using the one above) --}}

            <div class="row g-4 align-items-stretch" id="events">
                {{-- Bài viết mới (đổi sang bên trái) --}}
                <div class="col-lg-7" id="posts">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="section-title mb-0">Bài viết mới</h4>
                                <a href="{{ route('student.posts') }}" class="text-teal text-decoration-none fw-semibold">Xem thêm</a>
                            </div>
                            @if($recentPosts->count())
                                <div class="list-group list-group-flush">
                                    @foreach($recentPosts as $post)
                                        @php
                                            // Lấy ảnh của bài viết
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
                                            
                                            $commentCount = $post->comments_count ?? $post->comments->count() ?? 0;
                                        @endphp
                                        <div class="list-group-item border-0 px-0 py-3 border-bottom">
                                            @if($imageUrl)
                                                <div class="mb-3">
                                                    <a href="{{ route('student.posts.show', $post->id) }}">
                                                        <img src="{{ $imageUrl }}" alt="{{ $post->title }}" class="w-100 rounded" style="height: 250px; object-fit: cover;">
                                                    </a>
                                                </div>
                                            @endif
                                            <h6 class="fw-bold mb-1">
                                                <a href="{{ route('student.posts.show', $post->id) }}" class="text-dark text-decoration-none">{{ $post->title }}</a>
                                            </h6>
                                            <div class="text-muted small mb-2">
                                                <i class="fas fa-users me-1 text-teal"></i>{{ $post->club->name ?? 'Cộng đồng UniClubs' }}
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-user-circle me-1 text-teal"></i>{{ $post->user->name ?? 'Ban quản trị' }}
                                            </div>
                                            <p class="text-muted small mb-2">{{ Str::words($postExcerpt, 25, '...') }}</p>
                                            
                                            {{-- Hiển thị 1 bình luận đầu tiên --}}
                                            @if($post->comments->count() > 0)
                                                @php $firstComment = $post->comments->first(); @endphp
                                                <div class="bg-light rounded p-2 mb-2">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <strong class="small me-2">{{ $firstComment->user->name ?? 'Người dùng' }}</strong>
                                                        <small class="text-muted">{{ $firstComment->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="small text-muted mb-0">{{ Str::words($firstComment->content, 30, '...') }}</p>
                                                </div>
                                            @endif
                                            
                                            {{-- Nút thích và bình luận --}}
                                            <div class="d-flex gap-2 mt-2">
                                                <a href="{{ route('student.posts.show', $post->id) }}" class="btn btn-sm btn-outline-teal">
                                                    <i class="far fa-heart me-1"></i>Thích
                                                </a>
                                                <a href="{{ route('student.posts.show', $post->id) }}" class="btn btn-sm btn-outline-teal">
                                                    <i class="far fa-comment me-1"></i>Bình luận
                                                    @if($commentCount > 0)
                                                        <span class="badge bg-teal ms-1">{{ $commentCount }}</span>
                                                    @endif
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Chưa có bài viết công khai.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- Sự kiện sắp diễn ra (đổi sang bên phải) --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="section-title mb-0">Sự kiện</h4>
                                <a href="{{ route('student.events.index') }}" class="text-teal text-decoration-none fw-semibold">Xem tất cả</a>
                            </div>
                            @if($upcomingEvents->count())
                                <div class="list-group list-group-flush">
                                    @foreach($upcomingEvents->take(3) as $event)
                                        <div class="list-group-item border-0 px-0 py-2">
                                            <div class="fw-semibold">{{ $event->title }}</div>
                                            <small class="text-muted">
                                                {{ $event->club->name ?? 'CLB' }} • {{ optional($event->start_time)->format('H:i d/m/Y') }}
                                                @if($event->location) • {{ $event->location }} @endif
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Hiện chưa có sự kiện nào.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bỏ Lĩnh vực hoạt động theo yêu cầu --}}
        </div>
    </section>
@endsection

