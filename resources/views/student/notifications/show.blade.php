@extends('layouts.student')

@section('title', 'Chi tiết thông báo - UniClubs')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-bell text-teal"></i> Chi tiết thông báo
                    </h2>
                </div>
                <a href="{{ route('student.notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        // Xác định icon và màu sắc dựa trên tiêu đề
                        $icon = 'fa-info-circle';
                        $textColor = 'text-primary';
                        $bgColor = 'bg-primary';
                        if (str_contains(strtolower($notificationModel->title), 'duyệt') || str_contains(strtolower($notificationModel->title), 'thành công')) {
                            $icon = 'fa-check-circle';
                            $textColor = 'text-success';
                            $bgColor = 'bg-success';
                        } elseif (str_contains(strtolower($notificationModel->title), 'từ chối') || str_contains(strtolower($notificationModel->title), 'thất bại')) {
                            $icon = 'fa-times-circle';
                            $textColor = 'text-danger';
                            $bgColor = 'bg-danger';
                        } elseif (str_contains(strtolower($notificationModel->title), 'clb') || str_contains(strtolower($notificationModel->title), 'câu lạc bộ')) {
                            $icon = 'fa-users';
                            $textColor = 'text-info';
                            $bgColor = 'bg-info';
                        } elseif (str_contains(strtolower($notificationModel->title), 'sự kiện') || str_contains(strtolower($notificationModel->title), 'event')) {
                            $icon = 'fa-calendar';
                            $textColor = 'text-warning';
                            $bgColor = 'bg-warning';
                        }
                    @endphp
                    
                    <div class="text-center mb-4">
                        <div class="d-inline-block p-4 rounded-circle {{ $bgColor }} text-white mb-3" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas {{ $icon }}" style="font-size: 2.5rem;"></i>
                        </div>
                        <h3 class="{{ $textColor }} mb-2">{{ $notificationModel->title }}</h3>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> {{ $notificationModel->created_at->format('d/m/Y H:i:s') }}
                            <span class="mx-2">•</span>
                            {{ $notificationModel->created_at->diffForHumans() }}
                        </small>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-align-left me-2"></i>Nội dung:
                        </h6>
                        <div class="p-4 bg-light rounded" style="border-left: 4px solid #14b8a6;">
                            @if(isset($relatedPost) && $relatedPost && $relatedPost->type === 'announcement')
                                {{-- Hiển thị nội dung đầy đủ của announcement --}}
                                <h5 class="mb-3 fw-bold">{{ $relatedPost->title }}</h5>
                                <div class="mb-3">
                                    {!! $relatedPost->content !!}
                                </div>
                                @if($relatedPost->image)
                                    <div class="mb-3">
                                        <img src="{{ asset($relatedPost->image) }}" alt="{{ $relatedPost->title }}" class="img-fluid rounded">
                                    </div>
                                @endif
                                <hr>
                                <div class="d-flex align-items-center gap-3 text-muted small">
                                    <span>
                                        <i class="fas fa-user me-1"></i> {{ $relatedPost->user->name ?? 'Ban chủ nhiệm' }}
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar me-1"></i> {{ $relatedPost->created_at->format('d/m/Y H:i') }}
                                    </span>
                                    @if($relatedPost->club)
                                        <span>
                                            <i class="fas fa-users me-1"></i> {{ $relatedPost->club->name }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                {{-- Hiển thị message thông thường --}}
                                <p class="mb-0" style="white-space: pre-wrap; line-height: 1.8;">{{ $notificationModel->message }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('student.notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

