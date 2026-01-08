@extends('layouts.student')

@section('title', 'Quản lý bài viết - ' . $club->name)

@push('styles')
<style>
    .action-btn {
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        opacity: 0.9;
    }
    
    .btn-outline-primary:hover {
        background-color: #0d9488;
        border-color: #0d9488;
        color: white;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }
    
    .btn-outline-warning:hover {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }
    
    .btn-primary, .btn-outline-secondary, .btn-warning {
        border-radius: 6px;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <a href="{{ route('student.club-management.index') }}" class="text-decoration-none mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại quản lý CLB
                    </a>
                    <h4 class="mb-0"><i class="fas fa-newspaper me-2"></i>Quản lý - {{ $club->name }}</h4>
                </div>
                <div class="d-flex gap-2">
                    @if($activeTab === 'posts' || !$activeTab)
                        <a href="{{ route('student.posts.create') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;"><i class="fas fa-plus me-1"></i> Tạo bài viết</a>
                    @else
                        @if(isset($canPostAnnouncement) && $canPostAnnouncement)
                            <a href="{{ route('student.announcements.create', ['club_id' => $clubId]) }}" class="btn btn-warning" style="padding: 0.5rem 1rem; font-size: 0.9rem;"><i class="fas fa-plus me-1"></i> Tạo thông báo</a>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ ($activeTab === 'posts' || !$activeTab) ? 'active' : '' }}" 
                       href="{{ route('student.club-management.posts', ['club' => $clubId, 'tab' => 'posts']) }}">
                        <i class="fas fa-newspaper me-2"></i>Bài viết
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab === 'announcements' ? 'active' : '' }}" 
                       href="{{ route('student.club-management.posts', ['club' => $clubId, 'tab' => 'announcements']) }}">
                        <i class="fas fa-bullhorn me-2"></i>Thông báo
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Posts Tab -->
                <div class="tab-pane fade {{ ($activeTab === 'posts' || !$activeTab) ? 'show active' : '' }}" id="posts-tab" role="tabpanel">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Bộ lọc -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('student.club-management.posts', ['club' => $clubId]) }}" class="row g-3">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <div class="col-md-6">
                            <label for="search" class="form-label small">Tìm kiếm</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="Tìm theo tiêu đề...">
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label small">Trạng thái</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Tất cả</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Công khai</option>
                                <option value="members_only" {{ request('status') == 'members_only' ? 'selected' : '' }}>Chỉ thành viên</option>
                                <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-search me-1"></i> Lọc
                            </button>
                            <a href="{{ route('student.club-management.posts', ['club' => $clubId, 'tab' => $activeTab]) }}" class="btn btn-outline-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($posts->count() === 0)
                <div class="text-center py-5">
                    <i class="far fa-newspaper fa-2x text-muted mb-3"></i>
                    <p class="text-muted mb-2">CLB này chưa có bài viết nào.</p>
                    <a href="{{ route('student.posts.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tạo bài viết</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th style="width: 140px;">Ảnh đại diện</th>
                                <th>Tiêu đề</th>
                                <th>Tác giả</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th style="width: 150px; text-align: center;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $index => $post)
                                @php
                                    // Tính STT với pagination
                                    $stt = ($posts->currentPage() - 1) * $posts->perPage() + $index + 1;
                                    
                                    // Lấy ảnh đại diện
                                    $imageUrl = null;
                                    if (!empty($post->image)) {
                                        $imageField = $post->image;
                                        if (\Illuminate\Support\Str::startsWith($imageField, ['http://', 'https://'])) {
                                            $imageUrl = $imageField;
                                        } elseif (\Illuminate\Support\Str::startsWith($imageField, ['/storage/', 'storage/'])) {
                                            $imageUrl = asset(ltrim($imageField, '/'));
                                        } elseif (\Illuminate\Support\Str::startsWith($imageField, ['uploads/', '/uploads/'])) {
                                            $imageUrl = asset(ltrim($imageField, '/'));
                                        } else {
                                            $imageUrl = asset('storage/' . ltrim($imageField, '/'));
                                        }
                                    } elseif (isset($post->attachments) && $post->attachments->count() > 0) {
                                        $firstImageAttachment = $post->attachments->firstWhere('file_type', 'image') ?? $post->attachments->first();
                                        if ($firstImageAttachment && isset($firstImageAttachment->file_url)) {
                                            $fileUrl = $firstImageAttachment->file_url;
                                            if (\Illuminate\Support\Str::startsWith($fileUrl, ['http://', 'https://'])) {
                                                $imageUrl = $fileUrl;
                                            } else {
                                                $imageUrl = asset(ltrim($fileUrl, '/'));
                                            }
                                        }
                                    } elseif (!empty($post->content)) {
                                        if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $post->content, $m)) {
                                            $imgSrc = $m[1] ?? null;
                                            if ($imgSrc) {
                                                if (\Illuminate\Support\Str::startsWith($imgSrc, ['http://', 'https://'])) {
                                                    $imageUrl = $imgSrc;
                                                } else {
                                                    $imageUrl = asset(ltrim($imgSrc, '/'));
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <strong class="text-muted">{{ $stt }}</strong>
                                    </td>
                                    <td>
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" 
                                                 alt="{{ $post->title }}" 
                                                 class="img-thumbnail" 
                                                 style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                                 onclick="window.location.href='{{ route('student.posts.show', $post->id) }}'"
                                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22120%22 height=%22120%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-family=%22Arial%22 font-size=%2214%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-light rounded" 
                                                 style="width: 120px; height: 120px;">
                                                <i class="far fa-image text-muted" style="font-size: 32px;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.posts.show', $post->id) }}" class="text-decoration-none text-dark fw-bold">
                                            {{ \Illuminate\Support\Str::limit($post->title, 60) }}
                                        </a>
                                    </td>
                                    <td>{{ $post->user->name ?? 'Hệ thống' }}</td>
                                    <td>
                                        @php
                                            $colors = ['published'=>'success','members_only'=>'info','hidden'=>'secondary'];
                                            $labels = ['published'=>'Công khai','members_only'=>'Chỉ thành viên','hidden'=>'Ẩn'];
                                        @endphp
                                        <span class="badge bg-{{ $colors[$post->status] ?? 'secondary' }}">{{ $labels[$post->status] ?? $post->status }}</span>
                                    </td>
                                    <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2 align-items-center">
                                            @if($post->user_id === $user->id || $user->getPositionInClub($clubId) === 'leader')
                                                <a href="{{ route('student.posts.edit', $post->id) }}" class="btn btn-sm btn-outline-primary action-btn" title="Chỉnh sửa" style="padding: 0.4rem 0.8rem; font-size: 0.875rem; border-radius: 6px;">
                                                    <i class="fas fa-edit me-1"></i> Sửa
                                                </a>
                                                <form action="{{ route('student.posts.delete', $post->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger action-btn" title="Xóa" style="padding: 0.4rem 0.8rem; font-size: 0.875rem; border-radius: 6px;">
                                                        <i class="fas fa-trash me-1"></i> Xóa
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted" style="font-size: 0.875rem;">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @endif
                </div>

                <!-- Announcements Tab -->
                <div class="tab-pane fade {{ $activeTab === 'announcements' ? 'show active' : '' }}" id="announcements-tab" role="tabpanel">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <!-- Bộ lọc thông báo -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc thông báo</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('student.club-management.posts', ['club' => $clubId]) }}" class="row g-3">
                            <input type="hidden" name="tab" value="announcements">

                            <div class="col-md-6">
                                <label for="announcement_search" class="form-label small">Tìm kiếm</label>
                                <input type="text" class="form-control form-control-sm" id="announcement_search" name="announcement_search" value="{{ request('announcement_search') }}" placeholder="Tìm theo tiêu đề...">
                            </div>
                            <div class="col-md-4">
                                <label for="announcement_status" class="form-label small">Trạng thái</label>
                                <select class="form-select form-select-sm" id="announcement_status" name="announcement_status">
                                    <option value="all" {{ request('announcement_status') == 'all' || !request('announcement_status') ? 'selected' : '' }}>Tất cả</option>
                                    <option value="published" {{ request('announcement_status') == 'published' ? 'selected' : '' }}>Công khai</option>
                                    <option value="members_only" {{ request('announcement_status') == 'members_only' ? 'selected' : '' }}>Chỉ thành viên</option>
                                    <option value="hidden" {{ request('announcement_status') == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-warning" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                    <i class="fas fa-search me-1"></i> Lọc
                                </button>
                                <a href="{{ route('student.club-management.posts', ['club' => $clubId, 'tab' => 'announcements']) }}" class="btn btn-outline-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                @if($announcements->count() === 0)
                    <div class="text-center py-5">
                        <i class="fas fa-bullhorn fa-2x text-muted mb-3"></i>
                        <p class="text-muted mb-2">CLB này chưa có thông báo nào.</p>
                        @if(isset($canPostAnnouncement) && $canPostAnnouncement)
                            <a href="{{ route('student.announcements.create', ['club_id' => $clubId]) }}" class="btn btn-warning btn-sm"><i class="fas fa-plus me-1"></i> Tạo thông báo</a>
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Tiêu đề</th>
                                    <th>Tác giả</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th style="width: 150px; text-align: center;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($announcements as $index => $announcement)
                                    @php
                                        // Tính STT với pagination
                                        $stt = ($announcements->currentPage() - 1) * $announcements->perPage() + $index + 1;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <strong class="text-muted">{{ $stt }}</strong>
                                        </td>
                                        <td>
                                            <a href="{{ route('student.posts.show', $announcement->id) }}" class="text-decoration-none text-dark fw-bold">
                                                {{ \Illuminate\Support\Str::limit($announcement->title, 60) }}
                                            </a>
                                            <span class="badge bg-warning text-dark ms-2">Thông báo</span>
                                        </td>
                                        <td>{{ $announcement->user->name ?? 'Hệ thống' }}</td>
                                        <td>
                                            @php
                                                $colors = ['published'=>'success','members_only'=>'info','hidden'=>'secondary'];
                                                $labels = ['published'=>'Công khai','members_only'=>'Chỉ thành viên','hidden'=>'Ẩn'];
                                            @endphp
                                            <span class="badge bg-{{ $colors[$announcement->status] ?? 'secondary' }}">{{ $labels[$announcement->status] ?? $announcement->status }}</span>
                                        </td>
                                        <td>{{ $announcement->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2 align-items-center">
                                                @if($announcement->user_id === $user->id || $user->getPositionInClub($clubId) === 'leader')
                                                    <a href="{{ route('student.announcements.edit', $announcement->id) }}" class="btn btn-sm btn-outline-warning action-btn" title="Chỉnh sửa" style="padding: 0.4rem 0.8rem; font-size: 0.875rem; border-radius: 6px;">
                                                        <i class="fas fa-edit me-1"></i> Sửa
                                                    </a>
                                                    <form action="{{ route('student.posts.delete', $announcement->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger action-btn" title="Xóa" style="padding: 0.4rem 0.8rem; font-size: 0.875rem; border-radius: 6px;">
                                                            <i class="fas fa-trash me-1"></i> Xóa
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted" style="font-size: 0.875rem;">—</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $announcements->appends(request()->query())->links() }}
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
