@extends('admin.layouts.app')

@section('title', 'Chi tiết Bài viết - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Chi tiết Bài viết</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts') }}">Quản lý Bài viết</a></li>
            <li class="breadcrumb-item active">Chi tiết #{{ $post->id }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Nội dung bài viết -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $post->title }}</h5>
                <div>
                    <span class="badge bg-{{ $post->type === 'announcement' ? 'danger' : 'primary' }}">
                        {{ $post->type === 'announcement' ? 'Thông báo' : 'Bài viết' }}
                    </span>
                    <span class="badge bg-{{ $post->status === 'published' ? 'success' : ($post->status === 'hidden' ? 'warning' : 'danger') }}">
                        {{ $post->status === 'published' ? 'Đã xuất bản' : ($post->status === 'hidden' ? 'Ẩn' : 'Đã xóa') }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-user"></i> {{ $post->user->name ?? 'Không xác định' }} | 
                        <i class="fas fa-calendar"></i> {{ $post->created_at->format('d/m/Y H:i') }} |
                        <i class="fas fa-building"></i> {{ $post->club->name ?? 'Không xác định' }}
                    </small>
                </div>
                
                <div class="post-content">
                    <div class="markdown-content">
                        {!! $this->renderMarkdown($post->content) !!}
                    </div>
                </div>

                <!-- Album ảnh -->
                @if($post->images->count() > 0)
                <div class="mt-4">
                    <h6 class="mb-3"><i class="fas fa-images"></i> Album ảnh ({{ $post->images->count() }})</h6>
                    <div class="row">
                        @foreach($post->images as $image)
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <img src="{{ $image->image_url }}" 
                                     class="card-img-top" 
                                     style="height: 200px; object-fit: cover;"
                                     alt="{{ $image->alt_text }}"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal"
                                     onclick="showImageModal('{{ $image->image_url }}', '{{ $image->caption }}')">
                                @if($image->is_featured)
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-warning">⭐ Đại diện</span>
                                    </div>
                                @endif
                                @if($image->caption)
                                <div class="card-body p-2">
                                    <small class="text-muted">{{ $image->caption }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Bình luận -->
        @if($post->comments->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Bình luận ({{ $post->comments->count() }})</h6>
            </div>
            <div class="card-body">
                @foreach($post->comments as $comment)
                <div class="comment-item border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $comment->user->name ?? 'Không xác định' }}</strong>
                            <small class="text-muted ms-2">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteComment({{ $comment->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="mt-2">{{ $comment->content }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <!-- Thông tin chi tiết -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Thông tin chi tiết</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>{{ $post->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Slug:</strong></td>
                        <td>{{ $post->slug }}</td>
                    </tr>
                    <tr>
                        <td><strong>Loại:</strong></td>
                        <td>{{ $post->type === 'announcement' ? 'Thông báo' : 'Bài viết' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Trạng thái:</strong></td>
                        <td>
                            <span class="badge bg-{{ $post->status === 'published' ? 'success' : ($post->status === 'hidden' ? 'warning' : 'danger') }}">
                                {{ $post->status === 'published' ? 'Đã xuất bản' : ($post->status === 'hidden' ? 'Ẩn' : 'Đã xóa') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tác giả:</strong></td>
                        <td>{{ $post->user->name ?? 'Không xác định' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Câu lạc bộ:</strong></td>
                        <td>{{ $post->club->name ?? 'Không xác định' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Ngày tạo:</strong></td>
                        <td>{{ $post->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cập nhật cuối:</strong></td>
                        <td>{{ $post->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Hành động -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Hành động</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    
                    @if($post->status === 'published')
                        <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="hidden">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-eye-slash"></i> Ẩn bài viết
                            </button>
                        </form>
                    @endif
                    
                    @if($post->status === 'hidden')
                        <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="published">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-eye"></i> Hiện bài viết
                            </button>
                        </form>
                    @endif
                    
                    @if($post->status !== 'deleted')
                        <form method="POST" action="{{ route('admin.posts.destroy', $post->id) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                <i class="fas fa-trash"></i> Xóa bài viết
                            </button>
                        </form>
                    @endif
                    
                    @if($post->status === 'deleted')
                        <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-undo"></i> Khôi phục
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteComment(commentId) {
    if (confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
        // Implement delete comment functionality
        console.log('Delete comment:', commentId);
    }
}

function showImageModal(imageUrl, caption) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('modalCaption').textContent = caption || '';
}
</script>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Xem ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid rounded" alt="Ảnh bài viết">
                <p id="modalCaption" class="mt-3 text-muted"></p>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.markdown-content {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: #333;
}

.markdown-content h1 {
    font-size: 2rem;
    font-weight: 700;
    margin: 1.5rem 0 1rem 0;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 0.5rem;
}

.markdown-content h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 1.25rem 0 0.75rem 0;
    color: #34495e;
}

.markdown-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 1rem 0 0.5rem 0;
    color: #34495e;
}

.markdown-content p {
    margin-bottom: 1rem;
}

.markdown-content img {
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
    margin: 1rem 0;
}

.markdown-content img:hover {
    transform: scale(1.02);
}

.markdown-content blockquote {
    border-left: 4px solid #3498db;
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    background-color: #f8f9fa;
    border-radius: 0 0.5rem 0.5rem 0;
    font-style: italic;
    color: #555;
}

.markdown-content ul, .markdown-content ol {
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.markdown-content li {
    margin-bottom: 0.5rem;
}

.markdown-content strong {
    font-weight: 700;
    color: #2c3e50;
}

.markdown-content em {
    font-style: italic;
    color: #555;
}

.markdown-content a {
    color: #3498db;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: all 0.2s ease;
}

.markdown-content a:hover {
    color: #2980b9;
    border-bottom-color: #2980b9;
}

/* Responsive */
@media (max-width: 768px) {
    .markdown-content h1 {
        font-size: 1.5rem;
    }
    
    .markdown-content h2 {
        font-size: 1.25rem;
    }
    
    .markdown-content h3 {
        font-size: 1.1rem;
    }
}
</style>

@php
function renderMarkdown($content) {
    if (!$content) return '';
    
    // Convert markdown to HTML
    $html = $content;
    
    // Headers
    $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
    
    // Bold
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
    
    // Italic
    $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
    
    // Images
    $html = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="img-fluid mb-2" style="max-width: 100%; height: auto; border-radius: 0.375rem;">', $html);
    
    // Links
    $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $html);
    
    // Line breaks
    $html = nl2br($html);
    
    // Blockquotes
    $html = preg_replace('/^> (.*$)/m', '<blockquote class="blockquote"><p class="mb-0">$1</p></blockquote>', $html);
    
    // Lists
    $html = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $html);
    $html = preg_replace('/^- (.*$)/m', '<li>$1</li>', $html);
    $html = preg_replace('/^\d+\. (.*$)/m', '<li>$1</li>', $html);
    
    // Wrap list items in ul/ol tags
    $html = preg_replace_callback('/(<li>.*<\/li>)/s', function($match) {
        if (strpos($match[0], '1.') !== false || strpos($match[0], '2.') !== false || strpos($match[0], '3.') !== false) {
            return '<ol>' . $match[0] . '</ol>';
        } else {
            return '<ul>' . $match[0] . '</ul>';
        }
    }, $html);
    
    return $html;
}
@endphp
