@extends('layouts.student')

@section('title', 'Câu lạc bộ - UniClubs')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-users text-teal"></i> Câu lạc bộ
                    </h2>
                    <p class="text-muted mb-0">Khám phá, tìm kiếm và tham gia các câu lạc bộ thú vị.</p>
                </div>
            </div>
            <!-- Search Form -->
            <form action="{{ route('student.clubs.index') }}" method="GET" id="search-form">
                <div class="input-group input-group-lg" id="search-input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Nhập tên câu lạc bộ..." value="{{ $search ?? '' }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search me-1"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>

        <!-- Other Clubs / Search Results -->
        <div class="content-card" id="search-results-section">
            <h4 class="mb-3" id="search-results-title">
                @if(isset($search) && !empty($search))
                    <i class="fas fa-search text-primary me-2"></i> Kết quả tìm kiếm
                @else
                    <i class="fas fa-compass text-info me-2"></i> Khám phá CLB khác
                @endif
            </h4>
            
            <div id="search-results-container">
                @include('student.clubs._other_clubs_list', ['otherClubs' => $otherClubs, 'search' => $search])
            </div>
        </div>

        <!-- My Clubs -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-star text-warning me-2"></i> CLB của tôi
            </h4>
            
            @if($myClubs->count() > 0)
                <div class="row">
                    @foreach($myClubs as $club)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm club-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="club-logo me-3">
                                        {{ substr($club->name, 0, 2) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1 fw-bold">{{ $club->name }}</h5>
                                        <small class="text-muted d-flex align-items-center">
                                            <i class="fas fa-user-friends me-1"></i> {{ $club->members_count ?? $club->members->count() }} thành viên
                                        </small>
                                    </div>
                                </div>
                                <p class="card-text text-muted mb-3">{{ Str::limit(strip_tags($club->description ?? ''), 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-teal rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> Đã tham gia
                                    </span>
                                    <a href="{{ route('student.clubs.show', $club->id) }}" class="btn btn-outline-primary btn-sm rounded-pill">
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
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Tìm kiếm CLB
                    </a>
                </div>
            @endif
        </div>

    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="sidebar">
            <h5 class="sidebar-title">
                <i class="fas fa-chart-bar"></i> Thống kê
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="fw-bold">{{ $user->clubs->count() }}</div>
                    <small class="text-muted">CLB đã tham gia</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Sự kiện đã tham gia</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <div class="fw-bold">0</div>
                    <small class="text-muted">Giải thưởng</small>
                </div>
            </div>
        </div>

        <div class="sidebar mt-4">
            <h5 class="sidebar-title">
                <i class="fas fa-bell"></i> Thông báo
            </h5>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <div class="fw-bold">Chào mừng!</div>
                    <small class="text-muted">Bạn đã tham gia UniClubs</small>
                </div>
            </div>
            <div class="sidebar-item">
                <div class="sidebar-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="fw-bold">Sự kiện mới</div>
                    <small class="text-muted">Workshop "Lập trình Web"</small>
                </div>
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
        if (query) {
            resultsTitle.innerHTML = `<i class="fas fa-search text-primary me-2"></i> Kết quả tìm kiếm cho "${query}"`;
        } else {
            resultsTitle.innerHTML = `<i class="fas fa-compass text-info me-2"></i> Khám phá CLB khác`;
        }

        // Gửi yêu cầu AJAX đến route chuyên dụng, chỉ lấy phần HTML cần thiết
        fetch(ajaxUrl)
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching search results:', error);
                resultsContainer.innerHTML = `<div class="text-center py-5 text-danger">Đã có lỗi xảy ra khi tìm kiếm. Vui lòng tải lại trang và thử lại.</div>`;
            });
    }

    // 3. Xử lý khi người dùng click vào link phân trang AJAX
    document.body.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('#pagination-links a');
        if (paginationLink) {
            e.preventDefault();
            const url = paginationLink.href;

            // Hiển thị loading và fetch nội dung trang mới từ link phân trang
            resultsContainer.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                });
        }
    });
});
</script>
@endpush
@endsection
