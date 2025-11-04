@extends('admin.layouts.app')

@section('title', 'Quản lý Bình luận - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Quản lý Bình luận</h1>
</div>

<!-- Thống kê bình luận -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-comments"></i>
            </div>
            <p class="stats-number">{{ $allComments->count() }}</p>
            <p class="stats-label">Tổng bình luận</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-newspaper"></i>
            </div>
            <p class="stats-number">{{ $allComments->where('commentable_type', 'App\Models\Post')->count() }}</p>
            <p class="stats-label">Bình luận bài viết</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <p class="stats-number">{{ $allComments->where('commentable_type', 'App\Models\Event')->count() }}</p>
            <p class="stats-label">Bình luận sự kiện</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-clock"></i>
            </div>
            <p class="stats-number">{{ $allComments->where('created_at', '>=', now()->subWeek())->count() }}</p>
            <p class="stats-label">Tuần này</p>
        </div>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.comments') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm nội dung bình luận..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Tất cả loại</option>
                    <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>Bài viết</option>
                    <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Sự kiện</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.comments') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách bình luận -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Người bình luận</th>
                        <th>Nội dung</th>
                        <th>Loại</th>
                        <th>Liên kết</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allComments as $index => $comment)
                        @php
                            $commentable = $comment->post ?? $comment->event ?? null;
                            $commentableType = $comment->post ? 'Bài viết' : 'Sự kiện';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($comment->user && $comment->user->avatar && file_exists(public_path($comment->user->avatar)))
                                        <img src="{{ asset($comment->user->avatar) }}" 
                                             alt="{{ $comment->user->name }}" 
                                             class="rounded-circle me-2" 
                                             width="30" 
                                             height="30">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" 
                                             style="width: 30px; height: 30px;">
                                            <i class="fas fa-user text-white" style="font-size: 12px;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $comment->user->name ?? 'Không xác định' }}</strong>
                                        <br><small class="text-muted">{{ $comment->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 300px;">
                                    {{ substr($comment->content, 0, 100) }}{{ strlen($comment->content) > 100 ? '...' : '' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $comment->post ? 'primary' : 'warning' }}">
                                    {{ $commentableType }}
                                </span>
                            </td>
                            <td>
                                @if($commentable)
                                    @if($comment->post)
                                        <a href="{{ route('admin.posts.show', $commentable->id) }}" 
                                           class="text-decoration-none d-block" 
                                           style="cursor: pointer; transition: all 0.2s;">
                                            <strong class="text-primary" style="text-decoration: underline;">{{ $commentable->title ?? 'Không có tiêu đề' }}</strong>
                                            <br><small class="text-muted">{{ $commentable->club->name ?? 'Không xác định CLB' }}</small>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.events.show', $commentable->id) }}" 
                                           class="text-decoration-none d-block" 
                                           style="cursor: pointer; transition: all 0.2s;">
                                            <strong class="text-primary" style="text-decoration: underline;">{{ $commentable->title ?? 'Không có tiêu đề' }}</strong>
                                            <br><small class="text-muted">{{ $commentable->club->name ?? 'Không xác định CLB' }}</small>
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">Không tìm thấy</span>
                                @endif
                            </td>
                            <td>{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                            <td style="min-width: 120px; width: 120px;">
                                <div class="d-flex flex-column gap-1">
                                    <a href="{{ route('admin.comments.show', [$comment->post ? 'post' : 'event', $comment->id]) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteCommentModal{{ $comment->id }}">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>

                                    <!-- Modal xóa bình luận -->
                                    <div class="modal fade" id="deleteCommentModal{{ $comment->id }}" tabindex="-1" aria-labelledby="deleteCommentModalLabel{{ $comment->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title" id="deleteCommentModalLabel{{ $comment->id }}">
                                                        <i class="fas fa-exclamation-triangle"></i> Xóa bình luận
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.comments.delete', [$comment->post ? 'post' : 'event', $comment->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-info-circle"></i> Bạn có chắc chắn muốn xóa bình luận này? Hành động này không thể hoàn tác.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="deletion_reason{{ $comment->id }}" class="form-label">
                                                                <strong>Lý do xóa bình luận <span class="text-danger">*</span></strong>
                                                            </label>
                                                            <textarea class="form-control" 
                                                                      id="deletion_reason{{ $comment->id }}" 
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Không tìm thấy bình luận nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang (nếu cần) -->
        @if($allComments->count() > 20)
            <div class="d-flex justify-content-center mt-4">
                <!-- Pagination sẽ được thêm sau -->
            </div>
        @endif
    </div>
</div>

<style>
    .table tbody td a:hover strong {
        color: #0056b3 !important;
        text-decoration: underline !important;
    }
    .table tbody td a:hover {
        opacity: 0.8;
    }
</style>

@endsection
