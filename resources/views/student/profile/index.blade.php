@extends('layouts.student')

@section('title', 'Hồ sơ - UniClubs')

@section('content')
<<<<<<< HEAD

<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> <?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> Vui lòng kiểm tra lại các trường thông tin.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

=======
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Profile Header -->
        <div class="content-card">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
<<<<<<< HEAD
                    @if($user->avatar && file_exists(public_path($user->avatar)))
                        <img src="{{ asset($user->avatar) }}" alt="Avatar" class="profile-avatar-img mb-3">
                    @else
                        <div class="profile-avatar mb-3">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
=======
                    <div class="profile-avatar mb-3">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-camera me-2"></i> Đổi ảnh
                    </button>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                </div>
                <div class="col-md-9">
                    <h3 class="mb-2">{{ $user->name }}</h3>
                    <p class="text-muted mb-2">
                        <i class="fas fa-id-card me-2"></i> {{ $user->student_id ?? 'Chưa có mã sinh viên' }}
                    </p>
                    <p class="text-muted mb-3">
                        <i class="fas fa-envelope me-2"></i> {{ $user->email }}
                    </p>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-1 text-teal">{{ $user->clubs->count() }}</div>
                                <small class="text-muted">CLB đã tham gia</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-1 text-teal">0</div>
                                <small class="text-muted">Sự kiện đã tham gia</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-1 text-teal">0</div>
                                <small class="text-muted">Giải thưởng</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-user text-teal me-2"></i> Thông tin cá nhân
            </h4>
            
<<<<<<< HEAD
            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
=======
            <form>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="name" value="{{ $user->name }}" readonly>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="student_id" class="form-label">Mã sinh viên</label>
                        <input type="text" class="form-control" id="student_id" value="{{ $user->student_id ?? 'Chưa có' }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
<<<<<<< HEAD
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Chưa cập nhật">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="avatar" class="form-label">Ảnh đại diện</label>
                        <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar">
                        @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Chưa cập nhật">{{ old('address', $user->address) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
=======
                        <input type="tel" class="form-control" id="phone" value="{{ $user->phone ?? 'Chưa cập nhật' }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" rows="3">{{ $user->address ?? 'Chưa cập nhật' }}</textarea>
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Cập nhật thông tin
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- My Clubs -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-users text-teal me-2"></i> Câu lạc bộ của tôi
            </h4>
            
            @if($user->clubs->count() > 0)
                <div class="row">
                    @foreach($user->clubs as $club)
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="club-logo me-3">
                                        {{ substr($club->name, 0, 2) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1">{{ $club->name }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-user-friends me-1"></i> {{ $club->members->count() }} thành viên
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-teal">Thành viên</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-2x text-muted mb-3"></i>
                    <p class="text-muted mb-3">Bạn chưa tham gia câu lạc bộ nào</p>
                    <a href="{{ route('student.clubs.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Khám phá CLB
                    </a>
                </div>
            @endif
        </div>

        <!-- Activity History -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-history text-teal me-2"></i> Lịch sử hoạt động
            </h4>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker bg-teal"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Tham gia UniClubs</h6>
                        <p class="text-muted mb-1">Bạn đã tạo tài khoản và tham gia nền tảng UniClubs</p>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> {{ $user->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
                
                @if($user->clubs->count() > 0)
                @foreach($user->clubs as $club)
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Tham gia {{ $club->name }}</h6>
                        <p class="text-muted mb-1">Bạn đã trở thành thành viên của câu lạc bộ {{ $club->name }}</p>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> {{ $user->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-cog"></i> Cài đặt
            </h5>
            <div class="list-group list-group-flush">
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-edit me-2"></i> Chỉnh sửa hồ sơ
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-lock me-2"></i> Đổi mật khẩu
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-bell me-2"></i> Cài đặt thông báo
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-shield-alt me-2"></i> Bảo mật
                </a>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-chart-line"></i> Thống kê
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Sự kiện đã tham gia</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Giải thưởng</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Điểm hoạt động</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #14b8a6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: bold;
        margin: 0 auto;
    }
<<<<<<< HEAD

    .profile-avatar-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #14b8a6;
        margin: 0 auto;
    }
=======
>>>>>>> 81a815595f5f88780cc6d1c175df8cfc1a1de085
    
    .club-logo {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #14b8a6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .bg-teal {
        background-color: #14b8a6 !important;
    }
    
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    
    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 3px #e5e7eb;
    }
    
    .timeline-content {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 8px;
        border-left: 3px solid #14b8a6;
    }
</style>
@endpush
@endsection
