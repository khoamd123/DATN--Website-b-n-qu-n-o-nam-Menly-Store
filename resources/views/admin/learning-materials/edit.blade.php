@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa tài liệu học tập')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-edit"></i> Chỉnh sửa tài liệu học tập</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.learning-materials') }}">Tài liệu học tập</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa tài liệu</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin tài liệu</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.learning-materials.update', $document->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề tài liệu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title', $document->title) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="8" required>{{ old('content', $document->content) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                                        <option value="">Chọn câu lạc bộ</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ old('club_id', $document->club_id) == $club->id ? 'selected' : '' }}>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Đường dẫn file</label>
                                    <input type="text" class="form-control" name="file_path" value="{{ old('file_path', $document->file_path) }}" placeholder="Ví dụ: documents/tutorial.pdf">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="published" {{ old('status', $document->status) == 'published' ? 'selected' : '' }}>Công khai</option>
                                <option value="hidden" {{ old('status', $document->status) == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                                <option value="deleted" {{ old('status', $document->status) == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                            <a href="{{ route('admin.learning-materials') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin hiện tại</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tiêu đề:</strong> {{ $document->title }}</p>
                    <p><strong>Câu lạc bộ:</strong> {{ $document->club->name ?? 'N/A' }}</p>
                    <p><strong>Người tạo:</strong> {{ $document->user->name ?? 'N/A' }}</p>
                    <p><strong>Trạng thái:</strong> 
                        @php
                            $statusColors = [
                                'published' => 'success',
                                'hidden' => 'warning',
                                'deleted' => 'danger'
                            ];
                            $statusLabels = [
                                'published' => 'Công khai',
                                'hidden' => 'Ẩn',
                                'deleted' => 'Đã xóa'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$document->status] ?? 'secondary' }}">
                            {{ $statusLabels[$document->status] ?? ucfirst($document->status) }}
                        </span>
                    </p>
                    <p><strong>Ngày tạo:</strong> {{ $document->created_at->format('d/m/Y H:i') }}</p>
                    @if($document->file_path)
                        <p><strong>File đính kèm:</strong> {{ $document->file_path }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

