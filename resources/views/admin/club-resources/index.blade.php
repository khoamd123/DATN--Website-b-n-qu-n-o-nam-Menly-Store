@extends('admin.layouts.app')

@section('title', 'Quản lý tài nguyên CLB')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quản lý tài nguyên CLB</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.club-resources.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm tài nguyên
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tìm kiếm</label>
                                    <input type="text" name="search" class="form-control" 
                                           value="{{ request('search') }}" placeholder="Tìm kiếm...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>CLB</label>
                                    <select name="club_id" class="form-control">
                                        <option value="">Tất cả CLB</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" 
                                                    {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <select name="status" class="form-control">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                                        <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Tìm kiếm
                                        </button>
                                        <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Xóa bộ lọc
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Resources Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>CLB</th>
                                    <th>Trạng thái</th>
                                    <th>Lượt xem</th>
                                    <th>Lượt tải</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resources as $resource)
                                    <tr>
                                        <td>{{ $resource->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($resource->thumbnail_path)
                                                    <img src="{{ asset('storage/' . $resource->thumbnail_path) }}" 
                                                         class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                                @endif
                                                <div>
                                                    <strong>{{ $resource->title }}</strong>
                                                    @if($resource->description)
                                                        <br><small class="text-muted">{{ Str::limit($resource->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $resource->club->name }}</td>
                                        <td>
                                            @if($resource->status == 'active')
                                                <span class="badge badge-success">Hoạt động</span>
                                            @elseif($resource->status == 'inactive')
                                                <span class="badge badge-warning">Không hoạt động</span>
                                            @else
                                                <span class="badge badge-secondary">Lưu trữ</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($resource->view_count ?? 0) }}</td>
                                        <td>{{ number_format($resource->download_count ?? 0) }}</td>
                                        <td>{{ $resource->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.club-resources.show', $resource->id) }}" 
                                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.club-resources.edit', $resource->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($resource->file_path)
                                                    <a href="{{ route('admin.club-resources.download', $resource->id) }}" 
                                                       class="btn btn-sm btn-success" title="Tải xuống">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('admin.club-resources.destroy', $resource->id) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Không có tài nguyên nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $resources->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
