@extends('admin.layouts.app')

@section('title', 'Chi tiết bình luận')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-comment"></i> Chi tiết bình luận</h1>
        <div class="d-flex gap-2">
            @if($commentable)
                <a href="{{ route($commentableRoute, $commentable->id) }}" class="btn btn-sm btn-primary text-white">
                    <i class="fas fa-eye me-1"></i> Xem {{ $commentableType }}
                </a>
            @endif
            <button type="button" class="btn btn-sm btn-danger text-white" data-bs-toggle="modal" data-bs-target="#deleteCommentModal">
                <i class="fas fa-trash me-1"></i> Xóa
            </button>
            <a href="{{ route('admin.comments') }}" class="btn btn-sm btn-secondary text-white">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <!-- Nội dung bình luận -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comment"></i> Nội dung bình luận</h5>
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
                    <div class="mb-3">
                        <strong>Thời gian bình luận:</strong>
                        <div class="mt-1">
                            <i class="fas fa-calendar text-muted me-1"></i>{{ $comment->created_at->format('d/m/Y H:i:s') }}
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-history"></i> {{ $comment->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>

                    <!-- Nội dung bình luận -->
                    <div class="mb-3">
                        <strong>Nội dung:</strong>
                        <div class="mt-2" style="line-height: 1.8; white-space: pre-wrap;">{{ $comment->content }}</div>
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

    <div class="col-md-4">
        <!-- Thông tin bình luận -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin bình luận</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>ID:</strong>
                        <div class="mt-1"><span class="badge bg-secondary">#{{ $comment->id }}</span></div>
                    </div>
                    <div class="mb-3">
                        <strong>Loại:</strong>
                        <div class="mt-1">
                            <span class="badge bg-{{ $type === 'post' ? 'primary' : 'warning' }}">
                                {{ $commentableType }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Trạng thái:</strong>
                        <div class="mt-1">
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
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Ngày tạo:</strong>
                        <div class="mt-1">
                            <i class="fas fa-calendar text-muted me-1"></i>{{ $comment->created_at->format('d/m/Y H:i:s') }}
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-history"></i> {{ $comment->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Cập nhật:</strong>
                        <div class="mt-1">
                            @if($comment->updated_at->ne($comment->created_at))
                                <i class="fas fa-clock text-muted me-1"></i>{{ $comment->updated_at->format('d/m/Y H:i:s') }}
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-history"></i> {{ $comment->updated_at->diffForHumans() }}
                                </small>
                            @else
                                <span class="text-muted">Chưa cập nhật</span>
                            @endif
                        </div>
                    </div>
                    @if($comment->parent_id)
                    <div class="mb-3">
                        <strong>Bình luận cha:</strong>
                        <div class="mt-1"><span class="badge bg-info">ID: {{ $comment->parent_id }}</span></div>
                    </div>
                    @endif
                    @if($comment->replies && $comment->replies->count() > 0)
                    <div class="mb-3">
                        <strong>Số phản hồi:</strong>
                        <div class="mt-1"><span class="badge bg-primary">{{ $comment->replies->count() }}</span></div>
                    </div>
                    @endif
                    @if($comment->deletion_reason)
                    <div class="mb-3">
                        <strong>Lý do xóa:</strong>
                        <div class="mt-1">
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
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        <!-- Thông tin bài viết/sự kiện -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-link"></i> Liên kết</h5>
            </div>
            <div class="card-body">
                    @if($commentable)
                        <div class="mb-3">
                            <strong>{{ $commentableType }}:</strong>
                            <p class="mb-1 mt-1">
                                <a href="{{ route($commentableRoute, $commentable->id) }}" 
                                   class="text-decoration-none text-primary">
                                    <strong>{{ $commentable->title ?? 'Không có tiêu đề' }}</strong>
                                </a>
                            </p>
                            @if($commentable->club)
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>{{ $commentable->club->name }}
                                </small>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">Không tìm thấy {{ strtolower($commentableType) }}</p>
                    @endif
            </div>
        </div>
    </div>
</div>

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

@endsection

