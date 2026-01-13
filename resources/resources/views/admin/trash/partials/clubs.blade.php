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
                                @if($club->description)
                                    @php
                                        $cleanDescription = strip_tags($club->description);
                                        $cleanDescription = html_entity_decode($cleanDescription, ENT_QUOTES, 'UTF-8');
                                        $cleanDescription = trim($cleanDescription);
                                    @endphp
                                    <br><small class="text-muted">{{ Str::limit($cleanDescription, 50) }}</small>
                                @endif
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
                            <div class="d-flex gap-2">
                                <button class="btn btn-success text-white" 
                                        onclick="restore('club', {{ $club->id }})" 
                                        title="Khôi phục"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button class="btn btn-danger text-white" 
                                        onclick="forceDelete('club', {{ $club->id }})" 
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
        <i class="fas fa-building fa-3x mb-3"></i>
        <p>Không có câu lạc bộ nào trong thùng rác</p>
    </div>
@endif
