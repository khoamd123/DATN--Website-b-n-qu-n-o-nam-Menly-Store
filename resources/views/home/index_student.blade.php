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
                <div class="carousel-inner rounded-4 overflow-hidden shadow-lg">
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
            <div class="hero-banner-fallback rounded-4 overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); height: 400px; display: flex; align-items: center; justify-content: center;">
                <div class="text-center text-white p-5">
                    <h2 class="fw-bold mb-3">Chào mừng đến với UniClubs</h2>
                    <p class="lead mb-4">Nơi kết nối sinh viên và câu lạc bộ</p>
                    <a href="{{ route('student.clubs.index') }}" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-rocket me-2"></i>Khám phá ngay
                    </a>
                </div>
            </div>
        @endif
    </div>

    @php
        $joinedClubIds = $user && $user->clubs ? $user->clubs->pluck('id')->toArray() : [];
    @endphp

    @if($featuredClubs->count())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-fire text-danger me-2"></i>Câu lạc bộ nổi bật
            </h5>
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
                                    <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-primary cta-btn">
                                        Tham gia ngay
                                    </a>
                                @else
                                    <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-outline-secondary cta-btn">
                                        Xem CLB của tôi
                                    </a>
                                @endif
                                <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-outline-secondary cta-btn">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Newest Clubs Section --}}
    @if(isset($newestClubs) && $newestClubs->count() > 0)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-sparkles text-primary me-2"></i>CLB mới nhất
            </h5>
            <a href="{{ route('student.clubs.index') }}" class="text-decoration-none text-teal small">
                Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 mb-4">
            @foreach($newestClubs as $club)
                @php $joined = in_array($club->id, $joinedClubIds); @endphp
                <div class="col">
                    <div class="card h-100 shadow-sm club-featured-card">
                        @php
                            $logoUrl = null;
                            $hasLogo = false;
                            if ($club->logo) {
                                $logoPath = $club->logo;
                                if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
                                    $logoUrl = $logoPath;
                                    $hasLogo = true;
                                } else {
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
                                    <i class="fas fa-users me-1"></i>{{ number_format($club->active_members_count) }}
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
                                    <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-primary cta-btn">
                                        Tham gia ngay
                                    </a>
                                @else
                                    <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-outline-secondary cta-btn">
                                        Xem CLB của tôi
                                    </a>
                                @endif
                                <a href="{{ route('student.clubs.index') }}" class="btn btn-sm btn-outline-secondary cta-btn">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent Posts Section --}}
    @if($recentPosts->count())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-newspaper text-teal me-2"></i>Bài viết mới
                    </h4>
                    <a href="{{ route('student.posts') }}" class="text-decoration-none text-teal fw-semibold">
                        Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="row g-4">
                    {{-- Bài viết mới nhất - 2/3 --}}
                    <div class="col-lg-8">
                        @php
                            $latestPost = $recentPosts->first();
                        @endphp
                        @if($latestPost)
                            @php
                                $imageUrl = null;
                                // Lấy ảnh từ trường image
                                if (!empty($latestPost->image)) {
                                    $imageField = $latestPost->image;
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
                                // Fallback: lấy ảnh đầu tiên trong nội dung HTML nếu có
                                if (empty($imageUrl) && !empty($latestPost->content)) {
                                    if (preg_match('/<img[^>]+src=[\\\"\\\']([^\\\"\\\']+)/i', $latestPost->content, $m)) {
                                        $imageField = $m[1] ?? null;
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
                                }
                                
                                $raw = html_entity_decode($latestPost->content ?? '', ENT_QUOTES, 'UTF-8');
                                $text = strip_tags($raw);
                                $text = str_replace("\xc2\xa0", ' ', $text);
                                $text = preg_replace('/\s+/u', ' ', $text);
                                $text = preg_replace('/\b[\w\-]+\.(?:jpg|jpeg|png|gif|webp)\b/i', '', $text);
                                $postExcerpt = trim($text);
                            @endphp
                            <div class="post-item-card-featured rounded overflow-hidden h-100" style="background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,0.1); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: 1px solid rgba(226, 232, 240, 0.8);" onclick="window.location.href='{{ route('student.posts.show', $latestPost->id) }}'" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.1)';">
                                {{-- Image with overlay --}}
                                @if($imageUrl)
                                    <div class="post-image-container-featured position-relative" style="width: 100%; height: 450px; overflow: hidden; background: #f0f0f0;">
                                        <img src="{{ $imageUrl }}" alt="{{ $latestPost->title }}" class="w-100 h-100" style="object-fit: cover; display: block; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);" onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div style=\'width: 100%; height: 450px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;\'><i class=\'fas fa-newspaper text-white fa-4x\'></i></div>';" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                        {{-- Category Badge Overlay --}}
                                        @if($latestPost->club && $latestPost->club->field)
                                            <div class="position-absolute top-0 start-0 m-3">
                                                <span class="badge px-3 py-2" style="background: rgba(20, 184, 166, 0.95); color: white; font-size: 0.75rem; font-weight: 600; border-radius: 20px; backdrop-filter: blur(10px);">
                                                    <i class="fas fa-tag me-1"></i>{{ $latestPost->club->field->name ?? 'UniClubs' }}
                                                </span>
                                            </div>
                                        @endif
                                        {{-- Gradient Overlay --}}
                                        <div class="position-absolute bottom-0 start-0 w-100" style="height: 120px; background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 100%); pointer-events: none;"></div>
                                    </div>
                                @else
                                    <div class="post-icon-featured position-relative" style="width: 100%; height: 450px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
                                        <i class="fas fa-newspaper text-white fa-4x"></i>
                                        @if($latestPost->club && $latestPost->club->field)
                                            <div class="position-absolute top-0 start-0 m-3">
                                                <span class="badge px-3 py-2" style="background: rgba(255, 255, 255, 0.25); color: white; font-size: 0.75rem; font-weight: 600; border-radius: 20px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">
                                                    <i class="fas fa-tag me-1"></i>{{ $latestPost->club->field->name ?? 'UniClubs' }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                
                                {{-- Content --}}
                                <div class="p-5">
                                    {{-- Header --}}
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 me-3">
                                            <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(20, 184, 166, 0.25); transition: all 0.3s ease;">
                                                <i class="fas fa-users text-white" style="font-size: 1.1rem;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold mb-1" style="font-size: 0.9rem; color: #14b8a6;">{{ $latestPost->club->name ?? 'UniClubs' }}</div>
                                            <div class="text-muted d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.8rem;">
                                                <span><i class="fas fa-user-circle me-1"></i>{{ $latestPost->user->name ?? 'Ban quản trị' }}</span>
                                                <span class="text-muted">•</span>
                                                <span><i class="far fa-clock me-1"></i>{{ $latestPost->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Title --}}
                                    <h4 class="fw-bold mb-3 text-dark" style="font-size: 1.75rem; line-height: 1.35; letter-spacing: -0.01em; transition: color 0.3s ease;" onmouseover="this.style.color='#14b8a6'" onmouseout="this.style.color='#1e293b'">{{ $latestPost->title }}</h4>
                                    
                                    {{-- Excerpt --}}
                                    <p class="text-muted mb-4" style="font-size: 1rem; line-height: 1.75; color: #64748b;">{{ Str::words($postExcerpt, 45, '...') }}</p>
                                    
                                    {{-- Engagement Bar --}}
                                    <div class="d-flex align-items-center justify-content-between pt-3 border-top" style="border-color: #e2e8f0;">
                                        <div class="d-flex align-items-center text-muted gap-3 flex-wrap" style="font-size: 0.875rem;">
                                            @if(isset($latestPost->views) && $latestPost->views > 0)
                                                <span><i class="far fa-eye me-1"></i>{{ number_format($latestPost->views) }} lượt xem</span>
                                            @endif
                                            @if(isset($latestPost->likes_count) && $latestPost->likes_count > 0)
                                                <span><i class="fas fa-heart me-1" style="color: #dc3545;"></i>{{ number_format($latestPost->likes_count) }} lượt thích</span>
                                            @endif
                                            @if(isset($latestPost->comments_count) && $latestPost->comments_count > 0)
                                                <span><i class="far fa-comments me-1"></i>{{ $latestPost->comments_count }} bình luận</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('student.posts.show', $latestPost->id) }}#comments" class="btn btn-sm btn-outline-teal text-decoration-none px-3 py-2" style="border-radius: 8px; font-weight: 500; transition: all 0.3s ease; border-width: 2px;" onclick="event.stopPropagation();" onmouseover="this.style.transform='scale(1.05)'; this.style.background='#14b8a6'; this.style.color='white'; this.style.borderColor='#14b8a6';" onmouseout="this.style.transform='scale(1)'; this.style.background='transparent'; this.style.color='#14b8a6'; this.style.borderColor='#14b8a6';">
                                            <i class="far fa-comment me-1"></i>
                                            Bình luận
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                     {{-- Các bài viết khác - 1/3 --}}
                     <div class="col-lg-4">
                         <div class="d-flex flex-column gap-3">
                             @foreach($recentPosts->skip(1)->take(4) as $post)
                                @php
                                    $imageUrl = null;
                                    // Lấy ảnh từ trường image
                                    if (!empty($post->image)) {
                                        $imageField = $post->image;
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
                                    // Fallback: lấy ảnh đầu tiên trong nội dung HTML nếu có
                                    if (empty($imageUrl) && !empty($post->content)) {
                                        if (preg_match('/<img[^>]+src=[\\\"\\\']([^\\\"\\\']+)/i', $post->content, $m)) {
                                            $imageField = $m[1] ?? null;
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
                                    }
                                    
                                    $raw = html_entity_decode($post->content ?? '', ENT_QUOTES, 'UTF-8');
                                    $text = strip_tags($raw);
                                    $text = str_replace("\xc2\xa0", ' ', $text);
                                    $text = preg_replace('/\s+/u', ' ', $text);
                                    $text = preg_replace('/\b[\w\-]+\.(?:jpg|jpeg|png|gif|webp)\b/i', '', $text);
                                    $postExcerpt = trim($text);
                                @endphp
                                <div class="post-item-card-small rounded overflow-hidden" style="background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: 1px solid rgba(226, 232, 240, 0.5);" onclick="window.location.href='{{ route('student.posts.show', $post->id) }}'">
                                    <div class="d-flex">
                                        {{-- Image --}}
                                        <div class="flex-shrink-0" style="width: 130px; height: 130px; overflow: hidden; background: #f0f0f0; border-radius: 8px 0 0 8px;">
                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $post->title }}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);" onerror="this.onerror=null; this.src=''; this.parentElement.innerHTML='<div style=\'width: 130px; height: 130px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center; border-radius: 8px 0 0 8px;\'><i class=\'fas fa-newspaper text-white fa-2x\'></i></div>';">
                                            @else
                                                <div style="width: 130px; height: 130px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center; border-radius: 8px 0 0 8px; transition: all 0.3s ease;">
                                                    <i class="fas fa-newspaper text-white fa-2x"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Content --}}
                                        <div class="flex-grow-1 p-3 d-flex flex-column">
                                            <div class="mb-2">
                                                <div class="fw-bold text-teal small mb-1" style="font-size: 0.75rem; color: #14b8a6;">{{ $post->club->name ?? 'UniClubs' }}</div>
                                                <div class="text-muted d-flex align-items-center" style="font-size: 0.7rem;">
                                                    <i class="far fa-clock me-1"></i>{{ $post->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                            <h6 class="fw-bold mb-2 text-dark" style="font-size: 0.95rem; line-height: 1.4; flex-grow: 1; letter-spacing: -0.01em;">{{ Str::limit($post->title, 65) }}</h6>
                                            <div class="text-muted small mt-auto d-flex align-items-center gap-2 flex-wrap">
                                                @if(isset($post->likes_count) && $post->likes_count > 0)
                                                    <span><i class="fas fa-heart me-1" style="color: #dc3545;"></i>{{ number_format($post->likes_count) }}</span>
                                                @endif
                                                @if(isset($post->comments_count) && $post->comments_count > 0)
                                                    <span><i class="far fa-comments me-1"></i>{{ $post->comments_count }} bình luận</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- Nút Xem thêm --}}
                            @if($recentPosts->count() > 4)
                                <div class="text-center mt-2">
                                    <a href="{{ route('student.posts') }}" class="btn btn-outline-teal btn-sm w-100">
                                        <i class="fas fa-arrow-right me-1"></i> Xem thêm
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Upcoming Events Section --}}
    @if((isset($upcomingEvents) && $upcomingEvents->count() > 0) || (isset($todayEvents) && $todayEvents->count() > 0))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-calendar-alt text-teal me-2"></i>Sự kiện sắp diễn ra
                    </h4>
                    <a href="{{ route('student.events.index') }}" class="text-decoration-none text-teal fw-semibold">
                        Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="event-list">
                    {{-- Today's Events --}}
                    @if(isset($todayEvents) && $todayEvents->count() > 0)
                        @foreach($todayEvents as $event)
                            @php
                                $hasImages = $event->images && $event->images->count() > 0;
                                $hasOldImage = !empty($event->image);
                                $eventImageUrl = null;
                                if ($hasImages) {
                                    $eventImageUrl = $event->images->first()->image_url;
                                } elseif ($hasOldImage) {
                                    $eventImageUrl = asset('storage/' . $event->image);
                                }
                            @endphp
                            <a href="{{ route('student.events.show', $event->id) }}" class="event-item text-decoration-none">
                                <div class="d-flex align-items-start p-3 mb-2 rounded" style="background: #fef2f2; border-left: 4px solid #ef4444; transition: all 0.3s ease;">
                                    @if($eventImageUrl)
                                        <div class="flex-shrink-0 me-3">
                                            <div style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);">
                                                <img src="{{ $eventImageUrl }}" alt="{{ $event->title }}" class="w-100 h-100" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div style=\'width: 80px; height: 80px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); display: flex; align-items: center; justify-content: center;\'><i class=\'fas fa-calendar-day text-white fa-2x\'></i></div>';">
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 me-3">
                                            <div class="event-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calendar-day text-white fa-2x"></i>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">{{ $event->title }}</h6>
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-users me-1"></i>{{ $event->club->name ?? 'CLB' }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock me-1"></i>{{ optional($event->start_time)->format('H:i d/m/Y') }}
                                        </div>
                                        @if($event->location)
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-location-dot me-1"></i>{{ Str::limit($event->location, 40) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                        @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
                            <hr class="my-3">
                        @endif
                    @endif
                    {{-- Upcoming Events --}}
                    @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
                        @foreach($upcomingEvents->take(3) as $event)
                            @php
                                $hasImages = $event->images && $event->images->count() > 0;
                                $hasOldImage = !empty($event->image);
                                $eventImageUrl = null;
                                if ($hasImages) {
                                    $eventImageUrl = $event->images->first()->image_url;
                                } elseif ($hasOldImage) {
                                    $eventImageUrl = asset('storage/' . $event->image);
                                }
                            @endphp
                            <a href="{{ route('student.events.show', $event->id) }}" class="event-item text-decoration-none">
                                <div class="d-flex align-items-start p-3 mb-2 rounded" style="background: #f0fdfa; border-left: 4px solid #14b8a6; transition: all 0.3s ease;">
                                    @if($eventImageUrl)
                                        <div class="flex-shrink-0 me-3">
                                            <div style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%);">
                                                <img src="{{ $eventImageUrl }}" alt="{{ $event->title }}" class="w-100 h-100" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div style=\'width: 80px; height: 80px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); display: flex; align-items: center; justify-content: center;\'><i class=\'fas fa-calendar-alt text-white fa-2x\'></i></div>';">
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 me-3">
                                            <div class="event-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calendar-alt text-white fa-2x"></i>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">{{ $event->title }}</h6>
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-users me-1"></i>{{ $event->club->name ?? 'CLB' }}
                                            <span class="mx-2">•</span>
                                            <i class="far fa-clock me-1"></i>{{ optional($event->start_time)->format('H:i d/m/Y') }}
                                        </div>
                                        @if($event->location)
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-location-dot me-1"></i>{{ Str::limit($event->location, 40) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Bỏ Lĩnh vực hoạt động --}}

