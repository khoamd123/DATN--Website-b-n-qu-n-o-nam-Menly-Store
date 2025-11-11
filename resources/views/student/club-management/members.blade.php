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
                <div>
                    <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại trang quản lý CLB
                    </a>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                @php
                    $permLabels = [
                        'dang_thong_bao' => 'Đăng thông báo',
                        'quan_ly_clb' => 'Quản lý CLB',
                        'quan_ly_thanh_vien' => 'Quản lý thành viên',
                        'tao_su_kien' => 'Tạo sự kiện',
                        'xem_bao_cao' => 'Xem báo cáo',
                    ];
                @endphp
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Thành viên</th>
                            <th>Vai trò</th>
                            <th>Quyền hiện có</th>
                            <th class="text-end">Cập nhật</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clubMembers as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $member->user->avatar_url }}" alt="{{ $member->user->name }}" class="rounded-circle me-3" width="45" height="45">
                                    <div>
                                        <div class="fw-semibold">{{ $member->user->name }}</div>
                                        <small class="text-muted">{{ $member->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $positionLabels = [
                                        'leader' => 'Trưởng CLB',
                                        'vice_president' => 'Phó CLB',
                                        'officer' => 'Cán sự',
                                        'member' => 'Thành viên',
                                        'owner' => 'Chủ nhiệm',
                                    ];
                                @endphp
                                <span class="badge bg-light text-dark">
                                    {{ $positionLabels[$member->position] ?? ucfirst($member->position) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($member->permission_names as $permissionName)
                                        <span class="badge bg-success-subtle text-success">
                                            {{ $permLabels[$permissionName] ?? \Illuminate\Support\Str::headline(str_replace('_',' ',$permissionName)) }}
                                        </span>
                                    @endforeach
                                    @if(empty($member->permission_names))
                                        <span class="text-muted">Chưa có quyền</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($member->user_id === $user->id || $member->position === 'leader')
                                    <div class="text-end text-muted">
                                        <small>Không thể chỉnh sửa</small>
                                    </div>
                                @else
                                <form method="POST" class="text-end" action="{{ route('student.club-management.permissions.update', ['club' => $clubId, 'member' => $member->id]) }}">
                                    @csrf
                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                        @foreach($allPermissions as $permission)
                                            <div class="form-check form-check-inline">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    id="perm_{{ $member->id }}_{{ $permission->name }}"
                                                    value="{{ $permission->name }}"
                                                    {{ in_array($permission->name, $member->permission_names ?? []) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="perm_{{ $member->id }}_{{ $permission->name }}">
                                                    {{ $permLabels[$permission->name] ?? \Illuminate\Support\Str::headline(str_replace('_',' ',$permission->name)) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="submit" class="btn btn-outline-primary btn-sm mt-3">
                                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                                    </button>
                                </form>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($member->user_id === $user->id || in_array($member->position, ['leader','owner']))
                                    <span class="text-muted"><small>Không thể xóa</small></span>
                                @else
                                <!-- Delete with reason modal trigger -->
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteMemberModal_{{ $member->id }}">
                                    <i class="fas fa-user-times me-1"></i> Xóa
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="deleteMemberModal_{{ $member->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Xóa thành viên khỏi CLB</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                                                    <button type="submit" class="btn btn-danger">Xóa thành viên</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Chưa có thành viên nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


