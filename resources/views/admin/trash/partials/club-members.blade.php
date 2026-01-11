@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Thành viên</th>
                    <th>Câu lạc bộ</th>
                    <th>Vị trí</th>
                    <th>Ngày xóa</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $member)
                    <tr>
                        <td>{{ $member->id }}</td>
                        <td>
                            @if($member->user)
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-fixed me-2">
                                        {{ substr($member->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $member->user->name }}</strong>
                                        <br><small class="text-muted">{{ $member->user->email }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </td>
                        <td>
                            @if($member->club)
                                <span class="badge bg-info">{{ $member->club->name }}</span>
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </td>
                        <td>
                            @if($member->position)
                                @switch($member->position)
                                    @case('leader')
                                        <span class="badge bg-danger">Trưởng CLB</span>
                                        @break
                                    @case('vice_president')
                                        <span class="badge bg-warning">Phó CLB</span>
                                        @break
                                    @case('treasurer')
                                        <span class="badge bg-info">Thủ quỹ</span>
                                        @break
                                    @case('member')
                                        <span class="badge bg-success">Thành viên</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $member->position }}</span>
                                @endswitch
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </td>
                        <td>
                            @if($member->deleted_at)
                                {{ $member->deleted_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td style="min-width: 120px; width: 120px;">
                            <div class="d-flex flex-column gap-1">
                                <button class="btn btn-sm btn-success w-100 text-white" 
                                        onclick="restore('club-member', {{ $member->id }})" 
                                        title="Khôi phục">
                                    <i class="fas fa-undo"></i> Khôi phục
                                </button>
                                <button class="btn btn-sm btn-danger w-100 text-white" 
                                        onclick="forceDelete('club-member', {{ $member->id }})" 
                                        title="Xóa vĩnh viễn">
                                    <i class="fas fa-trash"></i> Xóa vĩnh viễn
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
        <i class="fas fa-user-friends fa-3x mb-3"></i>
        <p>Không có thành viên nào trong thùng rác</p>
    </div>
@endif
