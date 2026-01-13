@extends('admin.layouts.app')

@section('title', 'Hồ sơ - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-user-circle me-2"></i>Hồ sơ cá nhân</h1>
            <p class="text-muted mb-0">Thông tin tài khoản admin</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid">
    <div class="row">
        <!-- Thông tin cá nhân -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin cá nhân</h5>
                </div>
                <div class="card-body text-center">
                    <div class="user-avatar-large mb-3">
                        @php
                            $avatarPath = $user->avatar ? public_path($user->avatar) : null;
                            $hasAvatar = $avatarPath && file_exists($avatarPath);
                        @endphp
                        @if($hasAvatar)
                            <img src="{{ asset($user->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #14b8a6;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 3rem; border: 3px solid #14b8a6;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    
                    @if($user->is_admin)
                        <span class="badge bg-danger mb-3">
                            <i class="fas fa-shield-alt me-1"></i>Quản trị viên
                        </span>
                    @endif
                    
                    <div class="text-start mt-4">
                        <div class="mb-3">
                            <strong><i class="fas fa-id-card me-2 text-muted"></i>Mã sinh viên:</strong><br>
                            <span class="badge bg-success">{{ $user->student_id ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-phone me-2 text-muted"></i>Số điện thoại:</strong><br>
                            <span class="text-muted">{{ $user->phone ?? 'Chưa cập nhật' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-map-marker-alt me-2 text-muted"></i>Địa chỉ:</strong><br>
                            <span class="text-muted">{{ $user->address ?? 'Chưa cập nhật' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-calendar-plus me-2 text-muted"></i>Ngày tạo tài khoản:</strong><br>
                            <span class="text-muted">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong><i class="fas fa-calendar-check me-2 text-muted"></i>Cập nhật lần cuối:</strong><br>
                            <span class="text-muted">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                        </div>
                        
                        @if($user->last_online)
                            <div class="mb-3">
                                <strong><i class="fas fa-clock me-2 text-muted"></i>Lần cuối online:</strong><br>
                                @php
                                    $diffInMinutes = $user->last_online->diffInMinutes(now());
                                    $diffInHours = $user->last_online->diffInHours(now());
                                    $diffInDays = $user->last_online->diffInDays(now());
                                @endphp
                                <span class="text-muted">
                                    {{ $user->last_online->format('d/m/Y H:i:s') }}
                                    @if($diffInMinutes < 60)
                                        <small class="text-success">({{ $diffInMinutes }} phút trước)</small>
                                    @elseif($diffInHours < 24)
                                        <small class="text-info">({{ $diffInHours }} giờ trước)</small>
                                    @else
                                        <small class="text-warning">({{ $diffInDays }} ngày trước)</small>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Thông tin bổ sung -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin chi tiết</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong><br>
                            <span class="text-muted">{{ $user->email }}</span>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Trạng thái tài khoản:</strong><br>
                            @if($user->is_admin)
                                <span class="badge bg-danger">Quản trị viên</span>
                            @else
                                <span class="badge bg-secondary">Người dùng</span>
                            @endif
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Mã sinh viên:</strong><br>
                            <span class="text-muted">{{ $user->student_id ?? 'Không có' }}</span>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Số điện thoại:</strong><br>
                            <span class="text-muted">{{ $user->phone ?? 'Chưa cập nhật' }}</span>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <strong>Địa chỉ:</strong><br>
                            <span class="text-muted">{{ $user->address ?? 'Chưa cập nhật' }}</span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Ngày tạo tài khoản:</strong><br>
                            <span class="text-muted">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <strong>Cập nhật lần cuối:</strong><br>
                            <span class="text-muted">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Thống kê hoạt động (nếu có) -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê hoạt động</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat-item">
                                <h3 class="text-primary">{{ $user->posts()->count() }}</h3>
                                <p class="text-muted mb-0">Bài viết</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <h3 class="text-success">{{ $user->clubMembers()->whereIn('status', ['approved', 'active'])->count() }}</h3>
                                <p class="text-muted mb-0">CLB tham gia</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <h3 class="text-info">{{ $user->comments()->count() }}</h3>
                                <p class="text-muted mb-0">Bình luận</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .user-avatar-large {
        position: relative;
    }
    
    .stat-item {
        padding: 1rem;
    }
    
    .stat-item h3 {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
</style>
@endpush
@endsection

