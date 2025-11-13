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
        <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
            @foreach($featuredClubs as $club)
                @php $joined = in_array($club->id, $joinedClubIds); @endphp
                <div class="col">
                    <div class="card h-100 shadow-sm">
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

    <div class="row g-3 align-items-stretch" id="events">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Sự kiện sắp diễn ra</h5>
                        <a href="{{ route('student.events.index') }}" class="text-decoration-none">Xem tất cả</a>
                    </div>
                    @if($upcomingEvents->count())
                        <div class="list-group list-group-flush">
                            @foreach($upcomingEvents as $event)
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex">
                                        <div class="me-3 text-center" style="min-width: 64px;">
                                            <div class="badge bg-light text-dark fw-semibold d-block mb-1">
                                                {{ optional($event->start_time)->translatedFormat('d') }}
                                            </div>
                                            <small class="text-muted text-uppercase">
                                                {{ optional($event->start_time)->translatedFormat('M') }}
                                            </small>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $event->title }}</h6>
                                            <div class="text-muted small mb-1">
                                                <i class="fas fa-users me-1 text-teal"></i>{{ $event->club->name ?? 'CLB' }}
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-clock me-1 text-teal"></i>{{ optional($event->start_time)->format('H:i d/m/Y') }}
                                            </div>
                                            @if($event->location)
                                                <div class="text-muted small">
                                                    <i class="fas fa-location-dot me-1 text-teal"></i>{{ $event->location }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Hiện chưa có sự kiện nào sắp diễn ra.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5" id="posts">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Bài viết mới</h5>
                        <a href="{{ route('student.posts') }}" class="text-decoration-none">Xem thêm</a>
                    </div>
                    @if($recentPosts->count())
                        <div class="list-group list-group-flush">
                            @foreach($recentPosts as $post)
                                <div class="list-group-item border-0 px-0 py-3">
                                    <h6 class="fw-bold mb-1">{{ $post->title }}</h6>
                                    <div class="text-muted small mb-1">
                                        <i class="fas fa-users me-1 text-teal"></i>{{ $post->club->name ?? 'Cộng đồng UniClubs' }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-user-circle me-1 text-teal"></i>{{ $post->user->name ?? 'Ban quản trị' }}
                                    </div>
                                    @php
                                        $raw = html_entity_decode($post->content ?? '', ENT_QUOTES, 'UTF-8');
                                        $text = strip_tags($raw);
                                        $text = str_replace("\xc2\xa0", ' ', $text); // &nbsp;
                                        $text = preg_replace('/\s+/u', ' ', $text);
                                        $text = preg_replace('/\b[\w\-]+\.(?:jpg|jpeg|png|gif|webp)\b/i', '', $text);
                                        $postExcerpt = trim($text);
                                    @endphp
                                    <p class="text-muted small mb-0">{{ Str::words($postExcerpt, 25, '...') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Chưa có bài viết công khai.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bỏ Lĩnh vực hoạt động --}}
@endsection