{{-- Modal Thông báo công khai --}}
@if(isset($latestAnnouncement) && $latestAnnouncement)
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 0; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #e0e0e0; padding: 1.5rem 2rem; position: relative; background: #fff;">
                <div class="w-100 text-center">
                    <h1 class="modal-title fw-bold m-0" id="announcementModalLabel" style="font-size: 1.5rem; color: #333; text-transform: uppercase; letter-spacing: 1px;">
                        THÔNG BÁO
                    </h1>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 1rem; right: 1rem; font-size: 1.2rem; opacity: 0.7;"></button>
            </div>
            <div class="modal-body" style="padding: 2rem 2.5rem; background: #fff; max-height: 70vh; overflow-y: auto;">
                <h2 class="mb-4" style="font-size: 1.25rem; color: #333; font-weight: 600; line-height: 1.4;">
                    {{ $latestAnnouncement->title }}
                </h2>
                <div class="announcement-content" style="line-height: 1.8; color: #333; font-size: 1rem;">
                    {!! $latestAnnouncement->content !!}
                </div>
                @if($latestAnnouncement->club)
                <div class="mt-3 text-muted small">
                    <i class="fas fa-users me-1"></i> {{ $latestAnnouncement->club->name }}
                </div>
                @endif
                <div class="mt-2 text-muted small">
                    <i class="fas fa-calendar me-1"></i> {{ $latestAnnouncement->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
@if(isset($latestAnnouncement) && $latestAnnouncement)
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
</script>
@endif
@endpush

@endsection

@push('styles')
<style>
    .cta-btn {
        min-height: 42px;
        min-width: 120px;
        padding: 0.55rem 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    * {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    .club-featured-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .club-featured-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.08) !important;
        border-color: rgba(20, 184, 166, 0.3);
    }
    
    .club-image-container {
        border-radius: 20px 20px 0 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .club-image-container img {
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .club-featured-card:hover .club-image-container img {
        transform: scale(1.08);
    }
    
    .club-image-placeholder {
        border-radius: 20px 20px 0 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Event Card Styles */
    .event-card {
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    
    .event-item-link {
        display: block;
    }
    
    .event-item-home {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    
    .event-item-home:hover {
        background: linear-gradient(135deg, #f0fdfa 0%, #ecfdf5 100%);
        border-color: #14b8a6;
        transform: translateX(6px);
        box-shadow: 0 6px 16px rgba(20, 184, 166, 0.2), 0 2px 4px rgba(20, 184, 166, 0.1);
    }
    
    .event-image-thumb {
        width: 150px;
        min-width: 150px;
        height: 150px;
        overflow: hidden;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%);
        flex-shrink: 0;
        border-radius: 12px 0 0 12px;
    }
    
    .event-image-thumb img {
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .event-item-home:hover .event-image-thumb img {
        transform: scale(1.12);
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
        border-radius: 12px 0 0 12px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .event-item-home:hover .event-date-badge-home {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        transform: scale(1.05);
    }
    
    .event-date-badge-home .date-day {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
        transition: all 0.3s ease;
    }
    
    .event-date-badge-home .date-month {
        font-size: 0.875rem;
        text-transform: uppercase;
        margin-top: 0.5rem;
        font-weight: 600;
    }
    
    /* Post Card Styles */
    .post-card {
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    
    .post-item-link {
        display: block;
    }
    
    .post-item-home {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    
    .post-item-home:hover {
        background: linear-gradient(135deg, #f0fdfa 0%, #ecfdf5 100%);
        border-color: #14b8a6;
        transform: translateX(6px);
        box-shadow: 0 6px 16px rgba(20, 184, 166, 0.2), 0 2px 4px rgba(20, 184, 166, 0.1);
    }
    
    .post-image-thumb {
        width: 200px;
        min-width: 200px;
        height: 200px;
        overflow: hidden;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%);
        flex-shrink: 0;
        border-radius: 16px 0 0 16px;
    }
    
    .post-image-thumb img {
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .post-item-home:hover .post-image-thumb img {
        transform: scale(1.12);
    }
    
    .post-icon-placeholder {
        min-width: 200px;
        width: 200px;
        height: 200px;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2f1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 16px 0 0 16px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .post-item-home:hover .post-icon-placeholder {
        background: linear-gradient(135deg, #e0f2f1 0%, #ccfbf1 100%);
        transform: scale(1.05);
    }
    
    .text-teal {
        color: #14b8a6 !important;
        transition: color 0.3s ease;
    }
    
    .btn-outline-teal {
        border-color: #14b8a6;
        color: #14b8a6;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 8px;
    }
    
    .btn-outline-teal:hover {
        background-color: #14b8a6;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.3);
    }
    
    .badge.bg-teal {
        background-color: #14b8a6 !important;
        transition: all 0.3s ease;
    }
    
    /* Announcement Styles */
    .announcement-item {
        transition: all 0.3s ease;
    }
    
    .announcement-item > div {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .announcement-item:hover > div {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%) !important;
        transform: translateX(6px);
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.25), 0 2px 4px rgba(245, 158, 11, 0.1);
        border-left-width: 6px !important;
    }
    
    .announcement-icon {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .announcement-item:hover .announcement-icon {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    /* Post Item Styles (similar to announcement) */
    /* Featured Post Card (2/3) */
    .post-item-card-featured {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    
    .post-item-card-featured:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
        transform: translateY(-6px);
        border-color: rgba(20, 184, 166, 0.3) !important;
    }
    
    .post-item-card-featured .post-image-container-featured {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    
    .post-item-card-featured .post-image-container-featured::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.1) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .post-item-card-featured:hover .post-image-container-featured::after {
        opacity: 1;
    }
    
    .post-item-card-featured:hover .post-image-container-featured img {
        transform: scale(1.08);
    }
    
    .post-item-card-featured img {
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .post-item-card-featured .post-icon-featured {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .post-item-card-featured:hover .post-icon-featured {
        transform: scale(1.05);
        box-shadow: 0 4px 16px rgba(20, 184, 166, 0.3);
    }
    
    /* Small Post Card (1/3) */
    .post-item-card-small {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    
    .post-item-card-small:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
        transform: translateY(-3px);
        border-color: rgba(20, 184, 166, 0.4) !important;
        background: #fafafa !important;
    }
    
    .post-item-card-small img {
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .post-item-card-small:hover img {
        transform: scale(1.12);
    }
    
    .post-item-card-small:hover .text-teal {
        color: #0d9488 !important;
    }
    
    /* Event Item Styles (similar to announcement) */
    .event-item {
        transition: all 0.3s ease;
    }
    
    .event-item > div {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .event-item:hover > div {
        transform: translateX(6px);
        box-shadow: 0 6px 16px rgba(20, 184, 166, 0.25), 0 2px 4px rgba(20, 184, 166, 0.1);
        border-left-width: 6px !important;
    }
    
    .event-item:hover > div[style*="background: #fef2f2"] {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
    }
    
    .event-item:hover > div[style*="background: #f0fdfa"] {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%) !important;
    }
    
    .event-icon {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .event-item:hover .event-icon {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.3);
    }
    
    .event-item img {
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .event-item:hover img {
        transform: scale(1.1);
    }
    
    /* Card general styles */
    .card {
        transition: all 0.3s ease;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .card:hover {
        border-color: rgba(20, 184, 166, 0.3);
    }
    
    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }
</style>
@endpush

