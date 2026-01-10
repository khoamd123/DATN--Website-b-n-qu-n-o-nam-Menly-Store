@extends('layouts.student')

@section('title', $post->title)

@push('styles')
<style>
    /* Post Content Styling */
    .content-card article,
    .content-card .post-content {
        max-width: 100%;
        overflow-x: hidden;
    }
    
    /* Responsive Images in Post Content */
    .content-card img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 1.5rem auto;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Ensure images don't overflow */
    .content-card p img,
    .content-card div img {
        max-width: 100% !important;
        height: auto !important;
        width: auto !important;
    }
    
    /* Post Content Typography */
    .content-card .post-content {
        font-size: 1rem;
        line-height: 1.8;
        color: #333;
    }
    
    .content-card .post-content p {
        margin-bottom: 1rem;
    }
    
    .content-card .post-content h1,
    .content-card .post-content h2,
    .content-card .post-content h3,
    .content-card .post-content h4,
    .content-card .post-content h5,
    .content-card .post-content h6 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        font-weight: 600;
        color: #0d9488;
    }
    
    .content-card .post-content ul,
    .content-card .post-content ol {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }
    
    .content-card .post-content li {
        margin-bottom: 0.5rem;
    }
    
    .content-card .post-content blockquote {
        border-left: 4px solid #14b8a6;
        padding-left: 1rem;
        margin: 1.5rem 0;
        font-style: italic;
        color: #666;
    }
    
    .content-card .post-content a {
        color: #14b8a6;
        text-decoration: none;
    }
    
    .content-card .post-content a:hover {
        text-decoration: underline;
    }
    
    /* Post Title */
    .content-card h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #0d9488;
        line-height: 1.3;
        margin-bottom: 1rem;
    }
    
    /* Metadata Styling */
    .content-card .text-muted {
        font-size: 0.9rem;
    }
    
    /* Back Link */
    .content-card a.text-decoration-none {
        color: #14b8a6;
        transition: color 0.2s ease;
    }
    
    .content-card a.text-decoration-none:hover {
        color: #0d9488;
    }
    
    /* Comments Section */
    .content-card .user-avatar {
        flex-shrink: 0;
    }
    
    /* Related Posts */
    .content-card .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .content-card .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.2) !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .content-card {
            padding: 1.5rem;
        }
        
        .content-card h2 {
            font-size: 1.5rem;
        }
        
        .content-card .post-content {
            font-size: 0.95rem;
        }
        
        .content-card img {
            margin: 1rem auto;
        }
    }
    
    /* Fix for any inline styles that might break layout */
    .content-card * {
        max-width: 100%;
    }
    
    /* Table responsive if any */
    .content-card table {
        width: 100%;
        overflow-x: auto;
        display: block;
    }
    
    .content-card table thead,
    .content-card table tbody {
        display: table;
        width: 100%;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <article class="content-card">
                <div class="mb-3">
                    @if($post->type === 'announcement')
                        <a href="{{ route('student.notifications.index') }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    @else
                        <a href="{{ route('student.posts') }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    @endif
                </div>
                <div class="mb-3">
                    <h2 class="mb-2">{{ $post->title }}</h2>
                    @if($post->type === 'announcement')
                        <span class="badge bg-warning text-dark mb-2"><i class="fas fa-bullhorn me-1"></i>Thông báo</span>
                    @endif
                </div>
                <div class="d-flex align-items-center text-muted mb-3">
                    <small>
                        <i class="far fa-user me-1"></i>{{ $post->user->name ?? 'Hệ thống' }}
                        <span class="mx-2">•</span>
                        <i class="far fa-clock me-1"></i>{{ $post->created_at->format('d/m/Y H:i') }}
                        <span class="mx-2">•</span>
                        <i class="far fa-eye me-1"></i>{{ number_format($post->views ?? 0) }} lượt xem
                        <span class="mx-2">•</span>
                        <i class="fas fa-users me-1"></i>{{ $post->club->name ?? 'UniClubs' }}
                        @if($post->status === 'members_only')
                            <span class="mx-2">•</span>
                            <i class="fas fa-lock me-1"></i> Chỉ thành viên
                        @endif
                    </small>
                </div>

                {{-- Không hiển thị ảnh đại diện trên trang chi tiết theo yêu cầu --}}

                @php
                    // If a featured image is selected, avoid showing the same image again inside content
                    $contentForDisplay = $post->content;
                    if (!empty($post->image)) {
                        $relativeImage = ltrim($post->image, '/');
                        $assetImage = asset($relativeImage);
                        // Remove any <img> tags whose src matches featured image (relative or with asset URL)
                        $pattern = '#<img[^>]+src=["\\\'](?:' . preg_quote($assetImage, '#') . '|' . preg_quote('/' . $relativeImage, '#') . '|' . preg_quote($relativeImage, '#') . ')[^"\\\']*["\\\'][^>]*>#i';
                        $contentForDisplay = preg_replace($pattern, '', $contentForDisplay);
                        }
                    @endphp
                <div class="mt-3 post-content">
                    {!! $contentForDisplay !!}
                </div>

                @if($post->type !== 'announcement')
                <hr class="my-4">

                <div class="mt-4" id="comments">
                    <h5 class="mb-3"><i class="far fa-comments me-2"></i>Bình luận</h5>
                    
                    <form method="POST" action="{{ route('student.posts.comment', $post->id) }}" class="mb-4">
                        @csrf
                        <div class="mb-2">
                            <textarea name="content" class="form-control" rows="3" placeholder="Viết bình luận của bạn..." required>{{ old('content') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i> Gửi bình luận
                        </button>
                    </form>

                    @forelse($post->comments->sortByDesc('created_at') as $comment)
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <div class="user-avatar" style="width:36px;height:36px;font-size:14px;">
                                    {{ isset($comment->user->name) ? substr($comment->user->name, 0, 1) : 'U' }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <strong class="me-2">{{ $comment->user->name ?? 'Người dùng' }}</strong>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="text-muted">{!! nl2br(e($comment->content)) !!}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</div>
                    @endforelse
                </div>

                @if(isset($relatedPosts) && $relatedPosts->count() > 0)
                    <hr class="my-4">
                    <div class="mt-4">
                        <h5 class="mb-3"><i class="fas fa-link me-2"></i>Bài viết liên quan</h5>
                        <div class="row g-3">
                            @foreach($relatedPosts as $rel)
                                @php
                                    $relImage = null;
                                    if (!empty($rel->image)) {
                                        if (\Illuminate\Support\Str::startsWith($rel->image, ['uploads/', '/uploads/'])) {
                                            $relImage = asset(ltrim($rel->image, '/'));
                                        } else {
                                            $relImage = asset('storage/' . ltrim($rel->image, '/'));
                                        }
                                    }
                                @endphp
                                <div class="col-sm-6 col-lg-4">
                                    <a href="{{ route('student.posts.show', $rel->id) }}" class="text-decoration-none text-dark">
                                        <div class="card h-100 border-0 shadow-sm">
                                            @if($relImage)
                                                <div style="position: relative; width: 100%; aspect-ratio: 16/9; background: #f8fafc;">
                                                    <img src="{{ $relImage }}" alt="{{ $rel->title }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-top-left-radius:.5rem;border-top-right-radius:.5rem;">
                                                </div>
                                            @endif
                                            <div class="card-body">
                                                <h6 class="card-title mb-2">{{ \Illuminate\Support\Str::limit($rel->title, 80) }}</h6>
                                                <small class="text-muted">
                                                    <i class="far fa-clock me-1"></i>{{ $rel->created_at->format('d/m/Y') }}
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </article>
        </div>
    </div>
@endsection


