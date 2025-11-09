@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Thông tin CLB</th>
                    <th>Trưởng CLB</th>
                    <th>Ngày xóa</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $club)
                    <tr>
                        <td>{{ $club->id }}</td>
                        <td>
                            <div>
                                <strong>{{ $club->name }}</strong>
                                <br><small class="text-muted">{{ $club->slug }}</small>
                                <br><small class="text-muted">{{ substr($club->description, 0, 50) }}{{ strlen($club->description) > 50 ? '...' : '' }}</small>
                            </div>
                        </td>
                        <td>
                            @if($club->owner)
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-fixed me-2">
                                        {{ substr($club->owner->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $club->owner->name }}</strong>
                                        <br><small class="text-muted">{{ $club->owner->email }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Không xác định</span>
                            @endif
                        </td>
                        <td>
                            @if($club->deleted_at)
                                {{ $club->deleted_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-success" onclick="restore('club', {{ $club->id }})">
                                    <i class="fas fa-undo"></i> Khôi phục
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="forceDelete('club', {{ $club->id }})">
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
        <i class="fas fa-building fa-3x mb-3"></i>
        <p>Không có câu lạc bộ nào trong thùng rác</p>
    </div>
@endif
