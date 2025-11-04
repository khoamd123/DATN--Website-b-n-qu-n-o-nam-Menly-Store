@extends('admin.layouts.app')

@section('title', 'Chi tiết bài viết')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-newspaper"></i> Chi tiết bài viết</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.posts') }}">Quản lý bài viết</a></li>
                <li class="breadcrumb-item active">Chi tiết bài viết</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Thông tin bài viết chính -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-newspaper me-2"></i>{{ $post->title }}</h4>
                        @php
                            $statusColors = [
                                'published' => 'success',
                                'hidden' => 'warning',
                                'deleted' => 'danger'
                            ];
                            $statusLabels = [
                                'published' => 'Đã xuất bản',
                                'hidden' => 'Ẩn',
                                'deleted' => 'Đã xóa'
                            ];
                        @endphp
                        <span class="badge bg-light text-dark fs-6 px-3 py-2">
                            {{ $statusLabels[$post->status] ?? ucfirst($post->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="text-muted mb-3">Nội dung bài viết</h5>
                        <div class="post-content">
                            {!! nl2br(e($post->content)) !!}
                        </div>
                    </div>

                    @if($post->comments && $post->comments->count() > 0)
                        <div class="mt-4">
                            <h5 class="text-muted mb-3">
                                <i class="fas fa-comments"></i> Bình luận ({{ $post->comments->count() }})
                            </h5>
                            <div class="comments-list">
                                @foreach($post->comments as $comment)
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <div class="me-3">
                                                    @if($comment->user && $comment->user->avatar && file_exists(public_path($comment->user->avatar)))
                                                        <img src="{{ asset($comment->user->avatar) }}" 
                                                             alt="{{ $comment->user->name }}" 
                                                             class="rounded-circle" 
                                                             width="40" 
                                                             height="40">
                                                    @else
                                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <strong>{{ $comment->user->name ?? 'Không xác định' }}</strong>
                                                    <small class="text-muted ms-2">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                                                    <p class="mt-2 mb-0">{{ $comment->content }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Thông tin bổ sung -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $post->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Loại:</strong></td>
                            <td>
                                <span class="badge bg-{{ $post->type === 'announcement' ? 'danger' : 'primary' }}">
                                    {{ $post->type === 'announcement' ? 'Thông báo' : 'Bài viết' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Câu lạc bộ:</strong></td>
                            <td>{{ $post->club->name ?? 'Không xác định' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tác giả:</strong></td>
                            <td>{{ $post->user->name ?? 'Không xác định' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Slug:</strong></td>
                            <td><code>{{ $post->slug }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Ngày tạo:</strong></td>
                            <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Cập nhật:</strong></td>
                            <td>{{ $post->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Hành động</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .post-content {
        line-height: 1.8;
        font-size: 16px;
        color: #333;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>
@endsection

