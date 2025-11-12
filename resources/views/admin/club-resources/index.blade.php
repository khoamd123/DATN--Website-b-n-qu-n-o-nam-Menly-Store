@extends('admin.layouts.app')

@section('title', 'Quản lý tài nguyên CLB')

@section('content')

    <style>

        .btn-group .btn {
            margin-right: 3px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>

    <script>
        function deleteResource(resourceId) {
            if (confirm('Bạn có chắc chắn muốn chuyển tài nguyên này vào thùng rác?')) {
                document.getElementById('delete-form-' + resourceId).submit();
            }
        }
    </script>
    <div class="container-fluid">
        <!-- Title Card -->
        <div class="content-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Quản lý Tài nguyên CLB</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.club-resources.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tạo tài nguyên
                    </a>
                </div>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.club-resources.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tài nguyên..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="club_id" class="form-select">
                            <option value="">Tất cả CLB</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                    <div class="col-md-auto ms-auto">
                        <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Làm mới
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resources Table Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Resources Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tiêu đề</th>
                                        <th>CLB</th>
                                        <th>Trạng thái</th>
                                        <th>Lượt xem</th>
                                        <th>Ngày tạo</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($resources as $resource)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $previewImage = null;
                                                        $fullImage = null;
                                                        // Ưu tiên lấy ảnh từ album images
                                                        if($resource->images && $resource->images->count() > 0) {
                                                            $primaryImage = $resource->images->where('is_primary', true)->first();
                                                            $firstImage = $primaryImage ?: $resource->images->first();
                                                            $previewImage = $firstImage->thumbnail_url;
                                                            $fullImage = $firstImage->image_url;
                                                        }
                                                        // Nếu không có trong album images, lấy từ thumbnail_path
                                                        elseif($resource->thumbnail_path) {
                                                            $previewImage = asset('storage/' . $resource->thumbnail_path);
                                                            $fullImage = $previewImage;
                                                        }
                                                        // Nếu không có, kiểm tra files có ảnh không
                                                        elseif($resource->files && $resource->files->count() > 0) {
                                                            foreach($resource->files as $file) {
                                                                if(str_contains($file->file_type, 'image')) {
                                                                    $previewImage = $file->file_url;
                                                                    $fullImage = $file->file_url;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    @if($previewImage)
                                                        <img src="{{ $previewImage }}"
                                                            class="img-thumbnail me-2 resource-thumbnail" 
                                                            data-full-image="{{ $fullImage }}"
                                                            data-title="{{ $resource->title }}"
                                                            style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                                            onclick="showImageModal(this)">
                                                    @else
                                                        <div class="me-2 text-center" style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-file-alt fa-2x text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $resource->title }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            @if($resource->images && $resource->images->count() > 0)
                                                                <i class="fas fa-images"></i> {{ $resource->images->count() }} ảnh
                                                            @endif
                                                            @if($resource->files && $resource->files->count() > 0)
                                                                <i class="fas fa-file"></i> {{ $resource->files->count() }} file
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($resource->club)
                                                    <a href="{{ route('admin.clubs.show', $resource->club->id) }}" 
                                                       class="text-dark text-decoration-none"
                                                       title="Xem chi tiết câu lạc bộ">
                                                        {{ $resource->club->name }}
                                                        <i class="fas fa-external-link-alt fa-xs ms-1 text-muted"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">Không xác định</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($resource->status == 'active')
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @elseif($resource->status == 'inactive')
                                                    <span class="badge bg-warning">Không hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Lưu trữ</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($resource->view_count ?? 0) }}</td>
                                            <td>{{ $resource->created_at->format('d/m/Y H:i') }}</td>
                                            <td style="min-width: 120px; width: 120px;">
                                                <div class="d-flex flex-column gap-1">
                                                    <a href="{{ route('admin.club-resources.show', $resource->id) }}"
                                                        class="btn btn-sm btn-primary text-white w-100">
                                                        <i class="fas fa-eye"></i> Xem chi tiết
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger w-100 text-white"
                                                        onclick="deleteResource({{ $resource->id }})">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                </div>

                                                <!-- Hidden form for delete -->
                                                <form id="delete-form-{{ $resource->id }}"
                                                    action="{{ route('admin.club-resources.destroy', $resource->id) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Không có tài nguyên nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $resources->appends(request()->query())->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagePreviewModalTitle">Xem ảnh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="imagePreviewModalImg" src="" class="img-fluid" style="max-height: 70vh; width: 100%; object-fit: contain;" alt="Preview">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Đóng
                    </button>
                    <a id="imagePreviewModalLink" href="" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Mở trong tab mới
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
function showImageModal(img) {
    const fullImageUrl = img.getAttribute('data-full-image');
    const title = img.getAttribute('data-title');
    
    // Set modal content
    document.getElementById('imagePreviewModalTitle').textContent = title;
    document.getElementById('imagePreviewModalImg').src = fullImageUrl;
    document.getElementById('imagePreviewModalLink').href = fullImageUrl;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    modal.show();
}

// Add hover effect to thumbnails
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.resource-thumbnail');
    thumbnails.forEach(thumb => {
        thumb.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s ease';
        });
        thumb.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>
@endsection

