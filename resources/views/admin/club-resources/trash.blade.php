@extends('admin.layouts.app')

@section('title', 'Thùng rác - Tài nguyên CLB')

@section('content')
<div class="container-fluid">
    <!-- Title Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Thùng rác - Tài nguyên CLB</h2>
                        <a href="{{ route('admin.club-resources.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trash Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
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
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.club-resources.show', $resource->id) }}" 
                                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form action="{{ route('admin.club-resources.restore', $resource->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" 
                                                                title="Khôi phục" 
                                                                onclick="return confirm('Bạn có chắc chắn muốn khôi phục tài nguyên này?')">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.club-resources.force-delete', $resource->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                title="Xóa vĩnh viễn" 
                                                                onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài nguyên này? Hành động này không thể hoàn tác!')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $resources->links() }}
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
</div>
@endsection
