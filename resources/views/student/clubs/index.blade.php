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
                @if(isset($search) && !empty($search))
                    <span class="text-muted small">{{ $myClubs->count() }} kết quả</span>
                @endif
            </div>
            
            @if($myClubs->count() > 0)
                <div class="row">
                    @foreach($myClubs as $club)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm club-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    @php
                                        $logoUrl = null;
                                        $hasLogo = false;
                                        if ($club->logo) {
                                            $logoPath = $club->logo;
                                            if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
                                                $logoUrl = $logoPath;
                                                $hasLogo = true;
                                            } else {
                                                $fullPath = public_path($logoPath);
                                                if (file_exists($fullPath)) {
                                                    $logoUrl = asset($logoPath);
                                                    $hasLogo = true;
                                                }
                                            }
                                        }
                                    @endphp
                                    <div class="club-logo me-3">
                                        @if($hasLogo && $logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $club->name }}" class="club-logo-img" 
                                                 onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <span class="club-logo-fallback" style="display: none;">{{ substr($club->name, 0, 2) }}</span>
                                        @else
                                            <span class="club-logo-fallback">{{ substr($club->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1 fw-bold">{{ $club->name }}</h5>
                                        <small class="text-muted d-flex align-items-center">
                                            <i class="fas fa-user-friends me-1"></i> {{ $club->members_count ?? $club->members->count() }} thành viên
                                        </small>
                                    </div>
                                </div>
                                <p class="card-text text-muted mb-3">{{ Str::limit(strip_tags(html_entity_decode($club->description ?? '', ENT_QUOTES, 'UTF-8')), 100) }}</p>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-teal rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Đã tham gia
                                    </span>
                                    <a href="{{ route('student.clubs.show', $club->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <i class="fas fa-compass text-info me-2"></i> Khám phá CLB khác
                @endif
            </h4>
                @if(isset($otherClubs) && $otherClubs->count() > 0)
                    <span class="badge bg-primary">
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
            titleContainer.innerHTML = `<i class="fas fa-compass text-info me-2"></i> Khám phá CLB khác`;
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
