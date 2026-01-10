@extends('admin.layouts.app')

@section('title', 'Chi tiết người dùng')

@section('content')
@php
    // Sử dụng relationship đã được eager load từ controller
    // Chỉ lấy những clubMember có status approved/active và có club tồn tại
    $userClubs = collect($user->clubMembers ?? [])
        ->filter(function($clubMember) {
            // Lọc bỏ những clubMember không có status approved/active hoặc có club null
            return in_array($clubMember->status ?? '', ['approved', 'active']) 
                && $clubMember->club !== null;
        });
@endphp

<div class="container-fluid">
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-user"></i> Chi tiết người dùng</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Quản lý người dùng</a></li>
                        <li class="breadcrumb-item active">Chi tiết</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Thông tin cơ bản -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin cá nhân</h5>
                </div>
                <div class="card-body text-center">
                    <div class="user-avatar-large mb-3">
                        @if($user->avatar && file_exists(public_path($user->avatar)))
                            <img src="{{ asset($user->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2rem; margin: 0 auto;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    <div class="row text-start mt-3">
                        <div class="col-6">
                            <strong>Mã sinh viên:</strong><br>
                            <span class="badge bg-success">{{ $user->student_id ?? 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <strong>Số điện thoại:</strong><br>
                            {{ $user->phone ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <strong>Địa chỉ:</strong><br>
                        <span class="text-muted">{{ $user->address ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="mt-3">
                        <strong>Ngày tạo:</strong><br>
                        <span class="text-muted">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                    </div>
                    
                    <div class="mt-3">
                        <strong>Cập nhật lần cuối:</strong><br>
                        <span class="text-muted">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                    </div>
                    
                    <div class="mt-3">
                        <strong>Lần cuối online:</strong><br>
                        @if($user->last_online)
                            @php
                                $diffInMinutes = $user->last_online->diffInMinutes(now());
                                $statusClass = $diffInMinutes < 5 ? 'success' : ($diffInMinutes < 60 ? 'warning' : 'secondary');
                                $statusText = $diffInMinutes < 5 ? 'Đang online' : ($diffInMinutes < 60 ? $user->last_online->diffForHumans() . ' trước' : $user->last_online->format('d/m/Y H:i'));
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                        @else
                            <span class="text-muted">Chưa từng online</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin vai trò và quyền -->
        <div class="col-md-8">
            <div class="row">
                <!-- Vai trò trong hệ thống -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-crown"></i> Vai trò hệ thống</h5>
                        </div>
                        <div class="card-body">
                            @if($user->is_admin)
                                <div class="alert alert-danger">
                                    <i class="fas fa-shield-alt"></i>
                                    <strong>Administrator</strong><br>
                                    <small>Có toàn quyền quản trị hệ thống</small>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-user"></i>
                                    <strong>Người dùng thường</strong><br>
                                    <small>Quyền hạn cơ bản</small>
                                </div>
                            @endif
                            
                            <div class="mt-3">
                                <strong>Trạng thái:</strong><br>
                                <span class="badge bg-success">Đang hoạt động</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vai trò trong câu lạc bộ -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Vai trò câu lạc bộ</h5>
                        </div>
                        <div class="card-body">
                            @if($userClubs->count() > 0)
                                @foreach($userClubs as $clubMember)
                                    @php
                                        $position = $clubMember->position ?? $clubMember->role_in_club;
                                        $badgeColor = 'secondary';
                                        $positionLabel = 'Member';
                                        
                                        if($position === 'leader' || $position === 'chunhiem') {
                                            $badgeColor = 'danger';
                                            $positionLabel = 'Trưởng CLB';
                                        } elseif($position === 'vice_president' || $position === 'phonhiem') {
                                            $badgeColor = 'warning';
                                            $positionLabel = 'Phó CLB';
                                        } elseif($position === 'officer' || $position === 'can_su') {
                                            $badgeColor = 'info';
                                            $positionLabel = 'Cán sự';
                                        } elseif($position === 'member' || $position === 'thanhvien') {
                                            $badgeColor = 'success';
                                            $positionLabel = 'Thành viên';
                                        }
                                    @endphp
                                    @if($clubMember->club)
                                    <div class="mb-2">
                                            <strong>{{ $clubMember->club->name ?? 'CLB không tồn tại' }}:</strong><br>
                                        <span class="badge bg-{{ $badgeColor }}">{{ $positionLabel }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Chưa tham gia câu lạc bộ nào
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê hoạt động cơ bản -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê hoạt động cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @php
                                    $postsCount = \App\Models\Post::where('user_id', $user->id)->count();
                                    $commentsCount = \App\Models\PostComment::where('user_id', $user->id)->count() + 
                                                   \App\Models\EventComment::where('user_id', $user->id)->count();
                                    $eventsCount = \App\Models\EventRegistration::where('user_id', $user->id)->count();
                                @endphp
                                
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-primary">{{ $postsCount }}</h3>
                                        <small class="text-muted">Bài viết</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-success">{{ $commentsCount }}</h3>
                                        <small class="text-muted">Bình luận</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-info">{{ $eventsCount }}</h3>
                                        <small class="text-muted">Sự kiện tham gia</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-warning">{{ $userClubs->count() ?? 0 }}</h3>
                                        <small class="text-muted">Câu lạc bộ</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê nâng cao -->
            @include('admin.users.partials.advanced-stats')

            <!-- Các bài viết gần đây -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-newspaper"></i> Bài viết gần đây</h5>
                        </div>
                        <div class="card-body">
                            @php
                                // Lấy tất cả bài viết từ các CLB mà user tham gia (chỉ lấy type = 'post', không bao gồm bài viết đã xóa)
                                $clubIds = $userClubs->pluck('club_id')->toArray();
                                $recentPosts = \App\Models\Post::whereIn('club_id', $clubIds)
                                    ->where('type', 'post') // Chỉ lấy bài viết, không lấy announcement hay document
                                    ->whereNull('deleted_at') // Loại bỏ bài viết đã bị soft delete
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            
                            @if($recentPosts->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentPosts as $post)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">{{ $post->title }}</h6>
                                                <small>{{ $post->created_at ? $post->created_at->format('d/m/Y') : 'N/A' }}</small>
                                            </div>
                                            <p class="mb-1">@php
                                                $cleanContent = html_entity_decode(strip_tags($post->content), ENT_QUOTES, 'UTF-8');
                                                $cleanContent = str_replace('&nbsp;', ' ', $cleanContent);
                                                echo mb_strlen($cleanContent) > 100 ? mb_substr($cleanContent, 0, 100) . '...' : $cleanContent;
                                            @endphp</p>
                                            <small>
                                                <span class="badge bg-{{ $post->status === 'published' ? 'success' : 'warning' }}">
                                                    {{ $post->status === 'published' ? 'Đã xuất bản' : 'Ẩn' }}
                                                </span>
                                                @if($post->club)
                                                    - {{ $post->club->name }}
                                                @endif
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted text-center py-3">
                                    <i class="fas fa-newspaper"></i>
                                    Chưa có bài viết nào
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal">
                            <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                        </button>
                        <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn reset mật khẩu về \"password\"?')">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Reset mật khẩu
                            </button>
                        </form>
                        @if(!$user->is_admin)
                            <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Xóa tài khoản
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa thông tin người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ảnh đại diện</label>
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                @if($user->avatar && file_exists(public_path($user->avatar)))
                                    <img src="{{ asset($user->avatar) }}" alt="Avatar hiện tại" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" class="form-control mb-2" name="avatar" accept="image/*">
                                @if($user->avatar && file_exists(public_path($user->avatar)))
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="removeAvatar">
                                        <label class="form-check-label text-danger" for="removeAvatar">
                                            <i class="fas fa-trash"></i> Xóa ảnh đại diện hiện tại
                                        </label>
                                    </div>
                                @endif
                                <small class="text-muted d-block mt-1">Chọn ảnh mới để thay đổi ảnh đại diện</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên</label>
                        <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã sinh viên</label>
                        <input type="text" class="form-control" name="student_id" value="{{ $user->student_id }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" name="phone" value="{{ $user->phone }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" name="address" rows="3">{{ $user->address }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

