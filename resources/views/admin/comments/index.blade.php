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
<<<<<<< HEAD
                        <th>ID</th>
=======
                        <th>STT</th>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                        <th>Người bình luận</th>
                        <th>Nội dung</th>
                        <th>Loại</th>
                        <th>Liên kết</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
<<<<<<< HEAD
                    @forelse($allComments as $comment)
=======
                    @forelse($allComments as $index => $comment)
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                        @php
                            $commentable = $comment->post ?? $comment->event ?? null;
                            $commentableType = $comment->post ? 'Bài viết' : 'Sự kiện';
                        @endphp
                        <tr>
<<<<<<< HEAD
                            <td>{{ $comment->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $comment->user->avatar ?? '/images/avatar/avatar.png' }}" 
                                         alt="{{ $comment->user->name }}" 
                                         class="rounded-circle me-2" 
                                         width="30" 
                                         height="30"
                                         onerror="this.src='/images/avatar/avatar.png'">
=======
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
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                    <div>
                                        <strong>{{ $comment->user->name ?? 'Không xác định' }}</strong>
                                        <br><small class="text-muted">{{ $comment->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 300px;">
<<<<<<< HEAD
                                    {{ Str::limit($comment->content, 100) }}
=======
                                    {{ substr($comment->content, 0, 100) }}{{ strlen($comment->content) > 100 ? '...' : '' }}
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $comment->post ? 'primary' : 'warning' }}">
                                    {{ $commentableType }}
                                </span>
                            </td>
                            <td>
                                @if($commentable)
                                    <strong>{{ $commentable->title ?? 'Không có tiêu đề' }}</strong>
                                    <br><small class="text-muted">{{ $commentable->club->name ?? 'Không xác định CLB' }}</small>
                                @else
                                    <span class="text-muted">Không tìm thấy</span>
                                @endif
                            </td>
                            <td>{{ $comment->created_at->format('d/m/Y H:i') }}</td>
<<<<<<< HEAD
                            <td>
                                <div class="btn-group" role="group">
=======
                            <td style="min-width: 120px; width: 120px;">
                                <div class="d-flex flex-column gap-1">
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                    <button class="btn btn-sm btn-info" onclick="viewComment({{ $comment->id }})">
                                        <i class="fas fa-eye"></i> Xem
                                    </button>
                                    <form method="POST" action="{{ route('admin.comments.delete', [$comment->post ? 'post' : 'event', $comment->id]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
<<<<<<< HEAD
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này?')">
=======
                                        <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này?')">
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
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

<script>
function viewComment(id) {
    // Logic xem chi tiết bình luận
    alert('Xem bình luận ID: ' + id);
}
</script>
@endsection
