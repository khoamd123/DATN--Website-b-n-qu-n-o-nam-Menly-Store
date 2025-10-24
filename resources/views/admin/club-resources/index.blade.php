@extends('admin.layouts.app')

@section('title', 'Quản lý tài nguyên CLB')

@section('content')
    <style>

        .btn-group .btn {
            margin-right: 3px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>

    <script>
        function deleteResource(resourceId) {
            if (confirm('Bạn có chắc chắn muốn chuyển tài nguyên này vào thùng rác?')) {
                document.getElementById('delete-form-' + resourceId).submit();
            }
        }
    </script>
    <div class="container-fluid">
        <!-- Title Card -->
        <div class="content-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Quản lý Tài nguyên CLB</h1>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.club-resources.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tài nguyên..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
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
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="col-md-2 text-end">
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i> Làm mới
                            </a>
                            <a href="{{ route('admin.club-resources.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Tạo tài nguyên
                            </a>
                            <a href="{{ route('admin.club-resources.trash') }}" class="btn btn-warning">
                                <i class="fas fa-trash"></i> Thùng rác
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resources Table Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Resources Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tiêu đề</th>
                                        <th>CLB</th>
                                        <th>Trạng thái</th>
                                        <th>Lượt xem</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resources as $resource)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($resource->thumbnail_path)
                                                        <img src="{{ asset('storage/' . $resource->thumbnail_path) }}"
                                                            class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                                    @endif
                                                    <div>
                                                        <strong>{{ $resource->title }}</strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $resource->club->name }}</td>
                                            <td>
                                                @if($resource->status == 'active')
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @elseif($resource->status == 'inactive')
                                                    <span class="badge bg-warning">Không hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Lưu trữ</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($resource->view_count ?? 0) }}</td>
                                            <td>{{ $resource->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.club-resources.show', $resource->id) }}"
                                                        class="btn btn-sm btn-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i> Xem
                                                    </a>
                                                    <a href="{{ route('admin.club-resources.edit', $resource->id) }}"
                                                        class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i> Sửa
                                                    </a>
                                                    @if($resource->file_path)
                                                        <a href="{{ route('admin.club-resources.download', $resource->id) }}"
                                                            class="btn btn-sm btn-success" title="Tải xuống">
                                                            <i class="fas fa-download"></i> Tải
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        title="Chuyển vào thùng rác"
                                                        onclick="deleteResource({{ $resource->id }})">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                </div>

                                                <!-- Hidden form for delete -->
                                                <form id="delete-form-{{ $resource->id }}"
                                                    action="{{ route('admin.club-resources.destroy', $resource->id) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
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