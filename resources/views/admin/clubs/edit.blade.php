@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa câu lạc bộ')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-edit"></i> Chỉnh sửa câu lạc bộ</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.clubs') }}">Quản lý CLB</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa CLB</li>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <strong>Có lỗi xảy ra:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                    <form method="POST" action="{{ route('admin.clubs.update', $club->id) }}">
                        @csrf
                        @method('PUT')
                        
                        {{-- Hidden field to preserve owner_id --}}
                        <input type="hidden" name="owner_id" value="{{ $club->owner_id }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Tên câu lạc bộ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $club->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="4" required>{{ old('description', $club->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lĩnh vực <span class="text-danger">*</span></label>
                                    <select class="form-select" name="field_id" required>
                                        <option value="">Chọn lĩnh vực</option>
                                        @foreach($fields as $field)
                                            <option value="{{ $field->id }}" {{ old('field_id', $club->field_id) == $field->id ? 'selected' : '' }}>
                                                {{ $field->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trưởng câu lạc bộ</label>
                                    <select class="form-select" name="leader_id">
                                        <option value="">Chưa chọn</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('leader_id', $club->leader_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                @php
                                    $statusLabels = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'active' => 'Hoạt động',
                                        'inactive' => 'Tạm dừng',
                                        'rejected' => 'Từ chối'
                                    ];
                                @endphp
                                @foreach($statusLabels as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $club->status) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật
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
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin hiện tại</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tên CLB:</strong> {{ $club->name }}</p>
                    <p><strong>Lĩnh vực:</strong> {{ $club->field->name ?? 'N/A' }}</p>
                    <p><strong>Trưởng CLB:</strong> {{ $club->leader->name ?? 'Chưa có' }}</p>
                    <p><strong>Trạng thái:</strong> 
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'approved' => 'info',
                                'active' => 'success',
                                'inactive' => 'secondary',
                                'rejected' => 'danger'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$club->status] ?? 'secondary' }}">
                            {{ $statusLabels[$club->status] ?? ucfirst($club->status) }}
                        </span>
                    </p>
                    <p><strong>Ngày tạo:</strong> {{ $club->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Thành viên</h5>
                </div>
                <div class="card-body">
                    <p class="text-center">
                        <strong>{{ $club->clubMembers->count() }}</strong><br>
                        <small class="text-muted">thành viên</small>
                    </p>
                    <a href="{{ route('admin.clubs.members', $club->id) }}" class="btn btn-info btn-sm w-100">
                        <i class="fas fa-users"></i> Xem thành viên
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection