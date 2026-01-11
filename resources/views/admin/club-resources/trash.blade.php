@extends('admin.layouts.app')

@section('title', 'Thùng rác - Tài nguyên CLB')

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
        function restoreResource(resourceId) {
            if (confirm('Bạn có chắc chắn muốn khôi phục tài nguyên này?')) {
                document.getElementById('restore-form-' + resourceId).submit();
            }
        }

        function forceDeleteResource(resourceId) {
            if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài nguyên này? Hành động này không thể hoàn tác!')) {
                document.getElementById('force-delete-form-' + resourceId).submit();
            }
        }

        function restoreAllResources() {
            if (confirm('Bạn có chắc chắn muốn khôi phục tất cả tài nguyên trong thùng rác?')) {
                document.getElementById('restore-all-form').submit();
            }
        }

        function deleteAllResources() {
            if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tất cả tài nguyên trong thùng rác? Hành động này không thể hoàn tác!')) {
                document.getElementById('delete-all-form').submit();
            }
        }
    </script>

    <div class="container-fluid">
        <!-- Title Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0">Thùng rác Tài nguyên CLB</h2>
                            <div class="d-flex gap-2">

                                <a href="{{ route('admin.club-resources.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.club-resources.trash') }}" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tài nguyên đã xóa..."
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
                        <select name="deleted_days" class="form-select">
                            <option value="">Tất cả thời gian</option>
                            <option value="1" {{ request('deleted_days') == '1' ? 'selected' : '' }}>1 ngày qua</option>
                            <option value="7" {{ request('deleted_days') == '7' ? 'selected' : '' }}>7 ngày qua</option>
                            <option value="30" {{ request('deleted_days') == '30' ? 'selected' : '' }}>30 ngày qua</option>
                            <option value="90" {{ request('deleted_days') == '90' ? 'selected' : '' }}>90 ngày qua</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="col-md-2 text-end">
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('admin.club-resources.trash') }}" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i> Làm mới
                            </a>

                            <a href="#" class="btn btn-success btn-sm" onclick="restoreAllResources(); return false;">
                                <i class="fas fa-undo"></i> Khôi phục tất cả
                            </a>
                            <a href="#" class="btn btn-danger btn-sm" onclick="deleteAllResources(); return false;">
                                <i class="fas fa-trash"></i> Xóa tất cả
                            </a>

                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Trash Table Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Danh sách tài nguyên đã xóa</h3>
                    </div>
                    <div class="card-body">
                        @if($resources->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Tiêu đề</th>
                                            <th>CLB</th>
                                            <th>Người tạo</th>
                                            <th>Ngày xóa</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resources as $resource)
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
                                                <td>{{ $resource->user->name }}</td>
                                                <td>{{ $resource->deleted_at->format('d/m/Y H:i') }}</td>
                                                <td style="min-width: 120px; width: 120px;">
                                                    <div class="d-flex flex-column gap-1">
                                                        <a href="{{ route('admin.club-resources.show', $resource->id) }}"
                                                            class="btn btn-sm btn-primary text-white w-100">
                                                            <i class="fas fa-eye"></i> Xem chi tiết
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-success w-100 text-white" title="Khôi phục"
                                                            onclick="restoreResource({{ $resource->id }})">
                                                            <i class="fas fa-undo"></i> Khôi phục
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger w-100 text-white" title="Xóa vĩnh viễn"
                                                            onclick="forceDeleteResource({{ $resource->id }})">
                                                            <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                                        </button>
                                                    </div>

                                                    <!-- Hidden forms for restore and force delete -->
                                                    <form id="restore-form-{{ $resource->id }}"
                                                        action="{{ route('admin.club-resources.restore', $resource->id) }}"
                                                        method="POST" style="display: none;">
                                                        @csrf
                                                    </form>

                                                    <form id="force-delete-form-{{ $resource->id }}"
                                                        action="{{ route('admin.club-resources.force-delete', $resource->id) }}"
                                                        method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $resources->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-trash fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Thùng rác trống</h4>
                                <p class="text-muted">Không có tài nguyên nào bị xóa.</p>
                                <a href="{{ route('admin.club-resources.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden forms for bulk actions -->
        <form id="restore-all-form" action="{{ route('admin.club-resources.restore-all') }}" method="POST"
            style="display: none;">
            @csrf
        </form>

        <form id="delete-all-form" action="{{ route('admin.club-resources.force-delete-all') }}" method="POST"
            style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection