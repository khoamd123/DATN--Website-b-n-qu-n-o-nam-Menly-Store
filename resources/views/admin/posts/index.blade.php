@extends('admin.layouts.app')

@section('title', 'Quản lý Bài viết - CLB Admin')

@section('content')
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">📝 Quản lý Bài viết</h1>
        <p class="text-muted mb-0">Quản lý và theo dõi tất cả bài viết trong hệ thống</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.posts.create') }}" class="btn btn-success btn-lg">
            <i class="fas fa-plus-circle"></i> Tạo bài viết mới
        </a>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-filter"></i> Bộ lọc và tìm kiếm</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.posts') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Tìm kiếm bài viết..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Câu lạc bộ</label>
                <select name="club_id" class="form-select">
                    <option value="">Tất cả CLB</option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                            {{ $club->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Loại bài viết</label>
                <select name="type" class="form-select">
                    <option value="">Tất cả loại</option>
                    <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>📄 Bài viết</option>
                    <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>📢 Thông báo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>✅ Đã xuất bản</option>
                    <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>👁️ Ẩn</option>
                    <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>🗑️ Đã xóa</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.posts') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách bài viết -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách bài viết</h6>
            <span class="badge bg-primary">{{ $posts->total() }} bài viết</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">ID</th>
                        <th class="border-0">📝 Tiêu đề</th>
                        <th class="border-0">🏷️ Loại</th>
                        <th class="border-0">🏢 Câu lạc bộ</th>
                        <th class="border-0">👤 Tác giả</th>
                        <th class="border-0">📄 Nội dung</th>
                        <th class="border-0">📊 Trạng thái</th>
                        <th class="border-0">📅 Ngày tạo</th>
                        <th class="border-0">⚙️ Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr class="align-middle">
                            <td>
                                <span class="badge bg-light text-dark">#{{ $post->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($post->featured_image_url)
                                        <img src="{{ $post->featured_image_url }}" 
                                             alt="{{ $post->title }}" 
                                             class="rounded me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong class="text-dark mb-1">{{ Str::limit($post->title, 30) }}</strong>
                                        <br><small class="text-muted">{{ $post->slug }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $post->type === 'announcement' ? 'danger' : 'primary' }} rounded-pill">
                                    {{ $post->type === 'announcement' ? '📢 Thông báo' : '📄 Bài viết' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-building text-muted me-2"></i>
                                    <span>{{ Str::limit($post->club->name ?? 'Không xác định', 20) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user text-muted me-2"></i>
                                    <span>{{ Str::limit($post->user->name ?? 'Không xác định', 15) }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">{{ Str::limit(strip_tags($post->content), 60) }}</span>
                            </td>
                            <td>
                                @php
                                    $statusConfig = [
                                        'published' => ['color' => 'success', 'icon' => '✅', 'label' => 'Đã xuất bản'],
                                        'hidden' => ['color' => 'warning', 'icon' => '👁️', 'label' => 'Ẩn'],
                                        'deleted' => ['color' => 'danger', 'icon' => '🗑️', 'label' => 'Đã xóa']
                                    ];
                                    $config = $statusConfig[$post->status] ?? ['color' => 'secondary', 'icon' => '❓', 'label' => ucfirst($post->status)];
                                @endphp
                                <span class="badge bg-{{ $config['color'] }} rounded-pill">
                                    {{ $config['icon'] }} {{ $config['label'] }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark">{{ $post->created_at->format('d/m/Y') }}</span>
                                    <small class="text-muted">{{ $post->created_at->format('H:i') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group-vertical btn-group-sm" role="group">
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <a href="{{ route('admin.posts.show', $post->id) }}" class="btn btn-outline-info btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-outline-warning btn-sm" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($post->status === 'published')
                                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="hidden">
                                                <button type="submit" class="btn btn-outline-warning btn-sm" title="Ẩn bài viết">
                                                    <i class="fas fa-eye-slash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($post->status === 'hidden')
                                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="published">
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Hiện bài viết">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($post->status !== 'deleted')
                                            <form method="POST" action="{{ route('admin.posts.destroy', $post->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa bài viết" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Không tìm thấy bài viết nào</h5>
                                    <p class="text-muted mb-3">Hãy thử thay đổi bộ lọc hoặc tạo bài viết mới</p>
                                    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Tạo bài viết đầu tiên
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($posts->hasPages())
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Hiển thị {{ $posts->firstItem() ?? 0 }} - {{ $posts->lastItem() ?? 0 }} 
                        trong tổng số {{ $posts->total() }} bài viết
                    </div>
                    <div>
                        {{ $posts->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.empty-state {
    padding: 2rem;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    border-top: 1px solid #f1f3f4;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group-vertical .btn {
    border-radius: 0.375rem !important;
}

.btn-group-vertical .btn:first-child {
    border-top-left-radius: 0.375rem !important;
    border-top-right-radius: 0.375rem !important;
}

.btn-group-vertical .btn:last-child {
    border-bottom-left-radius: 0.375rem !important;
    border-bottom-right-radius: 0.375rem !important;
}

.badge.rounded-pill {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
</style>
@endsection
