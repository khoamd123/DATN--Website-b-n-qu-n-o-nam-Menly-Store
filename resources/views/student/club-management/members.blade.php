@extends('layouts.student')

@section('title', 'Quản lý thành viên - UniClubs')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="content-card mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-users-cog text-teal"></i>
                        Quản lý thành viên - {{ $club->name }}
                    </h3>
                    <small class="text-muted">Vai trò của bạn: <strong>{{ ucfirst($userPosition) }}</strong></small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('student.club-management.join-requests', ['club' => $clubId]) }}" class="btn btn-primary btn-sm text-white">
                        <i class="fas fa-inbox me-1"></i> Xem đơn chờ
                    </a>
                    <a href="{{ route('student.club-management.index') }}" class="btn btn-secondary btn-sm text-white">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại trang quản lý CLB
                    </a>
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
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="content-card">
            <div class="table-responsive">
                @php
                    // Nhãn tiếng Việt cho quyền
                    $permLabels = [
                        // Các key tiếng Việt hiện có
                        'dang_thong_bao'      => 'Tạo bài viết',
                        'quan_ly_clb'         => 'Quản lý CLB',
                        'quan_ly_thanh_vien'  => 'Quản lý thành viên',
                        'tao_su_kien'         => 'Tạo sự kiện',
                        'xem_bao_cao'         => 'Xem báo cáo',
                        // Bổ sung key tiếng Anh tương ứng
                        'manage_club'         => 'Quản lý CLB',
                        'manage_members'      => 'Quản lý thành viên',
                        'create_event'        => 'Tạo sự kiện',
                        'post_announcement'   => 'Tạo bài viết',
                        'evaluate_member'     => 'Đánh giá thành viên',
                        'manage_department'   => 'Quản lý phòng ban',
                        'manage_documents'    => 'Quản lý tài liệu',
                        'view_reports'        => 'Xem báo cáo',
                    ];
                    $positionLabels = [
                        'leader' => 'Trưởng CLB',
                        'vice_president' => 'Phó CLB',
                        'treasurer' => 'Thủ quỹ',
                        'member' => 'Thành viên',
                        'owner' => 'Chủ nhiệm',
                    ];
                @endphp
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            
                            <th style="width: 30%;">Thành viên</th>
                            <th style="width: 15%;">Vai trò</th>
                            <th style="width: 35%;">Quyền hiện có</th>
                            <th class="text-end" style="width: 20%;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clubMembers as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $avatar = optional($member->user)->avatar ?: 'images/default-avatar.png';
                                    @endphp
                                    <img src="{{ asset($avatar) }}" alt="{{ $member->user->name ?? 'User' }}" class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold mb-1">{{ $member->user->name }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.85rem;">{{ $member->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $roleColors = [
                                        'leader' => 'warning',
                                        'vice_president' => 'info',
                                        'treasurer' => 'success',
                                        'member' => 'secondary',
                                        'owner' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $roleColors[$member->position] ?? 'secondary' }}">
                                    {{ $positionLabels[$member->position] ?? ucfirst($member->position) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if(!empty($member->permission_names))
                                        @foreach(array_slice($member->permission_names, 0, 3) as $permissionName)
                                            <span class="badge bg-success-subtle text-success" style="font-size: 0.75rem;">
                                                {{ $permLabels[$permissionName] ?? \Illuminate\Support\Str::headline(str_replace('_',' ',$permissionName)) }}
                                            </span>
                                        @endforeach
                                        @if(count($member->permission_names) > 3)
                                            <span class="badge bg-light text-dark" style="font-size: 0.75rem; cursor: pointer;"
                                                  data-bs-toggle="modal"
                                                  data-bs-target="#permissionsModal_{{ $member->id }}">
                                                +{{ count($member->permission_names) - 3 }}
                                            </span>
                                        @endif
                                        <button type="button"
                                                class="btn btn-link btn-sm p-0 ms-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#permissionsModal_{{ $member->id }}">
                                            Xem quyền
                                        </button>

                                    @else
                                        <span class="text-muted small">Chưa có quyền</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                @if(empty($canManageMembers) || !$canManageMembers)
                                    <span class="text-muted small">Chỉ ban quản lý mới chỉnh sửa.</span>
                                @else
                                <div class="d-flex flex-column gap-1">
                                    @if($member->user_id === $user->id || in_array($member->position, ['leader','owner']))
                                        <span class="text-muted small">Không thể chỉnh sửa</span>
                                    @else
                                        <button type="button" class="btn btn-warning btn-sm text-white w-100 action-btn-equal" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editMemberModal_{{ $member->id }}">
                                            <i class="fas fa-edit me-1"></i> Chỉnh sửa
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm text-white w-100 action-btn-equal" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteMemberModal_{{ $member->id }}">
                                            <i class="fas fa-user-times me-1"></i> Xóa
                                        </button>
                                    @endif
                                </div>
                                @endif
                            </td>
                        </tr>

                        <!-- Permissions Modal -->
                        <div class="modal fade" id="permissionsModal_{{ $member->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-shield-alt me-2"></i>Quyền của {{ $member->user->name }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if(!empty($member->permission_names))
                                            <ul class="list-group">
                                                @foreach($member->permission_names as $permissionName)
                                                    <li class="list-group-item d-flex align-items-center">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        <span>{{ $permLabels[$permissionName] ?? \Illuminate\Support\Str::headline(str_replace('_',' ',$permissionName)) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted mb-0">Thành viên này chưa được gán quyền.</p>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Member Modal -->
                        @if(!empty($canManageMembers) && $canManageMembers && $member->user_id !== $user->id && !in_array($member->position, ['leader','owner']))
                        <div class="modal fade" id="editMemberModal_{{ $member->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title">
                                            <i class="fas fa-user-edit me-2"></i>Chỉnh sửa thành viên: {{ $member->user->name }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="{{ route('student.club-management.permissions.update', ['club' => $clubId, 'member' => $member->id]) }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-4">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-user-tag me-2"></i>Vai trò
                                                </label>
                                                <select name="position" class="form-select">
                                                    @foreach(['member' => 'Thành viên', 'treasurer' => 'Thủ quỹ', 'vice_president' => 'Phó CLB'] as $pos => $label)
                                                        <option value="{{ $pos }}" {{ $member->position === $pos ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Lưu ý: Chỉ có thể có 1 Trưởng CLB, 2 Phó CLB và 1 Thủ quỹ. Quyền sẽ được tự động cấp theo vai trò.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary text-white action-btn-equal" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i> Hủy
                                            </button>
                                        <button type="submit" class="btn btn-primary text-white action-btn-equal">
                                                <i class="fas fa-save me-1"></i> Lưu thay đổi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Delete Member Modal -->
                        @if(!empty($canManageMembers) && $canManageMembers && $member->user_id !== $user->id && !in_array($member->position, ['leader','owner']))
                        <div class="modal fade" id="deleteMemberModal_{{ $member->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Xóa thành viên khỏi CLB
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="{{ route('student.club-management.members.remove', ['club' => $clubId, 'member' => $member->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-body">
                                            <p>Bạn chắc chắn muốn xóa <strong>{{ $member->user->name }}</strong> khỏi CLB?</p>
                                            <div class="mb-3">
                                                <label for="reason_{{ $member->id }}" class="form-label">Lý do (tuỳ chọn)</label>
                                                <textarea id="reason_{{ $member->id }}" name="reason" class="form-control" rows="3" placeholder="Nhập lý do xóa (ví dụ: nghỉ CLB, vi phạm nội quy, ...)"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary text-white action-btn-equal" data-bs-dismiss="modal">Huỷ</button>
                                        <button type="submit" class="btn btn-danger text-white action-btn-equal">
                                                <i class="fas fa-trash me-1"></i> Xóa thành viên
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="fas fa-users fa-3x mb-3 d-block text-muted opacity-50"></i>
                                <p class="mb-0">Chưa có thành viên nào.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
    });
</script>
@endpush

@push('styles')
<style>
    .action-btn-equal {
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endpush
@endsection


