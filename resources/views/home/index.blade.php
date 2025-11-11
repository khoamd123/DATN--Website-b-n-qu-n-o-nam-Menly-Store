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
    .hero-section { background: #ffffff; color: #1f2937; padding: 2.5rem 0; }
    .hero-section h1 { font-weight: 800; font-size: 2rem; margin-bottom: .5rem; }
    .hero-section p.lead { color: #6b7280; margin-bottom: 1rem; }
    .btn-minimal { border-radius: 999px; padding: .5rem 1rem; }
    .club-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem; background: #fff; box-shadow: none; }
    .club-card h5 { margin-bottom: .25rem; font-weight: 700; font-size: 1rem; }
    .club-card p { margin-bottom: .75rem; color: #6b7280; font-size: .92rem; }
    .list-group-item { padding-left: 0; padding-right: 0; }
    .section-title { font-weight: 700; font-size: 1.125rem; margin: 0; }
</style>
@endpush
    <section class="hero-section">
        <div class="container position-relative">
            <div class="row align-items-center gy-4">
                <div class="col-lg-12">
                    <h1>Khám phá câu lạc bộ phù hợp với bạn</h1>
                    <p class="lead">Tìm – tham gia – kết nối chỉ trong vài bước.</p>
                    <a href="#club-search" class="btn btn-teal btn-minimal me-2">Khám phá CLB</a>
                    <a href="{{ route('student.posts') }}" class="btn btn-outline-teal btn-minimal">Xem bài viết</a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            {{-- Tìm kiếm CLB đưa lên trước --}}
            <div id="club-search" class="mb-5">
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <form method="GET" action="{{ route('home') }}" class="row gy-3 align-items-end">
                        <div class="col-md-5">
                            <label for="search" class="form-label fw-semibold text-muted">Tìm kiếm CLB</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Tên hoặc mô tả CLB" value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="field" class="form-label fw-semibold text-muted">Lĩnh vực</label>
                            <select name="field" id="field" class="form-select">
                                <option value="">Tất cả lĩnh vực</option>
                                @foreach($fields as $field)
                                    <option value="{{ $field->id }}" @selected($fieldId == $field->id)>{{ $field->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort" class="form-label fw-semibold text-muted">Sắp xếp</label>
                            <select name="sort" id="sort" class="form-select">
                                <option value="popular" @selected($sort === 'popular')>Phổ biến</option>
                                <option value="newest" @selected($sort === 'newest')>Mới nhất</option>
                                <option value="name" @selected($sort === 'name')>Theo tên</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-teal w-100">
                                <i class="fas fa-filter me-2"></i>Lọc kết quả
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($featuredClubs->count())
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
                    <h3 class="h4 fw-bold mb-0">Danh sách câu lạc bộ</h3>
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

            <div class="row g-4 align-items-stretch" id="events">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="section-title mb-0">Sự kiện sắp diễn ra</h4>
                                <a href="{{ route('student.events.index') }}" class="text-teal text-decoration-none fw-semibold">Xem tất cả</a>
                            </div>
                            @if($upcomingEvents->count())
                                <div class="list-group list-group-flush">
                                    @foreach($upcomingEvents as $event)
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
                                <p class="text-muted">Hiện chưa có sự kiện nào sắp diễn ra.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-5" id="posts">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="section-title mb-0">Bài viết mới</h4>
                                <a href="{{ route('student.posts') }}" class="text-teal text-decoration-none fw-semibold">Xem thêm</a>
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
                                <p class="text-muted">Chưa có bài viết công khai.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bỏ Lĩnh vực hoạt động theo yêu cầu --}}
        </div>
    </section>
@endsection

