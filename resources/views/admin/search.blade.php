@extends('admin.layouts.app')

@section('title', 'Tìm kiếm - CLB Admin')

@section('content')
<div class="content-header">
    <h1>🔍 Tìm kiếm</h1>
    <p class="text-muted">Kết quả tìm kiếm cho: "<strong>{{ $query }}</strong>"</p>
</div>

@if(empty($query))
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Vui lòng nhập từ khóa tìm kiếm.
    </div>
@else
    <!-- Thống kê kết quả -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-primary">{{ $results['users']->count() }}</h5>
                    <p class="card-text">Người dùng</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-success">{{ $results['clubs']->count() }}</h5>
                    <p class="card-text">Câu lạc bộ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-info">{{ $results['posts']->count() }}</h5>
                    <p class="card-text">Bài viết</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-warning">{{ $results['events']->count() }}</h5>
                    <p class="card-text">Sự kiện</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Kết quả tìm kiếm -->
    
    <!-- Người dùng -->
    @if($results['users']->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users text-primary"></i> Người dùng ({{ $results['users']->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thông tin</th>
                                <th>Email</th>
                                <th>MSSV</th>
                                <th>Vai trò</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['users'] as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar-fixed me-2">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->student_id ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->is_admin ? 'danger' : 'success' }}">
                                            {{ $user->is_admin ? 'Admin' : 'User' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users') }}?search={{ $user->name }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Câu lạc bộ -->
    @if($results['clubs']->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building text-success"></i> Câu lạc bộ ({{ $results['clubs']->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thông tin CLB</th>
                                <th>Trưởng CLB</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['clubs'] as $club)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $club->name }}</strong>
                                            <br><small class="text-muted">{{ $club->slug }}</small>
                                            <br><small class="text-muted">{{ substr($club->description, 0, 50) }}{{ strlen($club->description) > 50 ? '...' : '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($club->owner)
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar-fixed me-2">
                                                    {{ substr($club->owner->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $club->owner->name }}</strong>
                                                    <br><small class="text-muted">{{ $club->owner->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Không xác định</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $club->status === 'active' ? 'success' : ($club->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($club->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.clubs') }}?search={{ $club->name }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Bài viết -->
    @if($results['posts']->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-newspaper text-info"></i> Bài viết ({{ $results['posts']->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tiêu đề</th>
                                <th>CLB</th>
                                <th>Tác giả</th>
                                <th>Loại</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['posts'] as $post)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $post->title }}</strong>
                                            <br><small class="text-muted">{{ $post->slug }}</small>
                                            <br><small class="text-muted">{{ substr($post->content, 0, 50) }}{{ strlen($post->content) > 50 ? '...' : '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($post->club)
                                            <span class="badge bg-info">{{ $post->club->name }}</span>
                                        @else
                                            <span class="text-muted">Không xác định</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($post->user)
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar-fixed me-2">
                                                    {{ substr($post->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $post->user->name }}</strong>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Không xác định</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($post->type) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.posts') }}?search={{ $post->title }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Sự kiện -->
    @if($results['events']->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt text-warning"></i> Sự kiện ({{ $results['events']->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tiêu đề</th>
                                <th>CLB</th>
                                <th>Người tạo</th>
                                <th>Thời gian</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results['events'] as $event)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $event->title }}</strong>
                                            <br><small class="text-muted">{{ substr($event->description, 0, 50) }}{{ strlen($event->description) > 50 ? '...' : '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($event->club)
                                            <span class="badge bg-info">{{ $event->club->name }}</span>
                                        @else
                                            <span class="text-muted">Không xác định</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($event->creator)
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar-fixed me-2">
                                                    {{ substr($event->creator->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $event->creator->name }}</strong>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Không xác định</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($event->start_time)
                                            {{ $event->start_time->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">Chưa xác định</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.plans-schedule') }}?search={{ $event->title }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Không có kết quả -->
    @if($results['users']->count() == 0 && $results['clubs']->count() == 0 && $results['posts']->count() == 0 && $results['events']->count() == 0)
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Không tìm thấy kết quả nào</h4>
            <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc kiểm tra chính tả.</p>
        </div>
    @endif
@endif
@endsection
