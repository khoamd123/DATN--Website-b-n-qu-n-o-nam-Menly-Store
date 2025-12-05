@extends('admin.layouts.app')

@section('title', 'Chi tiết tài nguyên CLB')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Chi tiết tài nguyên CLB</h1>
        <div class="d-flex gap-2">
            @if($resource->trashed())
                <form action="{{ route('admin.club-resources.restore', $resource->id) }}" 
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục tài nguyên này?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-undo me-1"></i> Khôi phục
                    </button>
                </form>
                <form action="{{ route('admin.club-resources.force-delete', $resource->id) }}" 
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài nguyên này? Hành động này không thể hoàn tác!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Xóa vĩnh viễn
                    </button>
                </form>
                <a href="{{ route('admin.club-resources.trash') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            @else
                <a href="{{ route('admin.club-resources.edit', $resource->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Chỉnh sửa
                </a>
                @if($resource->file_path)
                    <a href="{{ route('admin.club-resources.download', $resource->id) }}" 
                       class="btn btn-success" target="_blank">
                        <i class="fas fa-download me-1"></i> Tải xuống
                    </a>
                @endif
                <form action="{{ route('admin.club-resources.destroy', $resource->id) }}" 
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Xóa
                    </button>
                </form>
                <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            @endif
        </div>
    </div>
</div>

@if($resource->trashed())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Lưu ý:</strong> Tài nguyên này đã bị xóa vào {{ $resource->deleted_at->format('d/m/Y H:i:s') }}. 
        Bạn có thể khôi phục hoặc xóa vĩnh viễn tài nguyên này.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid">
    <!-- Resource Information -->
            <div class="card mb-4">
        <div class="card-body">
            <!-- Title Section -->
            <div class="mb-4">
                <h2 class="mb-2" style="font-weight: 700; color: #333;">{{ $resource->title }}</h2>
            </div>

            <!-- Description Section -->
            @if($resource->description)
            <div class="mb-4">
                <div style="color: #555; font-size: 1rem; line-height: 1.6;">
                    {!! $resource->description !!}
                </div>
            </div>
            @endif

            <!-- Metadata Section - 2 Columns -->
            <div class="row mt-4">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-users me-2" style="color: #6c757d; font-size: 1.1rem;"></i>
                            <span style="color: #6c757d; font-weight: 500;">CLB</span>
                        </div>
                        <div>
                            <span class="badge rounded-pill px-3 py-2" style="background-color: #e3f2fd; color: #1976d2; font-size: 0.9rem;">
                                {{ $resource->club->name }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-eye me-2" style="color: #6c757d; font-size: 1.1rem;"></i>
                            <span style="color: #6c757d; font-weight: 500;">Lượt xem</span>
                        </div>
                        <div style="color: #333; font-size: 1rem;">
                            {{ number_format($resource->view_count ?? 0) }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-calendar me-2" style="color: #6c757d; font-size: 1.1rem;"></i>
                            <span style="color: #6c757d; font-weight: 500;">Ngày tạo</span>
                        </div>
                        <div style="color: #333; font-size: 1rem;">
                            {{ $resource->created_at->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-user me-2" style="color: #6c757d; font-size: 1.1rem;"></i>
                            <span style="color: #6c757d; font-weight: 500;">Người tạo</span>
                        </div>
                        <div style="color: #333; font-size: 1rem; font-weight: 600;">
                            {{ $resource->user->name }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-info-circle me-2" style="color: #6c757d; font-size: 1.1rem;"></i>
                            <span style="color: #6c757d; font-weight: 500;">Trạng thái</span>
                        </div>
                        <div>
                            @if($resource->trashed())
                                <span class="badge rounded-pill px-3 py-2 bg-danger" style="font-size: 0.9rem;">Đã xóa</span>
                            @elseif($resource->status == 'active')
                                <span class="badge rounded-pill px-3 py-2 bg-success" style="font-size: 0.9rem;">Hoạt động</span>
                            @elseif($resource->status == 'inactive')
                                <span class="badge rounded-pill px-3 py-2 bg-warning" style="font-size: 0.9rem;">Không hoạt động</span>
                            @elseif($resource->status == 'archived')
                                <span class="badge rounded-pill px-3 py-2 bg-secondary" style="font-size: 0.9rem;">Lưu trữ</span>
                            @else
                                <span class="badge rounded-pill px-3 py-2 bg-light text-dark" style="font-size: 0.9rem;">{{ $resource->status ?? 'Không xác định' }}</span>
                            @endif
            </div>
        </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-clock me-2" style="color: #6c757d; font-size: 1.1rem;"></i>
                            <span style="color: #6c757d; font-weight: 500;">Cập nhật lần cuối</span>
                        </div>
                        <div style="color: #333; font-size: 1rem;">
                            {{ $resource->updated_at->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>

            @if($resource->trashed())
            <div class="alert alert-warning mt-3 mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Tài nguyên này đã bị xóa vào {{ $resource->deleted_at->format('d/m/Y H:i:s') }}.
            </div>
            @endif
        </div>
    </div>

    <!-- Image & Video Album Display -->
    @if($resource->images && $resource->images->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-images me-2"></i>Album hình ảnh & video ({{ $resource->images->count() }} file)</h5>
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
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-file me-2"></i>Album file ({{ $resource->files->count() }} file)</h5>
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
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-link me-2"></i>Link ngoài</h5>
            </div>
            <div class="card-body">
                <a href="{{ $resource->external_link }}" target="_blank" class="btn btn-info">
                    <i class="fas fa-external-link-alt me-1"></i> Mở link
                </a>
            </div>
        </div>
    @endif

    <!-- Tags -->
    @if($resource->tags && count($resource->tags) > 0)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Tags</h5>
            </div>
            <div class="card-body">
                @foreach($resource->tags as $tag)
                    <span class="badge bg-primary me-2 mb-2">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @endif
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
