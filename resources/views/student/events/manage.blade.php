@extends('layouts.student')

@section('title', 'Quản lý sự kiện - UniClubs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="{{ route('student.club-management.index') }}" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại quản lý CLB
                </a>
            </div>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-2">
                        <i class="fas fa-calendar-alt text-teal me-2"></i>Quản lý sự kiện
                    </h2>
                    <p class="text-muted mb-0">CLB: <strong>{{ $userClub->name }}</strong></p>
                </div>
                <a href="{{ route('student.events.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tạo sự kiện mới
                </a>
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-calendar-alt text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            <p class="text-muted mb-0">Tổng sự kiện</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-clock text-warning" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                            <p class="text-muted mb-0">Chờ duyệt</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                            <p class="text-muted mb-0">Đã duyệt</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-play-circle text-info" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['ongoing'] }}</h3>
                            <p class="text-muted mb-0">Đang diễn ra</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="eventTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
                        <i class="fas fa-list me-2"></i>Tất cả
                        <span class="badge bg-primary ms-2">{{ $stats['total'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="false">
                        <i class="fas fa-clock me-2"></i>Chờ duyệt
                        @if($stats['pending'] > 0)
                            <span class="badge bg-warning ms-2">{{ $stats['pending'] }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="false">
                        <i class="fas fa-check-circle me-2"></i>Đã duyệt
                        @if($stats['approved'] > 0)
                            <span class="badge bg-success ms-2">{{ $stats['approved'] }}</span>
                        @endif
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="eventTabsContent">
                <!-- All Events Tab -->
                <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                    @if($allEvents->count() > 0)
                        <div class="row">
                            @foreach($allEvents as $event)
                                @php
                                    $hasImages = $event->images && $event->images->count() > 0;
                                    $hasOldImage = !empty($event->image);
                                    $imageUrl = null;
                                    if ($hasImages) {
                                        $imageUrl = $event->images->first()->image_url;
                                    } elseif ($hasOldImage) {
                                        $imageUrl = asset('storage/' . $event->image);
                                    }
                                    
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'ongoing' => 'info',
                                        'completed' => 'primary',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Bản nháp',
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'ongoing' => 'Đang diễn ra',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $event->title }}" style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">
                                                    <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark">
                                                        {{ $event->title }}
                                                    </a>
                                                </h5>
                                                <span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }}">
                                                    {{ $statusLabels[$event->status] ?? ucfirst($event->status) }}
                                                </span>
                                            </div>
                                            
                                            <p class="card-text text-muted small mb-3">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
                                            </p>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="far fa-calendar me-1"></i>
                                                    <strong>Bắt đầu:</strong> {{ $event->start_time->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-check me-1"></i>
                                                    <strong>Kết thúc:</strong> {{ $event->end_time->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            
                                            @if($event->location)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        {{ $event->location }}
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <div class="mb-3">
                                                <span class="badge bg-{{ $event->mode === 'offline' ? 'primary' : ($event->mode === 'online' ? 'success' : 'info') }}">
                                                    <i class="fas fa-{{ $event->mode === 'offline' ? 'map-marker-alt' : ($event->mode === 'online' ? 'video' : 'users') }} me-1"></i>
                                                    {{ $event->mode === 'offline' ? 'Trực tiếp' : ($event->mode === 'online' ? 'Trực tuyến' : 'Kết hợp') }}
                                                </span>
                                                @if($event->max_participants)
                                                    <span class="badge bg-info ms-1">
                                                        <i class="fas fa-users me-1"></i>{{ $event->max_participants }} người
                                                    </span>
                                                @endif
                                                
                                                @if($event->status === 'approved' || $event->status === 'ongoing')
                                                    @php
                                                        $registrationCount = \App\Models\EventRegistration::where('event_id', $event->id)
                                                            ->whereIn('status', ['registered', 'pending', 'approved'])
                                                            ->count();
                                                    @endphp
                                                    <span class="badge bg-secondary ms-1">
                                                        <i class="fas fa-user-check me-1"></i>{{ $registrationCount }} đã đăng ký
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i> Xem chi tiết
                                                </a>
                                                <small class="text-muted align-self-center ms-auto">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $event->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">Chưa có sự kiện nào</p>
                            <a href="{{ route('student.events.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Tạo sự kiện mới
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Pending Events Tab -->
                <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                    @if($pendingEvents->count() > 0)
                        <div class="row">
                            @foreach($pendingEvents as $event)
                                <div class="col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        @php
                                            $hasImages = $event->images && $event->images->count() > 0;
                                            $hasOldImage = !empty($event->image);
                                            $imageUrl = null;
                                            if ($hasImages) {
                                                $imageUrl = $event->images->first()->image_url;
                                            } elseif ($hasOldImage) {
                                                $imageUrl = asset('storage/' . $event->image);
                                            }
                                        @endphp
                                        
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $event->title }}" style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">
                                                    <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark">
                                                        {{ $event->title }}
                                                    </a>
                                                </h5>
                                                <span class="badge bg-warning">Chờ duyệt</span>
                                            </div>
                                            
                                            <p class="card-text text-muted small mb-3">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
                                            </p>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="far fa-calendar me-1"></i>
                                                    <strong>Bắt đầu:</strong> {{ $event->start_time->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-check me-1"></i>
                                                    <strong>Kết thúc:</strong> {{ $event->end_time->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            
                                            @if($event->location)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        {{ $event->location }}
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <div class="mb-3">
                                                <span class="badge bg-{{ $event->mode === 'offline' ? 'primary' : ($event->mode === 'online' ? 'success' : 'info') }}">
                                                    <i class="fas fa-{{ $event->mode === 'offline' ? 'map-marker-alt' : ($event->mode === 'online' ? 'video' : 'users') }} me-1"></i>
                                                    {{ $event->mode === 'offline' ? 'Trực tiếp' : ($event->mode === 'online' ? 'Trực tuyến' : 'Kết hợp') }}
                                                </span>
                                                @if($event->max_participants)
                                                    <span class="badge bg-info ms-1">
                                                        <i class="fas fa-users me-1"></i>{{ $event->max_participants }} người
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i> Xem chi tiết
                                                </a>
                                                <small class="text-muted align-self-center ms-auto">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $event->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">Chưa có sự kiện nào đang chờ duyệt</p>
                            <a href="{{ route('student.events.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Tạo sự kiện mới
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Approved Events Tab -->
                <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                    @if($approvedEvents->count() > 0)
                        <div class="row">
                            @foreach($approvedEvents as $event)
                                <div class="col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        @php
                                            $hasImages = $event->images && $event->images->count() > 0;
                                            $hasOldImage = !empty($event->image);
                                            $imageUrl = null;
                                            if ($hasImages) {
                                                $imageUrl = $event->images->first()->image_url;
                                            } elseif ($hasOldImage) {
                                                $imageUrl = asset('storage/' . $event->image);
                                            }
                                        @endphp
                                        
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $event->title }}" style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">
                                                    <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none text-dark">
                                                        {{ $event->title }}
                                                    </a>
                                                </h5>
                                                <span class="badge bg-success">Đã duyệt</span>
                                            </div>
                                            
                                            <p class="card-text text-muted small mb-3">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 100) }}
                                            </p>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="far fa-calendar me-1"></i>
                                                    <strong>Bắt đầu:</strong> {{ $event->start_time->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-check me-1"></i>
                                                    <strong>Kết thúc:</strong> {{ $event->end_time->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            
                                            @if($event->location)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        {{ $event->location }}
                                                    </small>
                                                </div>
                                            @endif
                                            
                                            <div class="mb-3">
                                                <span class="badge bg-{{ $event->mode === 'offline' ? 'primary' : ($event->mode === 'online' ? 'success' : 'info') }}">
                                                    <i class="fas fa-{{ $event->mode === 'offline' ? 'map-marker-alt' : ($event->mode === 'online' ? 'video' : 'users') }} me-1"></i>
                                                    {{ $event->mode === 'offline' ? 'Trực tiếp' : ($event->mode === 'online' ? 'Trực tuyến' : 'Kết hợp') }}
                                                </span>
                                                @if($event->max_participants)
                                                    <span class="badge bg-info ms-1">
                                                        <i class="fas fa-users me-1"></i>{{ $event->max_participants }} người
                                                    </span>
                                                @endif
                                                
                                                @php
                                                    $registrationCount = \App\Models\EventRegistration::where('event_id', $event->id)
                                                        ->whereIn('status', ['registered', 'pending', 'approved'])
                                                        ->count();
                                                @endphp
                                                <span class="badge bg-secondary ms-1">
                                                    <i class="fas fa-user-check me-1"></i>{{ $registrationCount }} đã đăng ký
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i> Xem chi tiết
                                                </a>
                                                <small class="text-muted align-self-center ms-auto">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $event->start_time->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">Chưa có sự kiện nào đã được duyệt</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #14b8a6;
    }
    
    .nav-tabs .nav-link.active {
        color: #14b8a6;
        border-bottom-color: #14b8a6;
        background-color: transparent;
    }
    
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush
@endsection

