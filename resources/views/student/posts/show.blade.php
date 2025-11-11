@extends('layouts.student')

@section('title', $post->title)

@section('content')
    <div class="row">
        <div class="col-12">
            <article class="content-card">
                <div class="mb-3">
                    <a href="{{ route('student.posts') }}" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại tin tức
                    </a>
                </div>
                <h2 class="mb-2">{{ $post->title }}</h2>
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

                @if($post->image)
                    @php
                        $imageUrl = null;
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
                    @endphp
                    @if($imageUrl)
                        <div class="mb-3" style="border-radius: 12px; overflow: hidden; background: #f8fafc;">
                            <div style="position: relative; width: 100%; aspect-ratio: 16/9; max-height: 520px;">
                                <img src="{{ $imageUrl }}" alt="{{ $post->title }}" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                    @endif
                @endif

                <div class="mt-3" style="line-height: 1.7;">
                    {!! $post->content !!}
                </div>

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
            </article>
        </div>
    </div>
@endsection


