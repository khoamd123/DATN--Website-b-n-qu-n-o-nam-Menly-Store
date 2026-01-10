@extends('admin.layouts.app')

<<<<<<< HEAD
@section('title', 'Tài liệu học tập - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Tài liệu học tập</h1>
=======
@section('title', 'Tài nguyên CLB - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Tài nguyên CLB</h1>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.learning-materials') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="search" 
                       class="form-control" 
<<<<<<< HEAD
                       placeholder="Tìm kiếm tài liệu..."
=======
                       placeholder="Tìm kiếm tài nguyên..."
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="club_id" class="form-select">
                    <option value="">Tất cả câu lạc bộ</option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                            {{ $club->name }}
                        </option>
                    @endforeach
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-2 text-end">
<<<<<<< HEAD
                <a href="{{ route('admin.learning-materials') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i> Làm mới
                </a>
=======
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.learning-materials') }}" class="btn btn-secondary">
                        <i class="fas fa-refresh"></i> Làm mới
                    </a>
                    <a href="{{ route('admin.learning-materials.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Thêm tài nguyên
                    </a>
                </div>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
            </div>
        </form>
    </div>
</div>

<<<<<<< HEAD
<!-- Danh sách tài liệu -->
=======
<!-- Danh sách tài nguyên -->
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
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
                        <th>Tiêu đề</th>
                        <th>Câu lạc bộ</th>
                        <th>Người tạo</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
<<<<<<< HEAD
                    @forelse($documents as $document)
                        <tr>
                            <td>{{ $document->id }}</td>
=======
                    @forelse($documents as $index => $document)
                        <tr>
                            <td>{{ ($documents->currentPage() - 1) * $documents->perPage() + $index + 1 }}</td>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                            <td>
                                <strong>{{ $document->title }}</strong>
                                <br><small class="text-muted">{{ $document->slug }}</small>
                            </td>
                            <td>{{ $document->club->name ?? 'Không xác định' }}</td>
                            <td>{{ $document->user->name ?? 'Không xác định' }}</td>
<<<<<<< HEAD
                            <td>{{ Str::limit($document->content, 50) }}</td>
=======
                            <td>{{ substr($document->content, 0, 50) }}{{ strlen($document->content) > 50 ? '...' : '' }}</td>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
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
                                <span class="badge bg-{{ $statusColors[$document->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$document->status] ?? ucfirst($document->status) }}
                                </span>
                            </td>
                            <td>{{ $document->created_at->format('d/m/Y') }}</td>
<<<<<<< HEAD
                            <td>
                                <div class="btn-group" role="group">
=======
            <td style="min-width: 140px; width: 140px;">
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('admin.learning-materials.edit', $document->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                    @if($document->status === 'published')
                                        <form method="POST" action="{{ route('admin.posts.status', $document->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="hidden">
<<<<<<< HEAD
                                            <button type="submit" class="btn btn-sm btn-warning">
=======
                                            <button type="submit" class="btn btn-sm btn-warning w-100">
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                                <i class="fas fa-eye-slash"></i> Ẩn
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($document->status === 'hidden')
                                        <form method="POST" action="{{ route('admin.posts.status', $document->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="published">
<<<<<<< HEAD
                                            <button type="submit" class="btn btn-sm btn-success">
=======
                                            <button type="submit" class="btn btn-sm btn-success w-100">
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                                <i class="fas fa-eye"></i> Hiện
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('admin.posts.status', $document->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="deleted">
<<<<<<< HEAD
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa tài liệu này?')">
=======
                                        <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
<<<<<<< HEAD
                                Không tìm thấy tài liệu nào
=======
                                Không tìm thấy tài nguyên nào
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($documents->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $documents->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
