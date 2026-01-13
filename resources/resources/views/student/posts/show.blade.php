@extends('layouts.student')

@section('title', $post->title)

@section('content')
    <div class="row">
        <div class="col-12">
            <article class="content-card">
                <div class="mb-3">
                    <a href="{{ route('student.posts') }}" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
                <h2 class="mb-2">{{ $post->title }}</h2>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <small class="text-muted">
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
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" 
                                class="btn btn-sm {{ isset($isLiked) && $isLiked ? 'btn-danger' : 'btn-outline-danger' }} like-btn" 
                                data-post-id="{{ $post->id }}"
                                style="transition: all 0.3s ease;">
                            <i class="fas fa-heart me-1"></i>
                            <span class="likes-count">{{ isset($likesCount) ? number_format($likesCount) : '0' }}</span>
                        </button>
                    </div>
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
                <div class="mt-3" style="line-height: 1.7;">
                    {!! $contentForDisplay !!}
                </div>

                @if($post->type !== 'announcement')
                <hr class="my-4">

                <div class="mt-4">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeBtn = document.querySelector('.like-btn');
    if (!likeBtn) return;

    likeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const postId = this.getAttribute('data-post-id');
        const btn = this;
        const icon = btn.querySelector('i');
        const countSpan = btn.querySelector('.likes-count');
        
        // Disable button during request
        btn.disabled = true;
        btn.style.opacity = '0.6';

        fetch(`/student/posts/${postId}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(async response => {
            // Kiểm tra xem response có phải JSON không
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error('Server trả về lỗi. Vui lòng kiểm tra console để xem chi tiết.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update button style
                if (data.liked) {
                    btn.classList.remove('btn-outline-danger');
                    btn.classList.add('btn-danger');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-danger');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                
                // Update count
                countSpan.textContent = parseInt(data.likesCount).toLocaleString('vi-VN');
                
                // Add animation
                btn.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    btn.style.transform = 'scale(1)';
                }, 200);
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi thực hiện thao tác. Vui lòng đảm bảo bảng post_likes đã được tạo trong database.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.style.opacity = '1';
        });
    });
});
</script>
@endpush


