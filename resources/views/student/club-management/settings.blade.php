@extends('layouts.student')

@section('title', 'Cài đặt CLB - UniClubs')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Page Header -->
        <div class="content-card mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-cogs text-warning"></i> Cài đặt CLB
                    </h2>
                    <p class="text-muted mb-0">{{ $club->name }}</p>
                </div>
                <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Settings Form -->
        <div class="content-card">
            <form action="{{ route('student.club-management.settings.update', ['club' => $clubId]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="settings-section mb-4">
                            <h4 class="section-title mb-3">
                                <i class="fas fa-info-circle text-primary me-2"></i>Thông tin cơ bản
                            </h4>

                            <div class="mb-3">
                                <label for="name" class="form-label">Tên CLB <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $club->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="5">{{ old('description', html_entity_decode($club->description, ENT_QUOTES, 'UTF-8')) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="field_id" class="form-label">Lĩnh vực</label>
                                <select class="form-select @error('field_id') is-invalid @enderror" 
                                        id="field_id" name="field_id">
                                    <option value="">-- Chọn lĩnh vực --</option>
                                    @foreach($fields as $field)
                                        <option value="{{ $field->id }}" 
                                                {{ old('field_id', $club->field_id) == $field->id ? 'selected' : '' }}>
                                            {{ $field->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('field_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Logo Upload -->
                        <div class="settings-section mb-4">
                            <h4 class="section-title mb-3">
                                <i class="fas fa-image text-info me-2"></i>Logo CLB
                            </h4>

                            <div class="mb-3">
                                @if($club->logo && file_exists(public_path($club->logo)))
                                    <div class="mb-3">
                                        <label class="form-label">Logo hiện tại</label>
                                        <div>
                                            <img src="{{ asset($club->logo) }}" alt="Club Logo" 
                                                 class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                        </div>
                                    </div>
                                @endif

                                <label for="logo" class="form-label">Thay đổi logo</label>
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       id="logo" name="logo" accept="image/*">
                                <small class="form-text text-muted">Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Member Management -->
                        <div class="settings-section mb-4">
                            <h4 class="section-title mb-3">
                                <i class="fas fa-users text-success me-2"></i>Quản lý thành viên
                            </h4>

                            <div class="mb-3">
                                <label for="max_members" class="form-label">Giới hạn số lượng thành viên <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('max_members') is-invalid @enderror" 
                                       id="max_members" name="max_members" 
                                       value="{{ old('max_members', $club->max_members) }}" 
                                       min="1" max="1000" required>
                                <small class="form-text text-muted">Số thành viên hiện tại: <strong>{{ $clubStats['members'] }}</strong></small>
                                @error('max_members')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Statistics -->
                    <div class="col-lg-4">
                        <div class="settings-sidebar">
                            <h5 class="sidebar-title mb-3">
                                <i class="fas fa-chart-pie me-2"></i>Thống kê CLB
                            </h5>

                            <div class="stat-card mb-3">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">{{ $clubStats['members'] }}</div>
                                    <div class="stat-label">Thành viên</div>
                                </div>
                            </div>

                            <div class="stat-card mb-3">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">{{ $clubStats['events'] }}</div>
                                    <div class="stat-label">Sự kiện</div>
                                </div>
                            </div>

                            <div class="stat-card mb-3">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">{{ $clubStats['posts'] }}</div>
                                    <div class="stat-label">Bài viết</div>
                                </div>
                            </div>

                            <div class="info-box mt-4">
                                <h6 class="info-title">
                                    <i class="fas fa-info-circle me-2"></i>Thông tin CLB
                                </h6>
                                <ul class="info-list">
                                    <li>
                                        <strong>Trưởng CLB:</strong><br>
                                        {{ $club->leader ? $club->leader->name : 'Chưa có' }}
                                    </li>
                                    <li>
                                        <strong>Ngày tạo:</strong><br>
                                        {{ $club->created_at->format('d/m/Y') }}
                                    </li>
                                    <li>
                                        <strong>Trạng thái:</strong><br>
                                        @php
                                            $clubStatusLabels = [
                                                'pending' => 'Chờ duyệt',
                                                'approved' => 'Đã duyệt',
                                                'rejected' => 'Từ chối',
                                                'active' => 'Đang hoạt động',
                                                'inactive' => 'Không hoạt động',
                                            ];
                                            $clubStatusLabel = $clubStatusLabels[$club->status] ?? ucfirst($club->status);
                                            $clubStatusColor = $club->status === 'active' ? 'success' : ($club->status === 'pending' ? 'warning' : ($club->status === 'rejected' ? 'danger' : 'secondary'));
                                        @endphp
                                        <span class="badge bg-{{ $clubStatusColor }}">
                                            {{ $clubStatusLabel }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="settings-actions mt-4 pt-4 border-top">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Lưu thay đổi
                    </button>
                    <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .settings-section {
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .section-title {
        color: #333;
        font-weight: 600;
        font-size: 1.1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .settings-sidebar {
        position: sticky;
        top: 20px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .stat-content {
        flex-grow: 1;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }

    .stat-label {
        color: #666;
        font-size: 0.9rem;
    }

    .info-box {
        padding: 1rem;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .info-title {
        color: #333;
        font-weight: 600;
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list li {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-list li:last-child {
        border-bottom: none;
    }

    .info-list strong {
        color: #333;
        display: block;
        margin-bottom: 0.25rem;
    }

    .settings-actions {
        display: flex;
        gap: 1rem;
    }

    .form-label {
        font-weight: 500;
        color: #333;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
// Khởi tạo CKEditor cho mô tả
document.addEventListener('DOMContentLoaded', function() {
    var editor = CKEDITOR.replace('description', {
        height: 300,
        language: 'vi',
        filebrowserImageBrowseUrl: '{{ route("student.posts.upload-image") }}',
        filebrowserImageUploadUrl: '{{ route("student.posts.upload-image") }}?_token={{ csrf_token() }}',
        toolbar: [
            { name: 'document', items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
            { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
            { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
            '/',
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl'] },
            { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
            { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak'] },
            '/',
            { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'tools', items: ['Maximize', 'ShowBlocks'] }
        ]
    });

    // Đảm bảo sync dữ liệu từ CKEditor vào textarea trước khi submit
    var form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            for (var instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }
        });
    }
});
</script>
@endpush
@endsection

