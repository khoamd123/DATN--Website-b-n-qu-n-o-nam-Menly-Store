@extends('layouts.student')

@section('title', 'Tạo Câu lạc bộ mới - UniClubs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-plus-circle text-teal"></i> Tạo Câu lạc bộ mới
                    </h2>
                    <p class="text-muted mb-0">Điền thông tin dưới đây để gửi yêu cầu thành lập CLB của bạn.</p>
                </div>
                <a href="{{ route('student.clubs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('student.clubs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <!-- Tên CLB -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Tên Câu lạc bộ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mô tả ngắn -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Mô tả ngắn <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                            <small class="form-text text-muted">Mô tả ngắn gọn về CLB của bạn (tối đa 255 ký tự).</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Giới thiệu chi tiết -->
                        <div class="mb-3">
                            <label for="introduction" class="form-label fw-bold">Giới thiệu chi tiết</label>
                            <textarea class="form-control @error('introduction') is-invalid @enderror" id="introduction" name="introduction" rows="6">{{ old('introduction') }}</textarea>
                            <small class="form-text text-muted">Bài viết chi tiết giới thiệu về mục đích, hoạt động, cách thức tham gia...</small>
                            @error('introduction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Lĩnh vực -->
                        <div class="mb-3">
                            <label for="field_id" class="form-label fw-bold">Lĩnh vực <span class="text-danger">*</span></label>
                            <select class="form-select @error('field_id') is-invalid @enderror" id="field_id" name="field_id" required>
                                <option value="" disabled selected>-- Chọn lĩnh vực --</option>
                                @foreach($fields as $field)
                                    <option value="{{ $field->id }}" {{ old('field_id') == $field->id ? 'selected' : '' }}>{{ $field->name }}</option>
                                @endforeach
                            </select>
                            @error('field_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Logo CLB -->
                        <div class="mb-3">
                            <label for="logo" class="form-label fw-bold">Logo CLB</label>
                            <input class="form-control @error('logo') is-invalid @enderror" type="file" id="logo" name="logo" accept="image/*">
                            <small class="form-text text-muted">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</small>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Sau khi tạo, yêu cầu của bạn sẽ được gửi đến quản trị viên để xét duyệt. Bạn sẽ tự động trở thành <strong>Trưởng CLB</strong> sau khi yêu cầu được chấp thuận.
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Gửi yêu cầu tạo CLB</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection