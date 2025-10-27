@extends('admin.layouts.app')

@section('title', 'Chi tiết tài nguyên CLB')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết tài nguyên CLB</h3>
                    <div class="card-tools">

                        @if($resource->trashed())
                            <a href="{{ route('admin.club-resources.trash') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại thùng rác
                            </a>
                        @else
                            <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <a href="{{ route('admin.club-resources.edit', $resource->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($resource->trashed())
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Lưu ý:</strong> Tài nguyên này đã bị xóa vào {{ $resource->deleted_at->format('d/m/Y H:i:s') }}. 
                            Bạn có thể khôi phục hoặc xóa vĩnh viễn tài nguyên này.
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Resource Information -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin tài nguyên</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="150"><strong>ID:</strong></td>
                                            <td>{{ $resource->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tiêu đề:</strong></td>
                                            <td>{{ $resource->title }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mô tả:</strong></td>
                                            <td>{!! $resource->description ?: 'Không có mô tả' !!}</td>
                                        </tr>
                                     
                                        <tr>
                                            <td><strong>CLB:</strong></td>
                                            <td>{{ $resource->club->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Người tạo:</strong></td>
                                            <td>{{ $resource->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                @if($resource->trashed())
                                                    <span class="badge bg-danger">Đã xóa</span>
                                                @elseif($resource->status == 'active')
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @elseif($resource->status == 'inactive')
                                                    <span class="badge bg-warning">Không hoạt động</span>
                                                @elseif($resource->status == 'archived')
                                                    <span class="badge bg-secondary">Lưu trữ</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ $resource->status ?? 'Không xác định' }}</span>
                                                @endif
                                                <!-- Debug: {{ $resource->status }} -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lượt xem:</strong></td>
                                            <td>{{ number_format($resource->view_count ?? 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày tạo:</strong></td>
                                            <td>{{ $resource->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Cập nhật lần cuối:</strong></td>
                                            <td>{{ $resource->updated_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        @if($resource->trashed())
                                        <tr>
                                            <td><strong>Ngày xóa:</strong></td>
                                            <td>{{ $resource->deleted_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <!-- Image & Video Album Display -->
                            @if($resource->images && $resource->images->count() > 0)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Album hình ảnh & video ({{ $resource->images->count() }} file)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($resource->images as $index => $image)
                                                <div class="col-md-4 col-sm-6 mb-3">
                                                    <div class="card image-gallery-card {{ $image->is_primary ? 'border-primary' : '' }}" 
                                                         data-bs-toggle="modal" data-bs-target="#imageModal{{ $image->id }}">
                                                        <div class="card-body p-2">
                                                            @if(str_contains($image->image_type, 'video'))
                                                                <!-- Video Thumbnail -->
                                                                <video class="img-fluid rounded" style="height: 200px; width: 100%; object-fit: cover; cursor: pointer;" muted>
                                                                    <source src="{{ $image->image_url }}" type="{{ $image->image_type }}">
                                                                    Your browser does not support the video tag.
                                                                </video>
                                                                <div class="video-play-overlay">
                                                                    <i class="fas fa-play-circle"></i>
                                                                </div>
                                                            @else
                                                                <!-- Image Thumbnail -->
                                                                <img src="{{ $image->thumbnail_url }}" 
                                                                     alt="{{ $image->image_name }}" 
                                                                     class="img-fluid rounded" 
                                                                     style="height: 200px; width: 100%; object-fit: cover; cursor: pointer;">
                                                            @endif
                                                            
                                                            @if($image->is_primary)
                                                                <div class="primary-badge">
                                                                    <i class="fas fa-star"></i>
                                                                </div>
                                                            @endif
                                                            
                                                            <div class="mt-2">
                                                                <small class="text-muted">{{ $image->image_name }}</small>
                                                                <br>
                                                                <small class="text-muted">{{ $image->formatted_size }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Image/Video Modal -->
                                                <div class="modal fade" id="imageModal{{ $image->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $image->image_name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                @if(str_contains($image->image_type, 'video'))
                                                                    <video controls class="img-fluid rounded" style="max-height: 70vh;">
                                                                        <source src="{{ $image->image_url }}" type="{{ $image->image_type }}">
                                                                        Your browser does not support the video tag.
                                                                    </video>
                                                                @else
                                                                    <img src="{{ $image->image_url }}" 
                                                                         alt="{{ $image->image_name }}" 
                                                                         class="img-fluid rounded">
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <small class="text-muted">
                                                                    Kích thước: {{ $image->formatted_size }} | 
                                                                    Loại: {{ $image->image_type }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- File Album Display -->
                            @if($resource->files && $resource->files->count() > 0)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Album file ({{ $resource->files->count() }} file)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($resource->files as $index => $file)
                                                <div class="col-md-4 col-sm-6 mb-3">
                                                    <div class="card file-gallery-card {{ $file->is_primary ? 'border-primary' : '' }}" 
                                                         data-bs-toggle="modal" data-bs-target="#fileModal{{ $file->id }}">
                                                        <div class="card-body p-2">
                                                            <div class="text-center">
                                                                <i class="{{ $file->file_icon }}" style="font-size: 3rem; margin-bottom: 10px;"></i>
                                                            </div>
                                                            @if($file->is_primary)
                                                                <div class="primary-badge">
                                                                    <i class="fas fa-star"></i>
                                                                </div>
                                                            @endif
                                                            <div class="text-center">
                                                                <small class="text-muted">{{ $file->file_name }}</small>
                                                                <br>
                                                                <small class="text-muted">{{ $file->formatted_size }}</small>
                                                            </div>
                                                            <div class="mt-2 text-center">
                                                                <a href="{{ $file->file_url }}" class="btn btn-sm btn-success" download>
                                                                    <i class="fas fa-download"></i> Tải xuống
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- File Modal -->
                                                <div class="modal fade" id="fileModal{{ $file->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ $file->file_name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                @if(str_contains($file->file_type, 'video'))
                                                                    <video controls class="img-fluid rounded" style="max-height: 400px;">
                                                                        <source src="{{ $file->file_url }}" type="{{ $file->file_type }}">
                                                                        Your browser does not support the video tag.
                                                                    </video>
                                                                @elseif(str_contains($file->file_type, 'image'))
                                                                    <img src="{{ $file->file_url }}" alt="{{ $file->file_name }}" class="img-fluid rounded" style="max-height: 400px;">
                                                                @else
                                                                    <div class="text-center">
                                                                        <i class="{{ $file->file_icon }}" style="font-size: 5rem; margin-bottom: 20px;"></i>
                                                                        <p>{{ $file->file_name }}</p>
                                                                        <p class="text-muted">{{ $file->formatted_size }}</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="{{ $file->file_url }}" class="btn btn-success" download>
                                                                    <i class="fas fa-download"></i> Tải xuống
                                                                </a>
                                                                <small class="text-muted ms-3">
                                                                    Kích thước: {{ $file->formatted_size }} | 
                                                                    Loại: {{ $file->file_type }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- External Link -->
                            @if($resource->external_link)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Link ngoài</h5>
                                    </div>
                                    <div class="card-body">
                                        <a href="{{ $resource->external_link }}" target="_blank" class="btn btn-info">
                                            <i class="fas fa-external-link-alt"></i> Mở link
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Tags -->
                            @if($resource->tags && count($resource->tags) > 0)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Tags</h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($resource->tags as $tag)
                                            <span class="badge badge-primary me-1">{{ $tag }}</span>
                                        @endforeach

                                    </div>
                                </div>
                            @endif
                        </div>


                        <div class="col-md-4">
                            <!-- File Preview -->
                            @if($resource->thumbnail_path)
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Xem trước</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="{{ asset('storage/' . $resource->thumbnail_path) }}" 
                                             class="img-fluid" alt="Preview">
                                    </div>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Thao tác</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        @if($resource->trashed())
                                            <!-- Actions for deleted resource -->
                                            <form action="{{ route('admin.club-resources.restore', $resource->id) }}" 
                                                  method="POST" class="d-inline w-100"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục tài nguyên này?')">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="fas fa-undo"></i> Khôi phục
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('admin.club-resources.force-delete', $resource->id) }}" 
                                                  method="POST" class="d-inline w-100"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài nguyên này? Hành động này không thể hoàn tác!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100">
                                                    <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                                </button>
                                            </form>
                                        @else
                                            <!-- Actions for active resource -->
                                            <a href="{{ route('admin.club-resources.edit', $resource->id) }}" 
                                               class="btn btn-warning">
                                                <i class="fas fa-edit"></i> Chỉnh sửa
                                            </a>
                                            
                                            @if($resource->file_path)
                                                <a href="{{ route('admin.club-resources.download', $resource->id) }}" 
                                                   class="btn btn-success">
                                                    <i class="fas fa-download"></i> Tải xuống
                                                </a>
                                            @endif

                                            <!-- Delete -->
                                            <form action="{{ route('admin.club-resources.destroy', $resource->id) }}" 
                                                  method="POST" class="d-inline w-100"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100">
                                                    <i class="fas fa-trash"></i> Xóa tài nguyên
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
.image-preview {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 10px;
    background-color: #f8f9fa;
}

.image-preview img {
    transition: transform 0.3s ease;
}

.image-preview img:hover {
    transform: scale(1.05);
}

.shadow {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

/* Image Gallery Styles */
.image-gallery-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.image-gallery-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.image-gallery-card.border-primary {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.primary-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    z-index: 10;
}

.image-gallery-card .card-body {
    position: relative;
}

/* Video Play Overlay */
.video-play-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 3rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
    pointer-events: none;
    z-index: 10;
}

.image-gallery-card:hover .video-play-overlay {
    color: #007bff;
    transform: translate(-50%, -50%) scale(1.1);
    transition: all 0.3s ease;
}

/* Modal styles */
.modal-body img {
    max-height: 70vh;
    object-fit: contain;
}

/* File Gallery Styles */
.file-gallery-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.file-gallery-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.file-gallery-card.border-primary {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}
</style>

@endsection
