@extends('admin.layouts.app')

@section('title', 'Quản lý Bài viết - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Quản lý Bài viết</h1>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.posts') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm bài viết..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
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
                <select name="type" class="form-select">
                    <option value="">Tất cả loại</option>
                    <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>Bài viết</option>
                    <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                    <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                    <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="col-md-2 text-end">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Làm mới
                    </a>
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tạo bài viết
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách bài viết -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Loại</th>
                        <th>Câu lạc bộ</th>
                        <th>Tác giả</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $index => $post)
                        <tr>
                            <td>{{ ($posts->currentPage() - 1) * $posts->perPage() + $index + 1 }}</td>
                            <td>
                                <strong>{{ $post->title }}</strong>
                                <br><small class="text-muted">{{ $post->slug }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $post->type === 'announcement' ? 'danger' : 'primary' }}">
                                    {{ $post->type === 'announcement' ? 'Thông báo' : 'Bài viết' }}
                                </span>
                            </td>
                            <td>{{ $post->club->name ?? 'Không xác định' }}</td>
                            <td>{{ $post->user->name ?? 'Không xác định' }}</td>
                            <td>{{ substr($post->content, 0, 50) }}{{ strlen($post->content) > 50 ? '...' : '' }}</td>
                            <td>
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
                                <span class="badge bg-{{ $statusColors[$post->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$post->status] ?? ucfirst($post->status) }}
                                </span>
                            </td>
                            <td>{{ $post->created_at->format('d/m/Y') }}</td>
            <td style="min-width: 140px; width: 140px;">
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                                    @if($post->status === 'published')
                                        <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="hidden">
                                            <button type="submit" class="btn btn-sm btn-warning w-100">
                                                <i class="fas fa-eye-slash"></i> Ẩn
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($post->status === 'hidden')
                                        <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="published">
                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                <i class="fas fa-eye"></i> Hiện
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($post->status !== 'deleted')
                                        <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="deleted">
                                            <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <button class="btn btn-sm btn-info w-100" onclick="viewPost({{ $post->id }})">
                                        <i class="fas fa-eye"></i> Xem
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Không tìm thấy bài viết nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($posts->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $posts->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
