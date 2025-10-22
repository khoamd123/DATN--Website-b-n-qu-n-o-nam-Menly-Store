@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa sự kiện')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-calendar-edit"></i> Chỉnh sửa sự kiện</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Sự kiện</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa</li>
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
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin sự kiện</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.events.update', $event->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Tên sự kiện <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title', $event->title) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ (old('club_id', $event->club_id) == $club->id) ? 'selected' : '' }}>{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả sự kiện</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Mô tả chi tiết về sự kiện...">{{ old('description', $event->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Ảnh sự kiện</label>
                            @if($event->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="img-thumbnail" style="max-height: 200px; object-fit: cover;">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                    <label class="form-check-label" for="remove_image">Xóa ảnh hiện tại</label>
                                </div>
                            @endif
                            <input type="file" class="form-control" name="image" accept="image/*" id="imageInput">
                            <small class="text-muted">Hỗ trợ: JPG, JPEG, PNG, WEBP. Tối đa 2MB.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($event->start_time)->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($event->end_time)->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Chế độ</label>
                                    <select class="form-select" name="mode">
                                        <option value="offline" {{ old('mode', $event->mode) === 'offline' ? 'selected' : '' }}>Tại chỗ</option>
                                        <option value="online" {{ old('mode', $event->mode) === 'online' ? 'selected' : '' }}>Trực tuyến</option>
                                        <option value="hybrid" {{ old('mode', $event->mode) === 'hybrid' ? 'selected' : '' }}>Kết hợp</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Địa điểm</label>
                                    <input type="text" class="form-control" name="location" value="{{ old('location', $event->location) }}" placeholder="Địa điểm tổ chức sự kiện">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số lượng tối đa</label>
                                    <input type="number" class="form-control" name="max_participants" value="{{ old('max_participants', $event->max_participants) }}" placeholder="Số người tham gia tối đa">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select" name="status">
                                        <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                        <option value="pending" {{ old('status', $event->status) === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                        <option value="approved" {{ old('status', $event->status) === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                        <option value="ongoing" {{ old('status', $event->status) === 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                                        <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                        <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Gợi ý</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="mb-1"><i class="fas fa-lightbulb"></i> Lưu ý khi chỉnh sửa</h6>
                        <ul class="mb-0">
                            <li>Ảnh mới sẽ thay thế ảnh hiện tại nếu tải lên.</li>
                            <li>Chọn "Xóa ảnh hiện tại" để bỏ ảnh bìa.</li>
                            <li>Đảm bảo thời gian kết thúc sau thời gian bắt đầu.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('imageInput');
    if (!input) return;
    input.addEventListener('change', function(e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;
        const previewContainer = document.createElement('div');
        previewContainer.style.marginTop = '8px';
        previewContainer.style.border = '1px solid #e9ecef';
        previewContainer.style.borderRadius = '8px';
        previewContainer.style.overflow = 'hidden';
        previewContainer.style.maxHeight = '260px';
        const img = document.createElement('img');
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        previewContainer.appendChild(img);
        input.parentElement.appendChild(previewContainer);
        const reader = new FileReader();
        reader.onload = function(ev) { img.src = ev.target.result; };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush


