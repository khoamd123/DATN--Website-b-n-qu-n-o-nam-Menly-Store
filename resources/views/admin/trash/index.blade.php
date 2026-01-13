@extends('admin.layouts.app')

@section('title', 'Quản lý Thùng rác - CLB Admin')

@section('content')
<style>
    .table th[data-column="action"],
    .table td:last-child {
        min-width: 100px;
        text-align: center;
        white-space: nowrap;
    }
    .table .d-flex.gap-2 {
        display: flex !important;
        gap: 0.5rem;
        justify-content: center;
    }
    .table .d-flex.gap-2 > .btn {
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<div class="content-header">
    <h1>🗑️ Quản lý Thùng rác</h1>
    <p class="text-muted">Khôi phục hoặc xóa vĩnh viễn dữ liệu đã bị xóa</p>
</div>

<!-- Thống kê thùng rác -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-danger">{{ \App\Models\User::onlyTrashed()->count() }}</h5>
                <p class="card-text">Người dùng</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">{{ \App\Models\Club::onlyTrashed()->count() }}</h5>
                <p class="card-text">Câu lạc bộ</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">{{ \App\Models\Post::onlyTrashed()->count() }}</h5>
                <p class="card-text">Bài viết</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">{{ \App\Models\ClubMember::onlyTrashed()->count() }}</h5>
                <p class="card-text">Thành viên</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-primary">{{ \App\Models\PostComment::onlyTrashed()->count() }}</h5>
                <p class="card-text">Bình luận</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-secondary">{{ \App\Models\ClubResource::onlyTrashed()->count() }}</h5>
                <p class="card-text">Tài nguyên CLB</p>
            </div>
        </div>
    </div>
</div>

<!-- Tổng cộng -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-dark">{{ \App\Models\User::onlyTrashed()->count() + \App\Models\Club::onlyTrashed()->count() + \App\Models\Post::onlyTrashed()->count() + \App\Models\ClubMember::onlyTrashed()->count() + \App\Models\PostComment::onlyTrashed()->count() + \App\Models\ClubResource::onlyTrashed()->count() }}</h3>
                <p class="card-text h5">Tổng cộng</p>
            </div>
        </div>
    </div>
</div>

<!-- Bộ lọc loại dữ liệu -->
<div class="card mb-4">
    <div class="card-body">
        <div class="btn-group mb-3" role="group">
            <a href="{{ route('admin.trash', ['type' => 'all']) }}" 
               class="btn {{ $type === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-list"></i> Tất cả
            </a>
            <a href="{{ route('admin.trash', ['type' => 'users']) }}" 
               class="btn {{ $type === 'users' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-users"></i> Người dùng
            </a>
            <a href="{{ route('admin.trash', ['type' => 'clubs']) }}" 
               class="btn {{ $type === 'clubs' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-building"></i> Câu lạc bộ
            </a>
            <a href="{{ route('admin.trash', ['type' => 'posts']) }}" 
               class="btn {{ $type === 'posts' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-newspaper"></i> Bài viết
            </a>
            <a href="{{ route('admin.trash', ['type' => 'club-members']) }}" 
               class="btn {{ $type === 'club-members' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-user-friends"></i> Thành viên
            </a>
            <a href="{{ route('admin.trash', ['type' => 'comments']) }}" 
               class="btn {{ $type === 'comments' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-comments"></i> Bình luận
            </a>
            <a href="{{ route('admin.trash', ['type' => 'club-resources']) }}" 
               class="btn {{ $type === 'club-resources' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-folder-open"></i> Tài nguyên CLB
            </a>
        </div>
        
        <!-- Form tìm kiếm và lọc -->
        <form method="GET" action="{{ route('admin.trash') }}" class="mt-3">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tìm kiếm..." 
                               value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Sắp xếp</label>
                    <select name="sort" class="form-select">
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                    @if($search || $dateFrom || $dateTo)
                        <a href="{{ route('admin.trash', ['type' => $type]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách dữ liệu đã xóa -->
@if($type === 'all')
    <!-- Hiển thị tất cả -->
    @foreach(['users' => 'Người dùng', 'clubs' => 'Câu lạc bộ', 'posts' => 'Bài viết', 'clubMembers' => 'Thành viên CLB', 'comments' => 'Bình luận', 'clubResources' => 'Tài nguyên CLB'] as $key => $title)
        @if($data[$key]->count() > 0)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }} ({{ $data[$key]->count() }})</h5>
                    @if($data[$key]->count() > 5)
                        <a href="{{ route('admin.trash', ['type' => \Str::kebab($key)]) }}" class="btn btn-sm btn-outline-primary">
                            Xem tất cả
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($key === 'posts')
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-success btn-sm me-2" onclick="restoreAll('posts')">
                                <i class="fas fa-undo"></i> Khôi phục tất cả bài viết
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="forceDeleteAll('posts')">
                                <i class="fas fa-trash"></i> Xóa vĩnh viễn tất cả bài viết
                            </button>
                        </div>
                    @endif
                    @include('admin.trash.partials.' . \Str::kebab($key), ['items' => $data[$key]])
                </div>
            </div>
        @endif
    @endforeach
@else
    <!-- Hiển thị theo loại -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @switch($type)
                    @case('users') Người dùng đã xóa @break
                    @case('clubs') Câu lạc bộ đã xóa @break
                    @case('posts') Bài viết đã xóa @break
                    @case('club-members') Thành viên CLB đã xóa @break
                    @case('comments') Bình luận đã xóa @break
                    @case('club-resources') Tài nguyên CLB đã xóa @break
                @endswitch
            </h5>
            <div>
                <button class="btn btn-success btn-sm" onclick="restoreAll('{{ $type }}')">
                    <i class="fas fa-undo"></i> Khôi phục tất cả
                </button>
                <button class="btn btn-danger btn-sm" onclick="forceDeleteAll('{{ $type }}')">
                    <i class="fas fa-trash"></i> Xóa vĩnh viễn tất cả
                </button>
            </div>
        </div>
        <div class="card-body">
            @php
                // Convert type to data key: 'club-members' -> 'clubMembers'
                $dataKey = \Str::camel($type);
            @endphp
            @include('admin.trash.partials.' . $type, ['items' => $data[$dataKey]])
        </div>
    </div>
@endif

<!-- Modal xác nhận -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAction = null;
let currentParams = null;

function restore(type, id) {
    showConfirm('Bạn có chắc chắn muốn khôi phục item này?', function() {
        performAction('restore', {type: type, id: id});
    });
}

function forceDelete(type, id) {
    showConfirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN item này? Hành động này không thể hoàn tác!', function() {
        performAction('forceDelete', {type: type, id: id});
    });
}

function restoreAll(type) {
    showConfirm('Bạn có chắc chắn muốn khôi phục TẤT CẢ items loại ' + type + '?', function() {
        performAction('restoreAll', {type: type});
    });
}

function forceDeleteAll(type) {
    showConfirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN TẤT CẢ items loại ' + type + '? Hành động này không thể hoàn tác!', function() {
        performAction('forceDeleteAll', {type: type});
    });
}

function showConfirm(message, callback) {
    document.getElementById('confirmMessage').textContent = message;
    currentAction = callback;
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}

function performAction(action, params) {
    let url = '';
    switch(action) {
        case 'restore':
            url = '{{ route("admin.trash.restore") }}';
            break;
        case 'forceDelete':
            url = '{{ route("admin.trash.force-delete") }}';
            break;
        case 'restoreAll':
            url = '{{ route("admin.trash.restore-all") }}';
            break;
        case 'forceDeleteAll':
            url = '{{ route("admin.trash.force-delete-all") }}';
            break;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(params)
    })
    .then(response => {
        // Kiểm tra status code
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        // Kiểm tra content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // Nếu không phải JSON, có thể là redirect hoặc HTML error
            // Nhưng restore có thể đã thành công, nên reload trang
            location.reload();
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            alert(data.message);
            location.reload();
        } else if (data) {
            alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra'));
        } else {
            // Nếu không có data, có thể đã thành công nhưng response không phải JSON
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Có thể restore đã thành công nhưng có lỗi trong response
        // Hỏi người dùng có muốn reload không
        if (confirm('Có thể đã khôi phục thành công. Bạn có muốn tải lại trang không?')) {
            location.reload();
        }
    });
}

document.getElementById('confirmButton').addEventListener('click', function() {
    if (currentAction) {
        currentAction();
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    }
});

// Khởi tạo tooltip Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
