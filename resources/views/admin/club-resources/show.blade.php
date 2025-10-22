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
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">ID:</th>
                                    <td>{{ $resource->id }}</td>
                                </tr>
                                <tr>
                                    <th>Tiêu đề:</th>
                                    <td><strong>{{ $resource->title }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Mô tả:</th>
                                    <td>{{ $resource->description ?: 'Không có mô tả' }}</td>
                                </tr>
                                <tr>
                                    <th>Câu lạc bộ:</th>
                                    <td>{{ $resource->club->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Người tạo:</th>
                                    <td>{{ $resource->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Loại:</th>
                                    <td>
                                        <span class="badge badge-info">{{ $resource->type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Trạng thái:</th>
                                    <td>
                                        @if($resource->status == 'active')
                                            <span class="badge badge-success">Hoạt động</span>
                                        @else
                                            <span class="badge badge-secondary">Không hoạt động</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>File:</th>
                                    <td>
                                        @if($resource->file_path)
                                            <a href="{{ route('admin.club-resources.download', $resource->id) }}" 
                                               class="btn btn-success btn-sm" target="_blank">
                                                <i class="fas fa-download"></i> {{ $resource->original_filename }}
                                            </a>
                                            <br>
                                            <small class="text-muted">
                                                Kích thước: {{ $resource->file_size ? number_format($resource->file_size / 1024, 2) . ' KB' : 'N/A' }}
                                            </small>
                                        @else
                                            <span class="text-muted">Không có file</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo:</th>
                                    <td>{{ $resource->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Cập nhật lần cuối:</th>
                                    <td>{{ $resource->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            @if($resource->file_path)
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Xem trước file</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        @if($resource->type == 'image')
                                            <img src="{{ Storage::url($resource->file_path) }}" 
                                                 class="img-fluid" 
                                                 alt="{{ $resource->title }}"
                                                 style="max-height: 300px;">
                                        @else
                                            <div class="text-center">
                                                <i class="fas fa-file fa-5x text-muted"></i>
                                                <p class="mt-2">{{ $resource->original_filename }}</p>
                                                <a href="{{ route('admin.club-resources.download', $resource->id) }}" 
                                                   class="btn btn-primary" target="_blank">
                                                    <i class="fas fa-download"></i> Tải xuống
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.club-resources.edit', $resource->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <form action="{{ route('admin.club-resources.destroy', $resource->id) }}" 
                              method="POST" class="d-inline" 
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
