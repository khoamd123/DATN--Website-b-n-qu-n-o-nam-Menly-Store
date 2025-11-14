@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Thông tin</th>
                    <th>Vai trò</th>
                    <th>Ngày xóa</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar-fixed me-2">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <br><small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge bg-danger">Admin</span>
                            @else
                                <span class="badge bg-success">User</span>
                            @endif
                        </td>
                        <td>
                            @if($user->deleted_at)
                                {{ $user->deleted_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success text-white" 
                                        onclick="restore('user', {{ $user->id }})" 
                                        title="Khôi phục"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button class="btn btn-danger text-white" 
                                        onclick="forceDelete('user', {{ $user->id }})" 
                                        title="Xóa vĩnh viễn"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center text-muted py-4">
        <i class="fas fa-trash fa-3x mb-3"></i>
        <p>Không có người dùng nào trong thùng rác</p>
    </div>
@endif
