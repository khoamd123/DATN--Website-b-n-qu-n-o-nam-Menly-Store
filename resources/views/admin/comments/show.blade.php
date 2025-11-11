@extends('admin.layouts.app')

@section('title', 'Chi tiết bình luận')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-comment"></i> Chi tiết bình luận</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.comments') }}">Quản lý bình luận</a></li>
                <li class="breadcrumb-item active">Chi tiết bình luận</li>
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
        <!-- Nội dung bình luận chính -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-comment me-2"></i>Nội dung bình luận</h4>
                </div>
                <div class="card-body">
                    <!-- Thông tin người bình luận -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            @if($comment->user && $comment->user->avatar && file_exists(public_path($comment->user->avatar)))
                                <img src="{{ asset($comment->user->avatar) }}" 
                                     alt="{{ $comment->user->name }}" 
                                     class="rounded-circle me-3" 
                                     width="60" 
                                     height="60">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" 
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-user text-white fs-4"></i>
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-1">{{ $comment->user->name ?? 'Không xác định' }}</h5>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-envelope"></i> {{ $comment->user->email ?? 'N/A' }}
                                    @if($comment->user && $comment->user->student_id)
                                        <span class="ms-2">
                                            <i class="fas fa-id-card"></i> {{ $comment->user->student_id }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Thời gian bình luận -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-primary me-2 fs-5"></i>
                            <div>
                                <strong class="text-dark">Thời gian bình luận:</strong>
                                <div class="mt-1">
                                    <span class="text-dark">
                                        Ngày {{ $comment->created_at->format('d') }} tháng {{ $comment->created_at->format('m') }} năm {{ $comment->created_at->format('Y') }}, 
                                        lúc {{ $comment->created_at->format('H') }} giờ {{ $comment->created_at->format('i') }} phút {{ $comment->created_at->format('s') }} giây
                                    </span>
                                    <span class="badge bg-info ms-2">
                                        <i class="fas fa-history"></i> {{ $comment->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nội dung bình luận -->
                    <div class="comment-content mb-4">
                        <div class="border-start border-primary border-4 ps-3">
                            <p class="mb-0" style="font-size: 16px; line-height: 1.8; white-space: pre-wrap;">{{ $comment->content }}</p>
                        </div>
                    </div>

                    <!-- Bình luận cha (nếu có) -->
                    @if($comment->parent)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-reply"></i> Phản hồi cho bình luận:
                            </h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        @if($comment->parent->user && $comment->parent->user->avatar && file_exists(public_path($comment->parent->user->avatar)))
                                            <img src="{{ asset($comment->parent->user->avatar) }}" 
                                                 alt="{{ $comment->parent->user->name }}" 
                                                 class="rounded-circle me-2" 
                                                 width="30" 
                                                 height="30">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 30px; height: 30px;">
                                                <i class="fas fa-user text-white" style="font-size: 12px;"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <strong>{{ $comment->parent->user->name ?? 'Không xác định' }}</strong>
                                                <small class="text-muted text-end">
                                                    <div><i class="fas fa-calendar"></i> {{ $comment->parent->created_at->format('d/m/Y') }}</div>
                                                    <div><i class="fas fa-clock"></i> {{ $comment->parent->created_at->format('H:i:s') }}</div>
                                                </small>
                                            </div>
                                            <p class="mt-1 mb-0">{{ $comment->parent->content }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Các bình luận phản hồi (nếu có) -->
                    @if($comment->replies && $comment->replies->count() > 0)
                        <div class="mt-4">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-comments"></i> Phản hồi ({{ $comment->replies->count() }})
                            </h6>
                            <div class="replies-list">
                                @foreach($comment->replies as $reply)
                                    <div class="card mb-2 ms-4">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                @if($reply->user && $reply->user->avatar && file_exists(public_path($reply->user->avatar)))
                                                    <img src="{{ asset($reply->user->avatar) }}" 
                                                         alt="{{ $reply->user->name }}" 
                                                         class="rounded-circle me-2" 
                                                         width="40" 
                                                         height="40">
                                                @else
                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <strong>{{ $reply->user->name ?? 'Không xác định' }}</strong>
                                                        <small class="text-muted text-end">
                                                            <div><i class="fas fa-calendar"></i> {{ $reply->created_at->format('d/m/Y') }}</div>
                                                            <div><i class="fas fa-clock"></i> {{ $reply->created_at->format('H:i:s') }}</div>
                                                        </small>
                                                    </div>
                                                    <p class="mt-1 mb-0">{{ $reply->content }}</p>
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
            <!-- Thông tin bình luận -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin bình luận</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $comment->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Loại:</strong></td>
                            <td>
                                <span class="badge bg-{{ $type === 'post' ? 'primary' : 'warning' }}">
                                    {{ $commentableType }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Trạng thái:</strong></td>
                            <td>
                                @php
                                    $statusColors = [
                                        'visible' => 'success',
                                        'hidden' => 'warning',
                                        'deleted' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'visible' => 'Hiển thị',
                                        'hidden' => 'Ẩn',
                                        'deleted' => 'Đã xóa'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$comment->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$comment->status] ?? ucfirst($comment->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Ngày tạo:</strong></td>
                            <td>
                                <div class="text-dark">
                                    <i class="fas fa-calendar text-primary"></i> 
                                    Ngày {{ $comment->created_at->format('d') }}/{{ $comment->created_at->format('m') }}/{{ $comment->created_at->format('Y') }}
                                    <br>
                                    <i class="fas fa-clock text-secondary"></i> 
                                    {{ $comment->created_at->format('H') }} giờ {{ $comment->created_at->format('i') }} phút {{ $comment->created_at->format('s') }} giây
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-history"></i> {{ $comment->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Cập nhật:</strong></td>
                            <td>
                                @if($comment->updated_at->ne($comment->created_at))
                                    <div class="text-dark">
                                        <i class="fas fa-calendar text-primary"></i> 
                                        Ngày {{ $comment->updated_at->format('d') }}/{{ $comment->updated_at->format('m') }}/{{ $comment->updated_at->format('Y') }}
                                        <br>
                                        <i class="fas fa-clock text-secondary"></i> 
                                        {{ $comment->updated_at->format('H') }} giờ {{ $comment->updated_at->format('i') }} phút {{ $comment->updated_at->format('s') }} giây
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-history"></i> {{ $comment->updated_at->diffForHumans() }}
                                        </small>
                                    </div>
                                @else
                                    <span class="text-muted">Chưa cập nhật</span>
                                @endif
                            </td>
                        </tr>
                        @if($comment->parent_id)
                            <tr>
                                <td><strong>Bình luận cha:</strong></td>
                                <td>ID: {{ $comment->parent_id }}</td>
                            </tr>
                        @endif
                        @if($comment->replies && $comment->replies->count() > 0)
                            <tr>
                                <td><strong>Số phản hồi:</strong></td>
                                <td>{{ $comment->replies->count() }}</td>
                            </tr>
                        @endif
                        @if($comment->deletion_reason)
                            <tr>
                                <td><strong>Lý do xóa:</strong></td>
                                <td>
                                    <div class="alert alert-danger mb-0">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>{{ $comment->deletion_reason }}</strong>
                                        @if($comment->deleted_at)
                                            <br><small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                Xóa lúc: {{ $comment->deleted_at->format('d/m/Y H:i:s') }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Thông tin bài viết/sự kiện -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Liên kết</h5>
                </div>
                <div class="card-body">
                    @if($commentable)
                        <div class="mb-3">
                            <strong>{{ $commentableType }}:</strong>
                            <p class="mb-1">
                                <a href="{{ route($commentableRoute, $commentable->id) }}" 
                                   class="text-decoration-none">
                                    {{ $commentable->title ?? 'Không có tiêu đề' }}
                                </a>
                            </p>
                            @if($commentable->club)
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> {{ $commentable->club->name }}
                                </small>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">Không tìm thấy {{ strtolower($commentableType) }}</p>
                    @endif
                </div>
            </div>

            <!-- Hành động -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Hành động</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.comments') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        @if($commentable)
                            <a href="{{ route($commentableRoute, $commentable->id) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Xem {{ $commentableType }}
                            </a>
                        @endif
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteCommentModal">
                            <i class="fas fa-trash"></i> Xóa bình luận
                        </button>

                        <!-- Modal xóa bình luận -->
                        <div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-labelledby="deleteCommentModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title" id="deleteCommentModalLabel">
                                            <i class="fas fa-exclamation-triangle"></i> Xóa bình luận
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.comments.delete', [$type, $comment->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-info-circle"></i> Bạn có chắc chắn muốn xóa bình luận này? Hành động này không thể hoàn tác.
                                            </div>
                                            <div class="mb-3">
                                                <label for="deletion_reason" class="form-label">
                                                    <strong>Lý do xóa bình luận <span class="text-danger">*</span></strong>
                                                </label>
                                                <textarea class="form-control" 
                                                          id="deletion_reason" 
                                                          name="deletion_reason" 
                                                          rows="4" 
                                                          placeholder="Vui lòng nhập lý do xóa bình luận (tối thiểu 10 ký tự)" 
                                                          required 
                                                          minlength="10" 
                                                          maxlength="1000"></textarea>
                                                <small class="form-text text-muted">Tối thiểu 10 ký tự, tối đa 1000 ký tự</small>
                                                @error('deletion_reason')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Xác nhận xóa
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .comment-content {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }
    .replies-list .card {
        border-left: 3px solid #007bff;
    }
</style>
@endsection

