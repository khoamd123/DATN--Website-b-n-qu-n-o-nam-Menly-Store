@extends('layouts.student')

@section('title', 'Quản lý sự kiện - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-12">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-calendar-check text-teal"></i> Quản lý sự kiện
                    </h2>
                    <p class="text-muted mb-0">Quản lý sự kiện của CLB: <strong>{{ $userClub->name }}</strong></p>
                </div>
                <div>
                    <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    <a href="{{ route('student.events.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Tạo sự kiện mới
                    </a>
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

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary mb-0">{{ $stats['total'] }}</h3>
                            <small class="text-muted">Tổng cộng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning mb-0">{{ $stats['pending'] }}</h3>
                            <small class="text-muted">Chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success mb-0">{{ $stats['approved'] }}</h3>
                            <small class="text-muted">Đã duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info mb-0">{{ $stats['ongoing'] }}</h3>
                            <small class="text-muted">Đang diễn ra</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary mb-0">{{ $stats['completed'] }}</h3>
                            <small class="text-muted">Đã hoàn thành</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger mb-0">{{ $stats['cancelled'] }}</h3>
                            <small class="text-muted">Đã hủy</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="eventTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        Tất cả ({{ $stats['total'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        Chờ duyệt ({{ $stats['pending'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                        Đã duyệt ({{ $stats['approved'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing" type="button" role="tab">
                        Đang diễn ra ({{ $stats['ongoing'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                        Đã hoàn thành ({{ $stats['completed'] }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">
                        Đã hủy ({{ $stats['cancelled'] }})
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="eventTabsContent">
                <!-- All Events -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <div class="row">
                        @forelse($allEvents as $event)
                            @include('student.events._event_card', ['event' => $event])
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>Chưa có sự kiện nào
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pending Events -->
                <div class="tab-pane fade" id="pending" role="tabpanel">
                    <div class="row">
                        @forelse($pendingEvents as $event)
                            @include('student.events._event_card', ['event' => $event])
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>Không có sự kiện nào đang chờ duyệt
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Approved Events -->
                <div class="tab-pane fade" id="approved" role="tabpanel">
                    <div class="row">
                        @forelse($approvedEvents as $event)
                            @include('student.events._event_card', ['event' => $event])
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>Không có sự kiện nào đã được duyệt
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Ongoing Events -->
                <div class="tab-pane fade" id="ongoing" role="tabpanel">
                    <div class="row">
                        @forelse($ongoingEvents as $event)
                            @include('student.events._event_card', ['event' => $event])
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>Không có sự kiện nào đang diễn ra
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Completed Events -->
                <div class="tab-pane fade" id="completed" role="tabpanel">
                    <div class="row">
                        @forelse($completedEvents as $event)
                            @include('student.events._event_card', ['event' => $event])
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>Không có sự kiện nào đã hoàn thành
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Cancelled Events -->
                <div class="tab-pane fade" id="cancelled" role="tabpanel">
                    <div class="row">
                        @forelse($cancelledEvents as $event)
                            @include('student.events._event_card', ['event' => $event])
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>Không có sự kiện nào đã bị hủy
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


