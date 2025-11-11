@extends('layouts.student')

@section('title', 'Bài viết - UniClubs')
@section('page_title', 'Bài viết')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="content-card mb-3">
            <form method="GET" action="{{ route('student.posts') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Tiêu đề, nội dung...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Câu lạc bộ</label>
                    <select class="form-select" name="club_id">
                        <option value="">Tất cả CLB</option>
                        @foreach($clubs as $club)
                            <option value="{{ $club->id }}" @selected(request('club_id') == $club->id)>{{ $club->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Loại</label>
                    <select class="form-select" name="type">
                        <option value="">Tất cả</option>
                        <option value="post" @selected(request('type')=='post')>Bài viết</option>
                        <option value="announcement" @selected(request('type')=='announcement')>Thông báo</option>
                        <option value="document" @selected(request('type')=='document')>Tài liệu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                </div>
            </form>
        </div>

        <div class="content-card">
            @if($posts->count())
            <div class="list-group list-group-flush">
                @foreach($posts as $post)
                <a href="{{ route('student.posts.show', $post->id) }}" class="list-group-item list-group-item-action py-3">
                    <h6 class="mb-1 fw-semibold">{{ $post->title }}</h6>
                    <div class="text-muted small mb-1">
                        <i class="fas fa-users me-1"></i>{{ $post->club->name ?? 'Cộng đồng UniClubs' }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-user-circle me-1"></i>{{ $post->user->name ?? 'Ban quản trị' }}
                        <span class="mx-2">•</span>
                        {{ $post->created_at->format('d/m/Y H:i') }}
                    </div>
                    @php
                        $raw = html_entity_decode($post->content ?? '', ENT_QUOTES, 'UTF-8');
                        $text = strip_tags($raw);
                        $text = str_replace("\xc2\xa0", ' ', $text);
                        $text = preg_replace('/\s+/u', ' ', $text);
                        $text = preg_replace('/\b[\w\-]+\.(?:jpg|jpeg|png|gif|webp)\b/i', '', $text);
                        $excerpt = trim($text);
                    @endphp
                    <p class="mb-0 text-muted">{{ \Illuminate\Support\Str::words($excerpt, 26, '...') }}</p>
                </a>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $posts->links('vendor.pagination.bootstrap-5') }}
            </div>
            @else
                <p class="text-muted mb-0">Chưa có bài viết phù hợp.</p>
            @endif
        </div>
    </div>
</div>
@endsection

