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
                            <i class="fas fa-plus"></i> Thêm tài nguyên mới
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Bộ lọc -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="club_id" class="form-control">
                                    <option value="">Tất cả CLB</option>
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                            {{ $club->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> Lọc
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh"></i> Làm mới
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Bảng dữ liệu -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>CLB</th>
                                    <th>Người tạo</th>
                                    <th>Loại</th>
                                    <th>Kích thước</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($resources as $resource)
                                <tr>
                                    <td>{{ $resource->id }}</td>
                                    <td>
                                        <strong>{{ $resource->title }}</strong>
                                        @if($resource->description)
                                            <br><small class="text-muted">{{ Str::limit($resource->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $resource->club->name ?? 'N/A' }}</td>
                                    <td>{{ $resource->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $resource->type }}</span>
                                    </td>
                                    <td>{{ $resource->file_size ? number_format($resource->file_size / 1024, 2) . ' KB' : 'N/A' }}</td>
                                    <td>
                                        @if($resource->status == 'active')
                                            <span class="badge badge-success">Hoạt động</span>
                                        @else
                                            <span class="badge badge-secondary">Không hoạt động</span>
                                        @endif
                                    </td>
                                    <td>{{ $resource->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.club-resources.show', $resource->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.club-resources.edit', $resource->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($resource->file_path)
                                                <a href="{{ route('admin.club-resources.download', $resource->id) }}" class="btn btn-success btn-sm">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('admin.club-resources.destroy', $resource->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-center">
                        {{ $resources->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
