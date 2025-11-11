@extends('layouts.student')

@section('title', 'Tin tức')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0"><i class="fas fa-newspaper me-2"></i>Tin tức</h4>
                </div>

                <form method="GET" action="{{ route('student.posts') }}" class="row g-2 mb-3">
                    <div class="col-md-6">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm kiếm bài viết...">
                    </div>
                    <div class="col-md-4">
                        <select name="club_id" class="form-select">
                            <option value="">Tất cả CLB</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ (string)request('club_id') === (string)$club->id ? 'selected' : '' }}>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Lọc
                        </button>
                    </div>
                </form>

                @forelse($posts as $post)
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="row g-0">
                            @php
                                $imageUrl = null;
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
                            @endphp
                            <div class="col-md-4">
                                <div class="w-100" style="height: 180px; overflow: hidden; border-top-left-radius: .5rem; border-bottom-left-radius: .5rem; background:#f0fdfa; display:flex; align-items:center; justify-content:center;">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="{{ $post->title }}">
                                    @else
                                        <i class="far fa-image" style="font-size:42px;color:#0d9488;opacity:.6;"></i>
                                    @endif
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
                                            // Dùng strlen và substr thay vì mb_* để tránh lỗi nếu extension chưa bật
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
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="far fa-newspaper fa-2x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Chưa có bài viết nào.</p>
                    </div>
                @endforelse

                <div class="mt-3">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
