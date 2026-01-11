@extends('layouts.student')

@section('title', 'Chi tiết thành viên - ' . ($clubMember->user->name ?? 'UniClubs'))

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Header -->
        <div class="content-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="mb-2">
                        <i class="fas fa-user-circle text-teal me-2"></i>
                        Chi tiết thành viên
                    </h2>
                    <p class="text-muted mb-0">
                        <a href="{{ route('student.club-management.members', ['club' => $clubId]) }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách thành viên
                        </a>
                        <span class="mx-2">|</span>
                        <strong>{{ $club->name }}</strong>
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Thông tin cá nhân -->
            <div class="col-lg-4 mb-4">
                <div class="content-card h-100">
                    <div class="text-center mb-4">
                        @php
                            $avatar = optional($clubMember->user)->avatar ?: 'images/default-avatar.png';
                        @endphp
                        <div class="position-relative d-inline-block">
                            <img src="{{ asset($avatar) }}" 
                                 alt="{{ $clubMember->user->name ?? 'User' }}" 
                                 class="rounded-circle mb-3 member-avatar"
                                 width="150" 
                                 height="150" 
                                 style="object-fit: cover; border: 4px solid #14b8a6; box-shadow: 0 4px 12px rgba(20, 184, 166, 0.2);"
                                 onerror="this.onerror=null; this.src='{{ asset('/images/avatar/avatar.png') }}';">
                        </div>
                        <h4 class="mb-1 fw-bold">{{ $clubMember->user->name ?? 'Chưa xác định' }}</h4>
                        <p class="text-muted mb-0">
                            <i class="fas fa-envelope me-1"></i>{{ $clubMember->user->email ?? '' }}
                        </p>
                    </div>

                    <hr class="my-4">

                    <div>
                        <h6 class="text-muted mb-3 fw-semibold">
                            <i class="fas fa-info-circle text-teal me-2"></i>Thông tin cơ bản
                        </h6>
                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-envelope text-muted me-2" style="width: 20px;"></i>
                                <small class="text-muted d-block">Email</small>
                            </div>
                            <p class="mb-0 ps-4">{{ $clubMember->user->email ?? 'Chưa có' }}</p>
                        </div>
                        @if($clubMember->user->phone)
                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-phone text-muted me-2" style="width: 20px;"></i>
                                <small class="text-muted d-block">Số điện thoại</small>
                            </div>
                            <p class="mb-0 ps-4">{{ $clubMember->user->phone }}</p>
                        </div>
                        @endif
                        @if($clubMember->user->student_id)
                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-id-card text-muted me-2" style="width: 20px;"></i>
                                <small class="text-muted d-block">Mã sinh viên</small>
                            </div>
                            <p class="mb-0 ps-4 fw-semibold">{{ $clubMember->user->student_id }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Thông tin CLB -->
            <div class="col-lg-8">
                <!-- Thông tin trong CLB -->
                <div class="content-card mb-4">
                    <h5 class="mb-4 fw-bold">
                        <i class="fas fa-users-cog text-teal me-2"></i>Thông tin trong CLB
                    </h5>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user-tag text-muted me-2" style="width: 20px;"></i>
                                    <small class="text-muted d-block">Vai trò</small>
                                </div>
                                @php
                                    $roleColors = [
                                        'leader' => 'warning',
                                        'vice_president' => 'info',
                                        'treasurer' => 'success',
                                        'member' => 'secondary',
                                        'owner' => 'danger',
                                    ];
                                    $roleColor = $roleColors[$clubMember->position] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $roleColor }} fs-6 px-3 py-2">
                                    <i class="fas fa-{{ $clubMember->position === 'leader' ? 'crown' : ($clubMember->position === 'vice_president' ? 'user-tie' : ($clubMember->position === 'treasurer' ? 'wallet' : 'user')) }} me-1"></i>
                                    {{ $positionLabels[$clubMember->position] ?? ucfirst($clubMember->position) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-muted me-2" style="width: 20px;"></i>
                                    <small class="text-muted d-block">Trạng thái</small>
                                </div>
                                @php
                                    $statusLabels = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'active' => 'Đang hoạt động',
                                        'inactive' => 'Tạm dừng'
                                    ];
                                    $statusLabel = $statusLabels[$clubMember->status] ?? ucfirst($clubMember->status);
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'active' => 'primary',
                                        'inactive' => 'secondary'
                                    ];
                                    $statusColor = $statusColors[$clubMember->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }} fs-6 px-3 py-2">
                                    <i class="fas fa-{{ $clubMember->status === 'active' ? 'check-circle' : ($clubMember->status === 'pending' ? 'clock' : 'check') }} me-1"></i>
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-calendar-alt text-muted me-2" style="width: 20px;"></i>
                                    <small class="text-muted d-block">Ngày tham gia</small>
                                </div>
                                <p class="mb-0 fw-semibold">
                                    @if($clubMember->joined_at)
                                        <i class="fas fa-clock me-1 text-muted"></i>
                                        {{ \Carbon\Carbon::parse($clubMember->joined_at)->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($clubMember->joined_at)->format('H:i') }}</small>
                                    @else
                                        <span class="text-muted fst-italic">Chưa cập nhật</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-users text-muted me-2" style="width: 20px;"></i>
                                    <small class="text-muted d-block">Câu lạc bộ</small>
                                </div>
                                <p class="mb-0 fw-semibold">{{ $club->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quyền hiện có -->
                <div class="content-card">
                    <h5 class="mb-4 fw-bold">
                        <i class="fas fa-shield-alt text-teal me-2"></i>Quyền hiện có
                    </h5>
                    @if(!empty($permissionNames))
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($permissionNames as $permissionName)
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $permLabels[$permissionName] ?? \Illuminate\Support\Str::headline(str_replace('_',' ',$permissionName)) }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info mb-0 d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>Thành viên này chưa được gán quyền đặc biệt nào.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .content-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(20, 184, 166, 0.1);
        border: 1px solid #a7f3d0;
        transition: all 0.3s ease;
    }
    
    .content-card:hover {
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.15);
    }
    
    .member-avatar {
        transition: transform 0.3s ease;
    }
    
    .member-avatar:hover {
        transform: scale(1.05);
    }
    
    .info-item {
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-box {
        padding: 1.25rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid #14b8a6;
        transition: all 0.2s ease;
        height: 100%;
    }
    
    .info-box:hover {
        background: #f0fdfa;
        transform: translateX(2px);
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    h2, h4, h5, h6 {
        color: #1f2937;
    }
    
    .fw-semibold {
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .content-card {
            padding: 1.5rem;
        }
        
        .member-avatar {
            width: 120px !important;
            height: 120px !important;
        }
    }
</style>
@endpush
@endsection
