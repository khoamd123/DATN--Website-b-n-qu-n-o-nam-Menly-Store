@extends('admin.layouts.app')

@section('title', 'Chi tiết Bài viết - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Chi tiết Bài viết 
            @if($post->deleted_at)
                <span class="badge bg-warning text-dark">Trong thùng rác</span>
            @endif
        </h1>
        <div>
            @if($post->deleted_at)
                {{-- Bài viết đã xóa - hiển thị nút khôi phục --}}
                <form method="POST" action="{{ route('admin.trash.restore') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="type" value="post">
                    <input type="hidden" name="id" value="{{ $post->id }}">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Bạn có chắc chắn muốn khôi phục bài viết này?')">
                        <i class="fas fa-undo"></i> Khôi phục
                    </button>
                </form>
                <a href="{{ route('admin.posts.trash') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại thùng rác
                </a>
            @else
                {{-- Bài viết chưa xóa - hiển thị nút chỉnh sửa --}}
                <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            @endif
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <!-- Nội dung bài viết -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt"></i> Nội dung bài viết</h5>
            </div>
            <div class="card-body">
                <h2 class="mb-4">{{ $post->title }}</h2>
                
                {{-- Hiển thị nội dung văn bản trước (nếu có) --}}
                @if(strip_tags($post->content))
                    <div class="content-body mb-4" style="line-height: 1.8; font-size: 15px;">
                        {!! $post->content !!}
                    </div>
                @endif
                
                {{-- Hiển thị gallery ảnh với lightbox --}}
                @if($post->attachments && $post->attachments->count() > 0)
                    <div class="mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-images text-primary"></i> Thư viện ảnh 
                            <span class="badge bg-primary">{{ $post->attachments->count() }}</span>
                        </h5>
                    </div>
                    <div class="row g-3 mb-4">
                        @foreach($post->attachments as $index => $attachment)
                            <div class="col-md-4 col-sm-6">
                                <div class="position-relative" style="cursor: pointer;" onclick="openLightbox({{ $index }})">
                                    <img 
                                        src="{{ asset($attachment->file_url) }}" 
                                        alt="Ảnh {{ $index + 1 }}" 
                                        class="img-fluid rounded border shadow-sm" 
                                        style="height: 200px; width: 100%; object-fit: cover; transition: transform 0.3s;"
                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'"
                                    >
                                    <div class="position-absolute top-0 end-0 bg-dark bg-opacity-50 text-white px-2 py-1 rounded-bottom-start">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Lightbox Modal -->
                    <div class="modal fade" id="imageLightbox" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content bg-dark">
                                <div class="modal-header border-secondary">
                                    <h5 class="modal-title text-white" id="lightboxTitle">Ảnh 1 / {{ $post->attachments->count() }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center p-0" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
                                    <img id="lightboxImage" src="" alt="" class="img-fluid" style="max-height: 80vh;">
                                </div>
                                <div class="modal-footer border-secondary justify-content-between">
                                    <button type="button" class="btn btn-outline-light" onclick="previousImage()">
                                        <i class="fas fa-chevron-left"></i> Trước
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    <button type="button" class="btn btn-outline-light" onclick="nextImage()">
                                        Sau <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($post->image)
                    <div class="mb-4 text-center">
                        <img 
                            src="{{ asset($post->image) }}" 
                            alt="{{ $post->title }}" 
                            class="img-fluid rounded shadow-sm" 
                            style="max-height: 500px; cursor: pointer;"
                            onclick="openSingleImage('{{ asset($post->image) }}')"
                        >
                    </div>
                    
                    <!-- Single Image Lightbox -->
                    <div class="modal fade" id="singleImageLightbox" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content bg-dark">
                                <div class="modal-header border-secondary">
                                    <h5 class="modal-title text-white">Ảnh đính kèm</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center p-0" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
                                    <img id="singleLightboxImage" src="" alt="" class="img-fluid" style="max-height: 80vh;">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Thông tin bài viết -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin bài viết</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Tiêu đề:</strong>
                    <p class="mt-1">{{ $post->title }}</p>
                </div>
                

                
                <div class="mb-3">
                    <strong>Câu lạc bộ:</strong>
                    <p class="mt-1">
                        @if($post->club)
                            <span class="badge badge-primary">{{ $post->club->name }}</span>
                        @else
                            <span class="badge badge-secondary">Không có CLB</span>
                        @endif
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong>Tác giả:</strong>
                    <p class="mt-1">
                        @if($post->user)
                            {{ $post->user->name }}
                        @else
                            <span class="text-muted">Không xác định</span>
                        @endif
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong>Loại bài viết:</strong>
                    <p class="mt-1">
                        @if($post->type == 'post')
                            <span class="badge badge-info">Bài viết thường</span>
                        @elseif($post->type == 'announcement')
                            <span class="badge badge-warning">Thông báo</span>
                        @endif
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong>Trạng thái:</strong>
                    <p class="mt-1">
                        @if($post->status == 'published')
                            <span class="badge badge-success">Công khai</span>

                        @elseif($post->status == 'members_only')
                            <span class="badge badge-info">Chỉ thành viên CLB</span>

                        @elseif($post->status == 'hidden')
                            <span class="badge badge-secondary">Ẩn</span>
                        @elseif($post->status == 'deleted')
                            <span class="badge badge-danger">Đã xóa</span>
                        @endif

                        
                        @if($post->deleted_at)
                            <span class="badge badge-warning ms-2">Trong thùng rác</span>
                        @endif
                    </p>
                </div>
                
                @if($post->deleted_at)
                <div class="mb-3">
                    <strong>Ngày xóa:</strong>
                    <p class="mt-1 text-danger">{{ $post->deleted_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                

                <div class="mb-3">
                    <strong>Ngày tạo:</strong>
                    <p class="mt-1">{{ $post->created_at->format('d/m/Y H:i') }}</p>
                </div>
                
                <div class="mb-3">
                    <strong>Cập nhật lần cuối:</strong>
                    <p class="mt-1">{{ $post->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Thống kê -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ number_format($post->views ?? 0) }}</h4>
                            <small class="text-muted">Lượt xem</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">{{ $post->download_count ?? 0 }}</h4>
                        <small class="text-muted">Lượt tải</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hành động -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> Hành động</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">

                    @if($post->deleted_at)
                        <!-- Bài viết đã bị xóa -->
                        <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Bạn có chắc muốn khôi phục bài viết này?')">
                                <i class="fas fa-undo"></i> Khôi phục bài viết
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.posts.force-delete', $post->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn bài viết này? Hành động này không thể hoàn tác!')">
                                <i class="fas fa-trash"></i> Xóa vĩnh viễn
                            </button>
                        </form>
                    @else
                        <!-- Bài viết chưa bị xóa -->
                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        
                        @if($post->status == 'published')
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="members_only">
                                <button type="submit" class="btn btn-info w-100" onclick="return confirm('Bạn có chắc muốn chuyển bài viết này thành chỉ thành viên CLB?')">
                                    <i class="fas fa-users"></i> Chỉ thành viên CLB
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="hidden">
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Bạn có chắc muốn ẩn bài viết này?')">
                                    <i class="fas fa-eye-slash"></i> Ẩn bài viết
                                </button>
                            </form>
                        @elseif($post->status == 'members_only')
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="published">
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Bạn có chắc muốn công khai bài viết này?')">
                                    <i class="fas fa-eye"></i> Công khai
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="hidden">
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Bạn có chắc muốn ẩn bài viết này?')">
                                    <i class="fas fa-eye-slash"></i> Ẩn bài viết
                                </button>
                            </form>
                        @elseif($post->status == 'hidden')
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="published">
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Bạn có chắc muốn công khai bài viết này?')">
                                    <i class="fas fa-eye"></i> Công khai
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="members_only">
                                <button type="submit" class="btn btn-info w-100" onclick="return confirm('Bạn có chắc muốn chuyển bài viết này thành chỉ thành viên CLB?')">
                                    <i class="fas fa-users"></i> Chỉ thành viên CLB
                                </button>
                            </form>
                        @endif
                        
                        <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="deleted">
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bạn có chắc muốn xóa bài viết này? Hành động này không thể hoàn tác!')">
                                <i class="fas fa-trash"></i> Xóa bài viết
                            </button>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.content-body {
    line-height: 1.6;
}

.content-body h1, .content-body h2, .content-body h3, .content-body h4, .content-body h5, .content-body h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.content-body p {
    margin-bottom: 1rem;
}

.content-body ul, .content-body ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.content-body blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #6c757d;
}

.content-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.content-body table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.content-body table th,
.content-body table td {
    border: 1px solid #dee2e6;
    padding: 0.75rem;
    text-align: left;
}

.content-body table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.badge {
    font-size: 0.875em;
    padding: 0.375rem 0.75rem;
}

.badge-primary { background-color: #007bff; }
.badge-info { background-color: #17a2b8; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-success { background-color: #28a745; }
.badge-secondary { background-color: #6c757d; }
.badge-danger { background-color: #dc3545; }
</style>

@if($post->attachments && $post->attachments->count() > 0)
<script>
    // Lưu danh sách ảnh và index hiện tại
    const imageUrls = [
        @foreach($post->attachments as $attachment)
            "{{ asset($attachment->file_url) }}",
        @endforeach
    ];
    let currentImageIndex = 0;

    function openLightbox(index) {
        currentImageIndex = index;
        updateLightboxImage();
        var lightbox = new bootstrap.Modal(document.getElementById('imageLightbox'));
        lightbox.show();
    }

    function updateLightboxImage() {
        const img = document.getElementById('lightboxImage');
        const title = document.getElementById('lightboxTitle');
        if (img && title) {
            img.src = imageUrls[currentImageIndex];
            title.textContent = `Ảnh ${currentImageIndex + 1} / ${imageUrls.length}`;
        }
    }

    function previousImage() {
        currentImageIndex = (currentImageIndex - 1 + imageUrls.length) % imageUrls.length;
        updateLightboxImage();
    }

    function nextImage() {
        currentImageIndex = (currentImageIndex + 1) % imageUrls.length;
        updateLightboxImage();
    }

    // Điều hướng bằng phím
    document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('imageLightbox');
        if (lightbox.classList.contains('show')) {
            if (e.key === 'ArrowLeft') previousImage();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'Escape') {
                const modal = bootstrap.Modal.getInstance(lightbox);
                if (modal) modal.hide();
            }
        }
    });
</script>
@endif

@if($post->image)
<script>
    function openSingleImage(imageUrl) {
        const img = document.getElementById('singleLightboxImage');
        if (img) {
            img.src = imageUrl;
        }
        var lightbox = new bootstrap.Modal(document.getElementById('singleImageLightbox'));
        lightbox.show();
    }
</script>
@endif
@endsection
