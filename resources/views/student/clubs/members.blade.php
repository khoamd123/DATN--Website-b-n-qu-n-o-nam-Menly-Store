@extends('layouts.student')

@section('title', 'Danh sách thành viên - ' . $club->name)

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="content-card mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-users text-teal me-2"></i> Danh sách thành viên
                    </h2>
                    <p class="text-muted mb-0">
                        <a href="{{ route('student.clubs.show', $club->id) }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại trang CLB
                        </a>
                        <span class="mx-2">|</span>
                        <strong>{{ $club->name }}</strong>
                    </p>
                </div>
                <div>
                    <span class="badge bg-primary fs-6">{{ $clubMembers->count() }} thành viên</span>
                </div>
            </div>
        </div>

        <!-- Members List -->
        <div class="content-card">
            @if($clubMembers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">STT</th>
                                <th style="width: 35%;">Thành viên</th>
                                <th style="width: 20%;">Vai trò</th>
                                <th style="width: 20%;">Trạng thái</th>
                                <th style="width: 20%;">Tham gia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clubMembers as $index => $member)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $avatar = optional($member->user)->avatar ?? '/images/avatar/avatar.png';
                                            if ($member->user && $member->user->avatar) {
                                                $avatar = $member->user->avatar;
                                            }
                                        @endphp
                                        <img src="{{ asset($avatar) }}" 
                                             alt="{{ $member->user->name ?? 'User' }}" 
                                             class="rounded-circle me-3" 
                                             width="50" 
                                             height="50" 
                                             style="object-fit: cover;"
                                             onerror="this.onerror=null; this.src='{{ asset('/images/avatar/avatar.png') }}';">
                                        <div>
                                            <div class="fw-semibold mb-1">{{ $member->user->name ?? 'N/A' }}</div>
                                            @if($member->user && $member->user->email)
                                                <small class="text-muted d-block" style="font-size: 0.85rem;">{{ $member->user->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $positionLabels = [
                                            'owner' => 'Chủ nhiệm',
                                            'leader' => 'Trưởng CLB',
                                            'vice_president' => 'Phó CLB',
                                            'treasurer' => 'Thủ quỹ',
                                            'officer' => 'Cán bộ',
                                            'member' => 'Thành viên'
                                        ];
                                        $positionLabel = $positionLabels[$member->position] ?? ucfirst($member->position);
                                        $positionColors = [
                                            'owner' => 'danger',
                                            'leader' => 'primary',
                                            'vice_president' => 'info',
                                            'treasurer' => 'warning',
                                            'officer' => 'success',
                                            'member' => 'secondary'
                                        ];
                                        $positionColor = $positionColors[$member->position] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $positionColor }}">{{ $positionLabel }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusLabels = [
                                            'pending' => 'Chờ duyệt',
                                            'approved' => 'Đã duyệt',
                                            'active' => 'Đang hoạt động',
                                            'inactive' => 'Tạm dừng'
                                        ];
                                        $statusLabel = $statusLabels[$member->status] ?? ucfirst($member->status);
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'active' => 'primary',
                                            'inactive' => 'secondary'
                                        ];
                                        $statusColor = $statusColors[$member->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                                </td>
                                <td>
                                    @if($member->joined_at)
                                        {{ \Carbon\Carbon::parse($member->joined_at)->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted mb-2">Chưa có thành viên</h5>
                    <p class="text-muted">CLB này hiện chưa có thành viên nào.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-teal {
        color: #14b8a6 !important;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush
@endsection

















