@extends('admin.layouts.app')

@section('title', 'Chi tiết tài nguyên CLB')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết tài nguyên CLB</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <a href="{{ route('admin.club-resources.edit', $resource->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Resource Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin tài nguyên</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="150"><strong>ID:</strong></td>
                                            <td>{{ $resource->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tiêu đề:</strong></td>
                                            <td>{{ $resource->title }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Slug:</strong></td>
                                            <td><code>{{ $resource->slug }}</code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mô tả:</strong></td>
                                            <td>{{ $resource->description ?: 'Không có mô tả' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Loại:</strong></td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($resource->resource_type) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>CLB:</strong></td>
                                            <td>{{ $resource->club->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Người tạo:</strong></td>
                                            <td>{{ $resource->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                @if($resource->status == 'active')
                                                    <span class="badge badge-success">Hoạt động</span>
                                                @elseif($resource->status == 'inactive')
                                                    <span class="badge badge-warning">Không hoạt động</span>
                                                @else
                                                    <span class="badge badge-secondary">Lưu trữ</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lượt xem:</strong></td>
                                            <td>{{ number_format($resource->view_count ?? 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lượt tải:</strong></td>
                                            <td>{{ number_format($resource->download_count ?? 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày tạo:</strong></td>
                                            <td>{{ $resource->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Cập nhật lần cuối:</strong></td>
                                            <td>{{ $resource->updated_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- File Information -->
                            @if($resource->file_path)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Thông tin file</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="150"><strong>Tên file:</strong></td>
                                                <td>{{ $resource->file_name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Loại file:</strong></td>
                                                <td>{{ $resource->file_type }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Kích thước:</strong></td>
                                                <td>{{ number_format($resource->file_size / 1024, 2) }} KB</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Đường dẫn:</strong></td>
                                                <td><code>{{ $resource->file_path }}</code></td>
                                            </tr>
                                        </table>
                                        
                                        <div class="mt-3">
                                            <a href="{{ route('admin.club-resources.download', $resource->id) }}" 
                                               class="btn btn-success">
                                                <i class="fas fa-download"></i> Tải xuống file
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- External Link -->
                            @if($resource->external_link)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Link ngoài</h5>
                                    </div>
                                    <div class="card-body">
                                        <a href="{{ $resource->external_link }}" target="_blank" class="btn btn-info">
                                            <i class="fas fa-external-link-alt"></i> Mở link
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Tags -->
                            @if($resource->tags && count($resource->tags) > 0)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Tags</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($resource->tags as $tag)
                                            <span class="badge badge-primary me-1">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <!-- File Preview -->
                            @if($resource->thumbnail_path)
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Xem trước</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="{{ asset('storage/' . $resource->thumbnail_path) }}" 
                                             class="img-fluid" alt="Preview">
                                    </div>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Thao tác</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.club-resources.edit', $resource->id) }}" 
                                           class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Chỉnh sửa
                                        </a>
                                        
                                        @if($resource->file_path)
                                            <a href="{{ route('admin.club-resources.download', $resource->id) }}" 
                                               class="btn btn-success">
                                                <i class="fas fa-download"></i> Tải xuống
                                            </a>
                                        @endif

                                        <!-- Status Update -->
                                        <div class="dropdown">
                                            <button class="btn btn-info dropdown-toggle w-100" type="button" 
                                                    data-toggle="dropdown">
                                                <i class="fas fa-cog"></i> Cập nhật trạng thái
                                            </button>
                                            <div class="dropdown-menu">
                                                <form action="{{ route('admin.club-resources.update-status', $resource->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-check text-success"></i> Hoạt động
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.club-resources.update-status', $resource->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="inactive">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-pause text-warning"></i> Không hoạt động
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.club-resources.update-status', $resource->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="archived">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-archive text-secondary"></i> Lưu trữ
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Delete -->
                                        <form action="{{ route('admin.club-resources.destroy', $resource->id) }}" 
                                              method="POST" class="d-inline w-100"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="fas fa-trash"></i> Xóa tài nguyên
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
