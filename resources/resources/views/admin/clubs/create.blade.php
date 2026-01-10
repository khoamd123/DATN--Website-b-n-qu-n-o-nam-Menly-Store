@extends('admin.layouts.app')

@section('title', 'Tạo câu lạc bộ mới')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-plus-circle"></i> Tạo câu lạc bộ mới</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.clubs') }}">Quản lý CLB</a></li>
                <li class="breadcrumb-item active">Tạo CLB mới</li>
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
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin câu lạc bộ</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.clubs.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Tên câu lạc bộ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="4" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lĩnh vực <span class="text-danger">*</span></label>
                                    <select class="form-select" name="field_id" required>
                                        <option value="">Chọn lĩnh vực</option>
                                        @foreach($fields as $field)
                                            <option value="{{ $field->id }}" {{ old('field_id') == $field->id ? 'selected' : '' }}>
                                                {{ $field->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Chủ sở hữu <span class="text-danger">*</span></label>
                                    <select class="form-select" name="user_id" required>
                                        <option value="">Chọn chủ sở hữu</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo câu lạc bộ</label>
                            <input type="file" class="form-control" name="logo">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo câu lạc bộ
                            </button>
                            <a href="{{ route('admin.clubs') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Hướng dẫn</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb"></i> Mẹo tạo CLB:</h6>
                        <ul class="mb-0">
                            <li>Đặt tên CLB rõ ràng và dễ hiểu</li>
                            <li>Mô tả chi tiết về mục đích và hoạt động</li>
                            <li>Chọn lĩnh vực phù hợp</li>
                            <li>Có thể bổ sung trưởng CLB sau</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Lưu ý:</h6>
                        <ul class="mb-0">
                            <li>CLB sẽ được tạo ở trạng thái "Chờ duyệt"</li>
                            <li>Cần duyệt trước khi hoạt động</li>
                            <li>Kiểm tra kỹ thông tin trước khi lưu</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection