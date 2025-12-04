@extends('admin.layouts.app')

@section('title', 'Thông báo - CLB Admin')

@section('styles')
<style>
    .table tbody tr {
        transition: background-color 0.2s;
    }
    .table tbody tr:hover {
        background-color: #e9ecef !important;
    }
    .user-avatar-fixed {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        flex-shrink: 0;
        overflow: hidden;
    }
    .user-avatar-fixed img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    .btn-xs {
        padding: 0 !important;
        font-size: 0.85rem;
        line-height: 1;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s ease;
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        max-width: 36px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
    }
    .btn-xs:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .btn-xs i {
        margin: 0;
    }
    .action-buttons .badge {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        max-width: 36px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        font-size: 0.85rem;
    }
    .action-buttons {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.5rem;
        align-items: center;
    }
    .action-buttons form {
        margin: 0;
        display: inline-flex;
    }
    .action-buttons button,
    .action-buttons .badge {
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <h1>🔔 Thông báo</h1>
    <p class="text-muted">Danh sách thông báo hệ thống</p>
</div>

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.notifications') }}" class="row g-3 align-items-end">
            <!-- Tìm kiếm -->
            <div class="col-md-2">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Tìm kiếm thông báo..."
                       value="{{ request('search') }}">
            </div>
            
            <!-- Bộ lọc người gửi -->
            <div class="col-md-2">
                <select name="sender_id" class="form-select">
                    <option value="">Tất cả người gửi</option>
                    @foreach($senders as $sender)
                        <option value="{{ $sender->id }}" {{ request('sender_id') == $sender->id ? 'selected' : '' }}>
                            {{ $sender->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Bộ lọc loại thông báo -->
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Tất cả loại</option>
                    @foreach($notificationTypes as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Bộ lọc trạng thái -->
            <div class="col-md-2">
                <select name="filter" class="form-select">
                    <option value="all" {{ request('filter', 'all') == 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="unread" {{ request('filter') == 'unread' ? 'selected' : '' }}>Chưa đọc</option>
                    <option value="read" {{ request('filter') == 'read' ? 'selected' : '' }}>Đã đọc</option>
                </select>
            </div>
            
            <!-- Nút tìm kiếm -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Tìm kiếm
                </button>
            </div>
            
            <!-- Nút làm mới -->
            <div class="col-md-2">
                <a href="{{ route('admin.notifications') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-sync-alt me-1"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

@if($notifications->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-bell"></i> 
                @if(request('filter') == 'unread')
                    Thông báo chưa đọc ({{ $notifications->total() }})
                @elseif(request('filter') == 'read')
                    Thông báo đã đọc ({{ $notifications->total() }})
                @else
                    Tất cả thông báo ({{ $notifications->total() }})
                @endif
                @if(request('sender_id') || request('type') || request('search'))
                    <small class="text-muted ms-2">
                        <i class="fas fa-filter"></i> Đang lọc
                    </small>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">STT</th>
                            <th>Người gửi</th>
                            <th>Tiêu đề</th>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $index => $notification)
                            @php
                                $sender = \App\Models\User::find($notification->sender_id);
                                $isRead = false;
                                $postId = null;
                                try {
                                    $adminId = session('user_id');
                                    if ($adminId) {
                                        $read = \App\Models\NotificationRead::where('notification_id', $notification->id)
                                            ->where('user_id', $adminId)
                                            ->where('is_read', true)
                                            ->first();
                                        $isRead = $read !== null;
                                    }
                                    
                                    // Tìm post_id từ notification message nếu là thông báo về bài viết mới
                                    if ($notification->title === 'Bài viết mới được đăng' && $sender) {
                                        // Parse message để tìm post title trong dấu ngoặc kép
                                        if (preg_match('/"([^"]+)"/', $notification->message, $matches)) {
                                            $postTitle = $matches[1];
                                            $post = \App\Models\Post::where('title', $postTitle)
                                                ->where('user_id', $sender->id)
                                                ->orderBy('created_at', 'desc')
                                                ->first();
                                            if ($post) {
                                                $postId = $post->id;
                                            }
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $isRead = false;
                                }
                            @endphp
                            <tr class="{{ !$isRead ? 'table-info' : '' }}">
                                <td>{{ $notifications->firstItem() + $index }}</td>
                                <td>
                                    @if($sender)
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar-fixed me-2">
                                                @php
                                                    $avatarUrl = $sender->avatar_url ?? '';
                                                    $isDefaultAvatar = $avatarUrl && strpos($avatarUrl, 'avatar.png') !== false;
                                                @endphp
                                                @if($avatarUrl && !$isDefaultAvatar)
                                                    <img src="{{ $avatarUrl }}" alt="{{ $sender->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" onerror="this.parentElement.innerHTML='{{ substr($sender->name, 0, 1) }}';">
                                                @else
                                                    {{ substr($sender->name, 0, 1) }}
                                                @endif
                                            </div>
                                            <div>
                                                <strong>{{ $sender->name }}</strong>
                                                <br><small class="text-muted">{{ $sender->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Không xác định</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $notification->title }}</strong>
                                    @if(!$isRead)
                                        <span class="badge bg-primary ms-2">Mới</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        <span class="text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                    </small>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <div class="action-buttons">
                                        <button type="button" 
                                                class="btn btn-xs btn-primary" 
                                                onclick="showNotificationDetail({{ $notification->id }}, '{{ addslashes($notification->title) }}', '{{ addslashes($notification->message) }}', '{{ $sender ? addslashes($sender->name) : 'Không xác định' }}', '{{ $sender ? addslashes($sender->email) : '' }}', '{{ $notification->created_at->format('d/m/Y H:i') }}', '{{ $notification->created_at->diffForHumans() }}', {{ $isRead ? 'true' : 'false' }}, '{{ $sender ? addslashes($sender->avatar_url ?? '') : '' }}', {{ $postId ?? 'null' }})"
                                                title="Xem chi tiết thông báo">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$isRead)
                                            <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-xs btn-success" 
                                                        title="Đánh dấu đã đọc">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-secondary" title="Đã đọc">
                                                <i class="fas fa-check-double"></i>
                                            </span>
                                        @endif
                                        <form action="{{ route('admin.notifications.delete', $notification->id) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-xs btn-danger" 
                                                    title="Xóa thông báo">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        <i class="fas fa-info-circle"></i>
                        <span>
                            Hiển thị <strong>{{ $notifications->firstItem() }}</strong> - <strong>{{ $notifications->lastItem() }}</strong> 
                            trong tổng <strong>{{ $notifications->total() }}</strong> kết quả
                        </span>
                    </div>
                    <nav>
                        <ul class="pagination">
                            @foreach ($notifications->getUrlRange(1, $notifications->lastPage()) as $page => $url)
                                @if ($page == $notifications->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </nav>
                </div>
            @else
                <div class="pagination-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        Hiển thị <strong>{{ $notifications->firstItem() ?? 0 }}</strong> - <strong>{{ $notifications->lastItem() ?? 0 }}</strong> 
                        trong tổng <strong>{{ $notifications->total() }}</strong> kết quả
                    </span>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">Chưa có thông báo nào</h4>
        <p class="text-muted">Các thông báo mới sẽ xuất hiện ở đây.</p>
    </div>
@endif

<!-- Modal Chi tiết thông báo -->
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationDetailModalLabel">
                    <i class="fas fa-bell me-2"></i>Chi tiết thông báo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Người gửi:</label>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar-fixed me-2" id="modalSenderAvatar"></div>
                        <div>
                            <strong id="modalSenderName"></strong>
                            <br><small class="text-muted" id="modalSenderEmail"></small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Tiêu đề:</label>
                    <h6 id="modalTitle" class="mb-0"></h6>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Nội dung:</label>
                    <div class="border rounded p-3 bg-light" id="modalMessage" style="min-height: 100px; max-height: 400px; overflow-y: auto; cursor: pointer;" onclick="handleMessageClick()"></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Thời gian:</label>
                    <div>
                        <strong id="modalCreatedAt"></strong>
                        <br><small class="text-muted" id="modalCreatedAtDiff"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <form id="markReadForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" id="markReadButton" style="display: none;">
                        <i class="fas fa-check"></i> Đánh dấu đã đọc
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPostId = null;

function showNotificationDetail(id, title, message, senderName, senderEmail, createdAt, createdAtDiff, isRead, avatarUrl, postId) {
    // Lưu postId để sử dụng khi click vào nội dung
    currentPostId = postId;
    
    // Set modal content
    document.getElementById('modalTitle').textContent = title;
    // Escape HTML để hiển thị an toàn, sau đó thay thế \n thành <br>
    const messageDiv = document.getElementById('modalMessage');
    messageDiv.textContent = message; // Sử dụng textContent để tránh XSS
    messageDiv.style.whiteSpace = 'pre-wrap'; // Giữ nguyên định dạng xuống dòng
    
    // Nếu có postId và là thông báo về bài viết mới, thêm style để hiển thị có thể click
    if (postId && title === 'Bài viết mới được đăng') {
        messageDiv.style.cursor = 'pointer';
        messageDiv.style.textDecoration = 'underline';
        messageDiv.title = 'Nhấp để xem bài viết';
    } else {
        messageDiv.style.cursor = 'default';
        messageDiv.style.textDecoration = 'none';
        messageDiv.title = '';
    }
    
    document.getElementById('modalSenderName').textContent = senderName;
    document.getElementById('modalSenderEmail').textContent = senderEmail;
    document.getElementById('modalCreatedAt').textContent = createdAt;
    document.getElementById('modalCreatedAtDiff').textContent = createdAtDiff;
    
    // Set sender avatar
    const avatar = document.getElementById('modalSenderAvatar');
    avatar.innerHTML = ''; // Clear previous content
    
    if (avatarUrl && avatarUrl.trim() !== '' && !avatarUrl.includes('avatar.png')) {
        const img = document.createElement('img');
        img.src = avatarUrl;
        img.alt = senderName;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '50%';
        img.onerror = function() {
            // Fallback to initial if image fails to load
            avatar.innerHTML = senderName ? senderName.charAt(0).toUpperCase() : '?';
        };
        avatar.appendChild(img);
    } else {
        avatar.textContent = senderName ? senderName.charAt(0).toUpperCase() : '?';
    }
    
    // Set mark read form
    const markReadForm = document.getElementById('markReadForm');
    const markReadButton = document.getElementById('markReadButton');
    
    if (!isRead) {
        markReadForm.action = '{{ route("admin.notifications.mark-read", ":id") }}'.replace(':id', id);
        markReadButton.style.display = 'inline-block';
    } else {
        markReadButton.style.display = 'none';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
    modal.show();
    
    // Auto mark as read when modal is shown (optional - you can remove this if you want manual marking)
    // if (!isRead) {
    //     markReadForm.submit();
    // }
}

// Form sẽ submit bình thường, không cần xử lý đặc biệt

// Xử lý click vào nội dung để chuyển đến trang bài viết
function handleMessageClick() {
    if (currentPostId && currentPostId !== null) {
        window.open('{{ url("/student/posts") }}/' + currentPostId, '_blank');
    }
}
</script>
@endpush
@endsection

