@extends('layouts.student')

@section('title', 'Câu lạc bộ - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-12">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-users text-teal"></i> Câu lạc bộ
                    </h2>
                    <p class="text-muted mb-0">Khám phá, tìm kiếm và tham gia các câu lạc bộ thú vị.</p>
                </div>
                @if(!isset($isLeader) || !$isLeader)
                    <a href="{{ route('student.clubs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Tạo CLB mới
                    </a>
                @endif
            </div>
        </div>

        @if(session('error'))
            <div class="content-card">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="content-card">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- My Clubs -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="fas fa-star text-warning me-2"></i> CLB của tôi
                </h4>
                @if($myClubs->count() > 0)
                    <span class="badge bg-warning">
                        <i class="fas fa-users me-1"></i> {{ $myClubs->count() }} CLB
                    </span>
                @endif
            </div>
            
            @if($myClubs->count() > 0)
                <div class="row">
                    @foreach($myClubs as $club)
                        @php
                            $isOwner = $club->owner_id == $user->id;
                            $isLeader = $club->leader_id == $user->id;
                            $roleText = $isOwner ? 'Chủ nhiệm' : ($isLeader ? 'Trưởng CLB' : 'Quản lý');
                            $badge = '<span class="badge bg-warning rounded-pill"><i class="fas fa-crown me-1"></i> ' . $roleText . '</span>';
                            $button = '<a href="' . route('student.clubs.show', $club->id) . '" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i> Xem chi tiết</a>';
                        @endphp
                        @include('student.clubs._club_card', ['club' => $club, 'badge' => $badge, 'button' => $button])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Bạn chưa có câu lạc bộ nào</h5>
                    <p class="text-muted">Tạo CLB mới hoặc tham gia các CLB khác để bắt đầu!</p>
                </div>
            @endif
        </div>

        <!-- Joined Clubs -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="fas fa-check-circle text-success me-2"></i> CLB đã tham gia
                </h4>
                @if(isset($joinedClubs) && $joinedClubs->count() > 0)
                    <span class="badge bg-success">
                        <i class="fas fa-users me-1"></i> {{ $joinedClubs->count() }} CLB
                    </span>
                @endif
            </div>
            
            @if(isset($joinedClubs) && $joinedClubs->count() > 0)
                <div class="row">
                    @foreach($joinedClubs as $club)
                        @php
                            $badge = '<span class="badge bg-success rounded-pill"><i class="fas fa-check-circle me-1"></i> Đã tham gia</span>';
                            $button = '<a href="' . route('student.clubs.show', $club->id) . '" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i> Xem chi tiết</a>';
                        @endphp
                        @include('student.clubs._club_card', ['club' => $club, 'badge' => $badge, 'button' => $button])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Bạn chưa tham gia câu lạc bộ nào</h5>
                    <p class="text-muted">Hãy khám phá và tham gia các câu lạc bộ thú vị ở bên dưới!</p>
                </div>
            @endif
        </div>

        <!-- Other Clubs / Search Results -->
        <div class="content-card" id="search-results-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0" id="search-results-title">
                @if(isset($search) && !empty($search))
                    <i class="fas fa-search text-primary me-2"></i> Kết quả tìm kiếm
                @else
                    <i class="fas fa-compass text-info me-2"></i> CLB khác
                @endif
            </h4>
                @if(isset($otherClubs) && $otherClubs->count() > 0)
                    <span class="badge bg-info">
                        <i class="fas fa-users me-1"></i> {{ $otherClubs->count() }} CLB
                    </span>
                @endif
            </div>
            
            <!-- Search Form -->
            <form action="{{ route('student.clubs.index') }}" method="GET" id="search-form" class="mb-4">
                <div class="input-group input-group-lg" id="search-input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Nhập tên câu lạc bộ để tìm kiếm..." value="{{ $search ?? '' }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search me-1"></i> Tìm kiếm
                    </button>
                </div>
            </form>

            <div id="search-results-container">
                @include('student.clubs._other_clubs_list', ['otherClubs' => $otherClubs, 'search' => $search])
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .club-logo {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.3rem;
        box-shadow: 0 4px 6px rgba(20, 184, 166, 0.2);
        flex-shrink: 0;
        overflow: hidden;
        position: relative;
    }
    
    .club-logo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 16px;
    }
    
    .club-logo-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    
    .club-card {
        transition: all 0.3s ease;
        border-radius: 16px;
        overflow: hidden;
    }
    
    .club-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }
    
    .club-card .card-body {
        padding: 1.5rem;
    }
    
    .text-teal {
        color: #14b8a6 !important;
    }
    
    .bg-teal {
        background-color: #14b8a6 !important;
    }
    
    /* Search input improvements */
    #search-input-group .form-control {
        border-left: none;
        padding-left: 0;
    }
    
    #search-input-group .input-group-text {
        border-right: none;
    }
    
    #search-input-group .form-control:focus {
        border-color: #14b8a6;
        box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.25);
    }
    
    /* Sidebar improvements */
    .sidebar {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    
    .sidebar-title {
        color: #1e293b;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .sidebar-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #e2e8f0;
        transition: background 0.2s ease;
    }
    
    .sidebar-item:last-child {
        border-bottom: none;
    }
    
    .sidebar-item:hover {
        background: rgba(20, 184, 166, 0.05);
        border-radius: 8px;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .sidebar-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('input[name="search"]'); // Input tìm kiếm
    const resultsContainer = document.getElementById('search-results-container'); // Vùng chứa kết quả
    const resultsTitle = document.getElementById('search-results-title'); // Tiêu đề kết quả
    const searchForm = document.getElementById('search-form'); // Form tìm kiếm
    let debounceTimer;

    // 1. Ngăn form submit theo cách truyền thống khi nhấn Enter
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch(searchInput.value.trim());
    });

    // 2. Tự động tìm kiếm khi người dùng gõ
    searchInput.addEventListener('keyup', function () {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        debounceTimer = setTimeout(() => {
            performSearch(query);
        }, 350); // Đợi 350ms sau khi người dùng ngừng gõ
    });

    function performSearch(query) {
        // Tạo URL cho AJAX request, không phải URL của cả trang
        const ajaxUrl = new URL("{{ route('student.clubs.ajax_search') }}");
        ajaxUrl.searchParams.set('search', query);

        // Cập nhật URL trên thanh địa chỉ để người dùng có thể copy/paste
        const browserUrl = new URL(window.location);
        browserUrl.searchParams.set('search', query);
        window.history.pushState({path: browserUrl.href}, '', browserUrl.href);

        // Hiển thị loading spinner
        resultsContainer.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
        
        // Cập nhật tiêu đề
        const titleContainer = document.getElementById('search-results-title');
        if (query) {
            titleContainer.innerHTML = `<i class="fas fa-search text-primary me-2"></i> Kết quả tìm kiếm cho "${query}"`;
        } else {
            titleContainer.innerHTML = `<i class="fas fa-compass text-info me-2"></i> CLB khác`;
        }

        // Gửi yêu cầu AJAX đến route chuyên dụng, chỉ lấy phần HTML cần thiết
        fetch(ajaxUrl)
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
                // Cập nhật số lượng CLB sau khi load xong
                updateClubCount();
            })
            .catch(error => {
                console.error('Error fetching search results:', error);
                resultsContainer.innerHTML = `<div class="text-center py-5 text-danger">Đã có lỗi xảy ra khi tìm kiếm. Vui lòng tải lại trang và thử lại.</div>`;
            });
    }

    function updateClubCount() {
        // Đếm số lượng CLB trong container
        const clubCards = resultsContainer.querySelectorAll('.club-card');
        const count = clubCards.length;
        
        // Tìm hoặc tạo badge số lượng
        const titleContainer = document.getElementById('search-results-title');
        if (!titleContainer) return;
        
        const parentContainer = titleContainer.parentElement;
        let countBadge = parentContainer.querySelector('.badge.bg-primary');
        
        if (count > 0) {
            if (countBadge) {
                countBadge.innerHTML = `<i class="fas fa-users me-1"></i> ${count} CLB`;
            } else {
                const newBadge = document.createElement('span');
                newBadge.className = 'badge bg-primary';
                newBadge.innerHTML = `<i class="fas fa-users me-1"></i> ${count} CLB`;
                parentContainer.appendChild(newBadge);
            }
        } else if (countBadge) {
            countBadge.remove();
        }
    }

});
</script>
@endpush
@endsection
