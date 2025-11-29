@extends('layouts.student')

@section('title', 'Chi tiết tài nguyên - ' . $club->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="text-decoration-none mb-2 d-inline-block">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại quản lý tài nguyên
                        </a>
                        <h4 class="mb-0"><i class="fas fa-folder-open me-2"></i>Chi tiết tài nguyên - {{ $club->name }}</h4>
                    </div>
                    <div class="d-flex gap-2">
                        @php
                            $position = $user->getPositionInClub($clubId);
                            $canEdit = in_array($position, ['leader', 'vice_president', 'treasurer']);
                        @endphp
                        @if($canEdit)
                            <a href="{{ route('student.club-management.resources.edit', ['club' => $clubId, 'resource' => $resource->id]) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i> Chỉnh sửa
                            </a>
                        @endif
                        <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <!-- Resource Information -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-4">
                                    <strong class="text-muted">Tiêu đề:</strong>
                                    <h3 class="mb-0 mt-2">{{ $resource->title }}</h3>
                                </div>
                                
                                @if($resource->description)
                                    @php
                                        // Loại bỏ HTML tags và hiển thị text thuần
                                        $cleanDescription = strip_tags($resource->description);
                                        $cleanDescription = trim(preg_replace('/\s+/', ' ', $cleanDescription));
                                    @endphp
                                    @if($cleanDescription)
                                        <div class="mb-4">
                                            <strong class="text-muted">Mô tả:</strong>
                                            <p class="mb-0 mt-2">{{ $cleanDescription }}</p>
                                        </div>
                                    @endif
                                @endif

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-users text-muted me-2" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">CLB</small>
                                                <span class="badge bg-info">{{ $resource->club->name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user text-muted me-2" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Người tạo</small>
                                                <strong>{{ $resource->user->name }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-eye text-muted me-2" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Lượt xem</small>
                                                <strong>{{ number_format($resource->view_count ?? 0) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle text-muted me-2" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Trạng thái</small>
                                                @if($resource->status == 'active')
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @elseif($resource->status == 'inactive')
                                                    <span class="badge bg-warning">Không hoạt động</span>
                                                @elseif($resource->status == 'archived')
                                                    <span class="badge bg-secondary">Lưu trữ</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ $resource->status ?? 'Không xác định' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar text-muted me-2" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Ngày tạo</small>
                                                <strong>{{ $resource->created_at->format('d/m/Y H:i:s') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-muted me-2" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Cập nhật lần cuối</small>
                                                <strong>{{ $resource->updated_at->format('d/m/Y H:i:s') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                        <div class="card file-gallery-card {{ $file->is_primary ? 'border-primary' : '' }}">
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
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
.modal-body img,
.modal-body video {
    max-height: 70vh;
    object-fit: contain;
}

/* File Gallery Styles */
.file-gallery-card {
    transition: all 0.3s ease;
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

.file-gallery-card .card-body {
    position: relative;
}
</style>
@endpush
@endsection

