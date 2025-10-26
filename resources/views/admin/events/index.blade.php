@extends('admin.layouts.app')

@section('title', 'Quản lý sự kiện - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Quản lý sự kiện</h1>
    <p class="text-muted">Tạo mới, xem và quản lý tất cả các sự kiện trong hệ thống</p>
</div>

<!-- Thông báo -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Form tạo sự kiện mới -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-plus-circle"></i> Tạo sự kiện mới
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.events.store') }}" id="createEventForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label">Tên sự kiện <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="club_id" class="form-label">Câu lạc bộ tổ chức <span class="text-danger">*</span></label>
                    <select class="form-select @error('club_id') is-invalid @enderror" id="club_id" name="club_id" required>
                        <option value="">-- Chọn câu lạc bộ --</option>
                        @foreach($clubs as $club)
                            <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('club_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Mô tả sự kiện <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="start_time" class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                           id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="end_time" class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                           id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="mode" class="form-label">Hình thức tổ chức <span class="text-danger">*</span></label>
                    <select class="form-select @error('mode') is-invalid @enderror" id="mode" name="mode" required>
                        <option value="">-- Chọn hình thức --</option>
                        <option value="offline" {{ old('mode') == 'offline' ? 'selected' : '' }}>Trực tiếp</option>
                        <option value="online" {{ old('mode') == 'online' ? 'selected' : '' }}>Trực tuyến</option>
                        <option value="hybrid" {{ old('mode') == 'hybrid' ? 'selected' : '' }}>Kết hợp</option>
                    </select>
                    @error('mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4" id="locationField" style="display: none;">
                    <label for="location" class="form-label">Địa điểm</label>
                    <input type="text" class="form-control @error('location') is-invalid @enderror" 
                           id="location" name="location" value="{{ old('location') }}" placeholder="Nhập địa điểm tổ chức">
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="max_participants" class="form-label">Số lượng tối đa</label>
                    <input type="number" class="form-control @error('max_participants') is-invalid @enderror" 
                           id="max_participants" name="max_participants" value="{{ old('max_participants') }}" 
                           min="1" placeholder="Không giới hạn nếu để trống">
                    @error('max_participants')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Trạng thái ban đầu</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                        <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="slug" class="form-label">URL thân thiện (Slug)</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                           id="slug" name="slug" value="{{ old('slug') }}" placeholder="Sẽ tự động tạo nếu để trống">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="images" class="form-label">Ảnh sự kiện</label>
                    <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                    <small class="text-muted">Có thể chọn nhiều ảnh cùng lúc. Hỗ trợ: JPG, JPEG, PNG, WEBP. Tối đa 2MB mỗi ảnh.</small>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Tạo sự kiện
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i> Làm mới
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleForm()">
                            <i class="fas fa-eye-slash"></i> Ẩn form
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Thống kê sự kiện -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #6c757d;">
                <i class="fas fa-file-alt"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'draft')->count() }}</p>
            <p class="stats-label">Bản nháp</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #ffc107;">
                <i class="fas fa-clock"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'pending')->count() }}</p>
            <p class="stats-label">Chờ duyệt</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #17a2b8;">
                <i class="fas fa-check-circle"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'approved')->count() }}</p>
            <p class="stats-label">Đã duyệt</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #28a745;">
                <i class="fas fa-play"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'ongoing')->count() }}</p>
            <p class="stats-label">Đang diễn ra</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #007bff;">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'completed')->count() }}</p>
            <p class="stats-label">Hoàn thành</p>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card">
            <div class="stats-icon" style="background-color: #dc3545;">
                <i class="fas fa-ban"></i>
            </div>
            <p class="stats-number">{{ $events->where('status', 'cancelled')->count() }}</p>
            <p class="stats-label">Đã hủy</p>
        </div>
    </div>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.events.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm sự kiện..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="club_id" class="form-select">
                    <option value="">Tất cả CLB</option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                            {{ $club->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="mode" class="form-select">
                    <option value="">Tất cả chế độ</option>
                    <option value="offline" {{ request('mode') == 'offline' ? 'selected' : '' }}>Trực tiếp</option>
                    <option value="online" {{ request('mode') == 'online' ? 'selected' : '' }}>Trực tuyến</option>
                    <option value="hybrid" {{ request('mode') == 'hybrid' ? 'selected' : '' }}>Kết hợp</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                    <i class="fas fa-refresh"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách sự kiện -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-calendar-alt"></i> Danh sách sự kiện
            <span class="badge bg-primary ms-2">{{ $events->total() }} sự kiện</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Thông tin sự kiện</th>
                        <th>Câu lạc bộ</th>
                        <th>Thời gian & Địa điểm</th>
                        <th>Chế độ</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Người tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->id }}</td>
                            <td>
                                <div style="width: 78px;">
                                    <div style="width: 76px; height: 56px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #eee; background: #f8f9fa; display:flex; align-items:center; justify-content:center;">
                                        @php
                                            $hasImages = $event->images && $event->images->count() > 0;
                                            $hasOldImage = !empty($event->image);
                                        @endphp
                                        
                                        @if($hasImages)
                                            <img src="{{ $event->images->first()->image_url }}" alt="{{ $event->title }}" style="width: 100%; height: 100%; object-fit: cover; display:block;" title="{{ $event->images->count() }} images" onerror="this.style.border='2px solid red'; this.alt='Image failed to load';"/>
                                        @elseif($hasOldImage)
                                            <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" style="width: 100%; height: 100%; object-fit: cover; display:block;" title="Old image field" onerror="this.style.border='2px solid red'; this.alt='Image failed to load';"/>
                                        @else
                                            <i class="fas fa-image" style="color:#adb5bd; font-size: 18px;" title="No images"></i>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $event->title }}</strong>
                                <br><small class="text-muted">{{ Str::limit($event->description, 60) }}</small>
                                <br><small class="text-info">Slug: {{ $event->slug }}</small>
                                @if($event->status === 'cancelled' && $event->cancellation_reason)
                                    <br>
                                    <div class="alert alert-danger alert-sm mb-0 mt-1 p-2">
                                        <small>
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <strong>Lý do hủy:</strong> {{ $event->cancellation_reason }}
                                        </small>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $event->club->name ?? 'Không xác định' }}</strong>
                                <br><small class="text-muted">ID: {{ $event->club_id }}</small>
                            </td>
                            <td>
                                <strong>Bắt đầu:</strong> {{ \Carbon\Carbon::parse($event->start_time)->format('d/m/Y H:i') }}
                                <br><strong>Kết thúc:</strong> {{ \Carbon\Carbon::parse($event->end_time)->format('d/m/Y H:i') }}
                                @if($event->location)
                                    <br><strong>Địa điểm:</strong> {{ $event->location }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $modeColors = [
                                        'offline' => 'primary',
                                        'online' => 'success',
                                        'hybrid' => 'info'
                                    ];
                                    $modeLabels = [
                                        'offline' => 'Trực tiếp',
                                        'online' => 'Trực tuyến',
                                        'hybrid' => 'Kết hợp'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $modeColors[$event->mode] ?? 'secondary' }}">
                                    <i class="fas fa-{{ $event->mode === 'offline' ? 'map-marker-alt' : ($event->mode === 'online' ? 'video' : 'users') }}"></i>
                                    {{ $modeLabels[$event->mode] ?? ucfirst($event->mode) }}
                                </span>
                            </td>
                            <td>
                                @if($event->max_participants)
                                    <span class="badge bg-info">
                                        <i class="fas fa-users"></i> {{ $event->max_participants }}
                                    </span>
                                @else
                                    <span class="text-muted">Không giới hạn</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'ongoing' => 'success',
                                        'completed' => 'primary',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Bản nháp',
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'ongoing' => 'Đang diễn ra',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $event->creator->name ?? 'Không xác định' }}</strong>
                                <br><small class="text-muted">ID: {{ $event->created_by }}</small>
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($event->created_at)->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group-vertical" role="group">
                                    <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                    @if($event->status === 'pending')
                                        <form method="POST" action="{{ route('admin.events.approve', $event->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Bạn có chắc muốn duyệt sự kiện này?')">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($event->status, ['pending', 'approved', 'ongoing']))
                                        <form method="POST" action="{{ route('admin.events.cancel', $event->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn hủy sự kiện này?')">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <br>Không tìm thấy sự kiện nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($events->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        Hiển thị <strong>{{ $events->firstItem() }}</strong> - <strong>{{ $events->lastItem() }}</strong> 
                        trong tổng <strong>{{ $events->total() }}</strong> kết quả
                    </span>
                </div>
                {{ $events->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tự động hiển thị/ẩn trường location dựa trên mode
    const modeSelect = document.getElementById('mode');
    const locationField = document.getElementById('locationField');
    
    function toggleLocationField() {
        const mode = modeSelect.value;
        if (mode === 'offline' || mode === 'hybrid') {
            locationField.style.display = 'block';
            document.getElementById('location').required = true;
        } else {
            locationField.style.display = 'none';
            document.getElementById('location').required = false;
            document.getElementById('location').value = '';
        }
    }
    
    modeSelect.addEventListener('change', toggleLocationField);
    
    // Kiểm tra giá trị ban đầu
    toggleLocationField();
    
    // Auto-generate slug từ title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.value === '') {
            const slug = this.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
        }
    });
    
    // Validate thời gian
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    function validateTime() {
        const startTime = new Date(startTimeInput.value);
        const endTime = new Date(endTimeInput.value);
        
        if (startTime && endTime && startTime >= endTime) {
            endTimeInput.setCustomValidity('Thời gian kết thúc phải sau thời gian bắt đầu');
        } else {
            endTimeInput.setCustomValidity('');
        }
    }
    
    startTimeInput.addEventListener('change', validateTime);
    endTimeInput.addEventListener('change', validateTime);
});

function resetForm() {
    document.getElementById('createEventForm').reset();
    document.getElementById('locationField').style.display = 'none';
    document.getElementById('location').required = false;
}

function toggleForm() {
    const formCard = document.querySelector('.card:first-of-type');
    const toggleBtn = event.target;
    
    if (formCard.style.display === 'none') {
        formCard.style.display = 'block';
        toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> Ẩn form';
    } else {
        formCard.style.display = 'none';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i> Hiện form';
    }
}
</script>
@endpush
@endsection
