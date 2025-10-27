@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Thông tin</th>
                    <th>CLB</th>
                    <th>Người tạo</th>
                    <th>Ngày xóa</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $resource)
                    <tr>
                        <td>{{ $resource->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($resource->thumbnail_path)
                                    <img src="{{ asset('storage/' . $resource->thumbnail_path) }}" 
                                         class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                @endif
                                <div>
                                    <strong>{{ $resource->title }}</strong>
                                    @if($resource->description)
                                        <br><small class="text-muted">{{ Str::limit($resource->description, 50) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $resource->club->name }}</span>
                        </td>
                        <td>{{ $resource->user->name }}</td>
                        <td>{{ $resource->deleted_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-success btn-sm" onclick="restore('club-resource', {{ $resource->id }})" title="Khôi phục">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="forceDelete('club-resource', {{ $resource->id }})" title="Xóa vĩnh viễn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if(method_exists($items, 'links'))
        <div class="d-flex justify-content-center">
            {{ $items->links() }}
        </div>
    @endif
@else
    <div class="text-center py-4">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Không có tài nguyên CLB nào bị xóa</h5>
        <p class="text-muted">Tất cả tài nguyên CLB đều đang hoạt động bình thường.</p>
    </div>
@endif
