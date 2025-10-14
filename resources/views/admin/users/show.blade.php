@extends('admin.layouts.app')

@section('title', 'Chi tiết người dùng')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-user"></i> Chi tiết người dùng</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Quản lý người dùng</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
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
                            @php
                                $clubs = \App\Models\Club::all();
                                $clubRoles = [];
                                foreach($clubs as $club) {
                                    $position = $user->getPositionInClub($club->id);
                                    if($position) {
                                        $clubRoles[] = [
                                            'club' => $club,
                                            'position' => $position
                                        ];
                                    }
                                }
                            @endphp
                            
                            @if(count($clubRoles) > 0)
                                @foreach($clubRoles as $clubRole)
                                    <div class="mb-2">
                                        <strong>{{ $clubRole['club']->name }}:</strong><br>
                                        <span class="badge bg-info">{{ ucfirst($clubRole['position']) }}</span>
                                    </div>
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

            <!-- Thống kê hoạt động -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê hoạt động</h5>
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
                                        <h3 class="text-warning">{{ count($clubRoles) }}</h3>
                                        <small class="text-muted">Câu lạc bộ</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Các bài viết gần đây -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-newspaper"></i> Bài viết gần đây</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $recentPosts = \App\Models\Post::where('user_id', $user->id)
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
                                            <p class="mb-1">{{ substr($post->content, 0, 100) }}...</p>
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
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
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

