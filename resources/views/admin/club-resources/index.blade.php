@extends('admin.layouts.app')

@section('title', 'Quản lý tài nguyên CLB')

@section('content')
<style>
    .search-filter-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .action-buttons {
        background: #fff;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .btn-sm {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .form-control-sm, .form-select-sm {
        border-radius: 6px;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    
    .form-control-sm:focus, .form-select-sm:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
</style>
<div class="container-fluid">
    <!-- Title Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="mb-0">Quản lý tài nguyên CLB</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Search, Filter and Actions Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card search-filter-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Search and Filter Section -->
                        <div class="col-md-10">
                            <form method="GET" class="d-flex align-items-center gap-3">
                                <div class="flex-grow-1">
                                    <input type="text" name="search" class="form-control form-control-sm" 
                                           value="{{ request('search') }}" placeholder="Tìm kiếm tài nguyên...">
                                </div>
                                <div style="min-width: 140px;">
                                    <select name="club_id" class="form-select form-select-sm">
                                        <option value="">Tất cả CLB</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="min-width: 140px;">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm px-3">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>

                        <!-- Action Buttons Section -->
                        <div class="col-md-2">
                            <div class="d-flex flex-column gap-2 action-buttons">
                                <button class="btn btn-dark btn-sm" onclick="location.reload()" title="Làm mới trang">
                                    <i class="fas fa-sync-alt"></i> Làm mới
                                </button>
                                <a href="{{ route('admin.club-resources.create') }}" class="btn btn-success btn-sm" title="Tạo tài nguyên mới">
                                    <i class="fas fa-plus"></i> Tạo tài nguyên
                                </a>
                                <a href="{{ route('admin.club-resources.trash') }}" class="btn btn-warning btn-sm" title="Xem thùng rác">
                                    <i class="fas fa-trash"></i> Thùng rác
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn chuyển tài nguyên này vào thùng rác?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Chuyển vào thùng rác">
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
