@extends('layouts.student') 

@section('title', 'Báo cáo & Thống kê - ' . $club->name)

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="fas fa-chart-bar text-teal"></i> Báo cáo & Thống kê
                </h2>
                <p class="text-muted mb-0">Phân tích hoạt động của CLB: <strong>{{ $club->name }}</strong></p>
            </div>
            @if($isLeaderOrOfficer ?? false)
                <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                </a>
            @else
                <a href="{{ route('student.clubs.show', $club->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                </a>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="stat-card h-100">
                <div class="stat-icon bg-primary"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-number">{{ $stats['totalMembers'] }}</div>
                    <div class="stat-label">Tổng thành viên</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card h-100">
                <div class="stat-icon bg-success"><i class="fas fa-user-plus"></i></div>
                <div class="stat-info">
                    <div class="stat-number">{{ $stats['newMembersThisMonth'] }}</div>
                    <div class="stat-label">Thành viên mới tháng này</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card h-100">
                <div class="stat-icon bg-info"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-number">{{ $stats['totalEvents'] }}</div>
                    <div class="stat-label">Tổng sự kiện</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card h-100">
                <div class="stat-icon bg-warning"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-info">
                    <div class="stat-number">{{ $stats['upcomingEvents'] }}</div>
                    <div class="stat-label">Sự kiện sắp tới</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fund Stats - Hiển thị cho tất cả thành viên (chỉ xem, không có nút tạo giao dịch) -->
    @if($canViewReports)
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><i class="fas fa-wallet text-info me-2"></i> Thống kê quỹ</h4>
        </div>
        @if($stats['fund']['balance'] > 0 || $stats['fund']['totalIncome'] > 0 || $stats['fund']['totalExpense'] > 0)
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="stat-card h-100 fund-stat-card border-success">
                        <div class="stat-icon bg-success"><i class="fas fa-arrow-down"></i></div>
                        <div class="stat-info">
                            <div class="stat-number text-success">{{ number_format($stats['fund']['totalIncome'], 0, ',', '.') }} VNĐ</div>
                            <div class="stat-label">Tổng thu</div>
                            <div class="stat-sub-label text-muted small mt-1">
                                <i class="fas fa-info-circle me-1"></i> Tổng số tiền đã thu
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-card h-100 fund-stat-card border-danger">
                        <div class="stat-icon bg-danger"><i class="fas fa-arrow-up"></i></div>
                        <div class="stat-info">
                            <div class="stat-number text-danger">{{ number_format($stats['fund']['totalExpense'], 0, ',', '.') }} VNĐ</div>
                            <div class="stat-label">Tổng chi</div>
                            <div class="stat-sub-label text-muted small mt-1">
                                <i class="fas fa-info-circle me-1"></i> Tổng số tiền đã chi
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-card h-100 fund-stat-card border-primary">
                        <div class="stat-icon bg-primary"><i class="fas fa-balance-scale"></i></div>
                        <div class="stat-info">
                            <div class="stat-number text-primary">{{ number_format($stats['fund']['balance'], 0, ',', '.') }} VNĐ</div>
                            <div class="stat-label">Số dư hiện tại</div>
                            <div class="stat-sub-label text-muted small mt-1">
                                <i class="fas fa-info-circle me-1"></i> Số tiền còn lại trong quỹ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Danh sách thu chi -->
            @if(isset($transactions) && $transactions->count() > 0)
            <div class="mt-4">
                <h5 class="mb-3"><i class="fas fa-list text-teal me-2"></i> Danh sách thu chi</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Loại</th>
                                <th>Mô tả</th>
                                <th>Số tiền</th>
                                <th>Ngày</th>
                                <th>Người tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    @if($transaction->type === 'income')
                                        <span class="badge bg-success">Thu</span>
                                    @else
                                        <span class="badge bg-danger">Chi</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $transaction->title ?? $transaction->description ?? 'Không có mô tả' }}</strong>
                                    @if($transaction->category)
                                        <br><small class="text-muted">{{ $transaction->category }}</small>
                                    @endif
                                </td>
                                <td class="fw-bold {{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'income' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', '.') }} VNĐ
                                </td>
                                <td>
                                    {{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') : $transaction->created_at->format('d/m/Y') }}
                                    <br><small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    {{ $transaction->creator->name ?? 'N/A' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Phân trang -->
                @if($transactions->hasPages())
                <div class="mt-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 p-3 bg-light rounded">
                        <div class="pagination-info d-flex align-items-center gap-2 text-muted">
                            <i class="fas fa-info-circle"></i>
                            <span>
                                Hiển thị <strong>{{ $transactions->firstItem() }}</strong> - <strong>{{ $transactions->lastItem() }}</strong> 
                                trong tổng <strong>{{ $transactions->total() }}</strong> giao dịch
                            </span>
                        </div>
                        <nav aria-label="Phân trang">
                            <ul class="pagination pagination-sm mb-0">
                                @php
                                    $queryParams = request()->except('page');
                                    $previousUrl = $transactions->onFirstPage() ? '#' : $transactions->appends($queryParams)->previousPageUrl();
                                    $nextUrl = $transactions->hasMorePages() ? $transactions->appends($queryParams)->nextPageUrl() : '#';
                                @endphp
                                {{-- Previous Page Link --}}
                                @if ($transactions->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $previousUrl }}" rel="prev">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($transactions->appends($queryParams)->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                                    @if($page == $transactions->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($transactions->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $nextUrl }}" rel="next">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
                @endif
            </div>
            @elseif(isset($transactions))
            <div class="mt-4">
                <h5 class="mb-3"><i class="fas fa-list text-teal me-2"></i> Danh sách thu chi</h5>
                <div class="text-center py-3">
                    <i class="fas fa-inbox text-muted fa-2x mb-2"></i>
                    <p class="text-muted mb-0">Chưa có giao dịch nào.</p>
                </div>
            </div>
            @endif
        @else
            <div class="text-center py-4">
                <i class="fas fa-info-circle text-muted fa-2x mb-3"></i>
                <p class="text-muted">Chưa có dữ liệu về quỹ của câu lạc bộ.</p>
            </div>
        @endif
    </div>
    @else
    <!-- Thông tin chi tiêu công khai cho thành viên -->
    <div class="content-card">
        <h4 class="mb-4"><i class="fas fa-receipt text-info me-2"></i> Danh sách chi tiêu</h4>
        @if(isset($publicExpenses) && $publicExpenses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Mục đích</th>
                            <th>Số tiền</th>
                            <th>Ngày</th>
                            <th>Người tạo</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($publicExpenses as $expense)
                        <tr>
                            <td>
                                <strong>{{ $expense->description ?? $expense->title ?? 'Không có mô tả' }}</strong>
                            </td>
                            <td class="text-danger fw-bold">
                                {{ number_format($expense->amount, 0, ',', '.') }} VNĐ
                            </td>
                            <td>
                                {{ $expense->created_at->format('d/m/Y') }}
                                <br><small class="text-muted">{{ $expense->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                {{ $expense->creator->name ?? 'N/A' }}
                            </td>
                            <td>
                                <span class="badge bg-success">Đã duyệt</span>
                                @if($expense->approved_at)
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($expense->approved_at)->format('d/m/Y') }}</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <td colspan="4" class="text-end fw-bold">Tổng chi tiêu:</td>
                            <td class="text-danger fw-bold">
                                {{ number_format($stats['fund']['totalExpense'], 0, ',', '.') }} VNĐ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                Đây là danh sách các khoản chi đã được duyệt. Thông tin số dư và chi tiết tài chính chỉ dành cho Trưởng CLB, Phó CLB và Thủ quỹ.
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-receipt text-muted fa-2x mb-3"></i>
                <p class="text-muted">Chưa có khoản chi nào được ghi nhận.</p>
            </div>
        @endif
    </div>
    @endif

    <!-- Resources Management Section -->
    @php
        $position = $user->getPositionInClub($club->id);
        $canViewResources = $position !== null; // Tất cả thành viên đều có thể xem
        $canManageResources = in_array($position, ['leader', 'vice_president']); // Chỉ leader và vice_president mới có thể quản lý
    @endphp
    @if($canViewResources)
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><i class="fas fa-folder-open text-teal me-2"></i> Tài nguyên CLB</h4>
            <a href="{{ route('student.club-management.resources', ['club' => $club->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> Xem tài nguyên
            </a>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="stat-card h-100">
                    <div class="stat-icon bg-info"><i class="fas fa-folder"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $stats['resources']['total'] ?? 0 }}</div>
                        <div class="stat-label">Tổng tài nguyên</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="stat-card h-100">
                    <div class="stat-icon bg-warning"><i class="fas fa-file"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $stats['resources']['files'] ?? 0 }}</div>
                        <div class="stat-label">Tổng file</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-info mt-3 mb-0">
            <i class="fas fa-info-circle me-2"></i>
            @if($canManageResources)
                Quản lý tài liệu, file và tài nguyên của CLB. Bạn có thể tạo, chỉnh sửa và xóa tài nguyên.
            @else
                Xem tài liệu, file và tài nguyên của CLB. Chỉ Trưởng CLB và Phó CLB mới có quyền tạo và chỉnh sửa tài nguyên.
            @endif
        </div>
    </div>
    @endif

    <!-- Charts -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="content-card h-100">
                <h4 class="mb-4">Cơ cấu thành viên</h4>
                <div class="chart-container">
                    <canvas id="memberStructureChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 mb-4">
            <div class="content-card h-100">
                <h4 class="mb-4">Sự kiện theo tháng (12 tháng qua)</h4>
                <div class="chart-container">
                    <canvas id="eventsByMonthChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="content-card h-100">
                <h4 class="mb-4">Tăng trưởng thành viên (3 tháng gần nhất)</h4>
                <div class="chart-container">
                    <canvas id="memberGrowthChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 mb-4">
            <div class="content-card h-100">
                <h4 class="mb-4">Xu hướng hoạt động (6 tháng gần nhất)</h4>
                <div class="chart-container">
                    <canvas id="activityTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>@endsection

@push('styles')
<style>
    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }
    .stat-info {
        margin-left: 1rem;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
    }
    .stat-label {
        font-size: 0.9rem;
        color: #666;
    }    
    .fund-stat-card {
        border-left-width: 4px;
    }
    .fund-stat-card.border-success { border-left-color: #198754 !important; }
    .fund-stat-card.border-danger { border-left-color: #dc3545 !important; }
    .fund-stat-card.border-primary { border-left-color: #0d6efd !important; }
    .stat-number.text-success { color: #198754 !important; }
    .stat-number.text-danger { color: #dc3545 !important; }
    .stat-number.text-primary { color: #0d6efd !important; }

    .stat-sub-label {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    .stat-detail-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        border: 1px solid #e9ecef;
    }
    .stat-detail-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .bg-success-light {
        background-color: rgba(25, 135, 84, 0.1);
    }
    .bg-info-light {
        background-color: rgba(13, 110, 253, 0.1);
    }
    .stat-detail-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }
    .stat-detail-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }

    .chart-container {
        position: relative;
        height: 350px; /* Giới hạn chiều cao của biểu đồ */
        width: 100%;
    }

    /* Pagination Styles */
    .pagination {
        gap: 0.25rem;
        display: flex;
        align-items: center;
    }
    
    .pagination .page-link {
        border-radius: 6px;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        text-decoration: none;
        background-color: #fff;
    }
    
    .pagination .page-link:hover {
        background-color: #14b8a6;
        border-color: #14b8a6;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(20, 184, 166, 0.3);
    }
    
    .pagination .page-item.active .page-link {
        background-color: #14b8a6;
        border-color: #14b8a6;
        color: white;
        box-shadow: 0 2px 6px rgba(20, 184, 166, 0.3);
    }
    
    .pagination .page-item.disabled .page-link {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .pagination .page-item.disabled .page-link:hover {
        transform: none;
        box-shadow: none;
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }

    .pagination-info {
        font-size: 0.9rem;
    }
</style>

@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ======== Common Chart Configs & Helpers ========
        const createGradient = (ctx, color1, color2) => {
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color1);
            gradient.addColorStop(1, color2);
            return gradient;
        };

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                        },
                        color: '#333'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: true,
                    boxPadding: 4
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#555',
                        // Ensure integer ticks for counts
                        precision: 0
                    },
                    grid: {
                        color: '#e9ecef'
                    }
                },
                x: {
                    ticks: {
                        color: '#555'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        };

        // ======== Member Growth Chart (Area Chart) ========
        const memberGrowthCtx = document.getElementById('memberGrowthChart')?.getContext('2d');
        if (memberGrowthCtx) {
            const gradient = createGradient(memberGrowthCtx, 'rgba(20, 184, 166, 0.5)', 'rgba(20, 184, 166, 0.05)');
            new Chart(memberGrowthCtx, {
                type: 'line',
                data: {
                    labels: @json($stats['memberGrowth']['labels']),
                    datasets: [{
                        label: 'Tổng số thành viên',
                        data: @json($stats['memberGrowth']['data']),
                        borderColor: '#14b8a6',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#14b8a6',
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#14b8a6',
                        pointRadius: 5,
                    }]
                },
                options: commonOptions
            });
        }

        // ======== Member Structure Chart (Pie Chart) ========
        const memberStructureCtx = document.getElementById('memberStructureChart');
        if (memberStructureCtx) {
            new Chart(memberStructureCtx, {
                type: 'pie',
                data: {
                    labels: @json($stats['memberStructure']['labels']),
                    datasets: [{
                        label: 'Thành viên',
                        data: @json($stats['memberStructure']['data']),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: { ...commonOptions, scales: {} } // Remove scales for pie chart
            });
        }

        // ======== Events by Month Chart (Bar Chart) ========
        const eventsByMonthCtx = document.getElementById('eventsByMonthChart')?.getContext('2d');
        if (eventsByMonthCtx) {
            const gradient = createGradient(eventsByMonthCtx, 'rgba(54, 162, 235, 0.7)', 'rgba(54, 162, 235, 0.4)');
            new Chart(eventsByMonthCtx, {
                type: 'bar',
                data: {
                    labels: @json($stats['eventsByMonth']['labels']),
                    datasets: [{
                        label: 'Số lượng sự kiện',
                        data: @json($stats['eventsByMonth']['data']),
                        backgroundColor: gradient,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        ...commonOptions.plugins,
                        tooltip: {
                            ...commonOptions.plugins.tooltip,
                            callbacks: {
                                label: (context) => `${context.dataset.label}: ${context.parsed.y} sự kiện`
                            }
                        }
                    }
                }
            });
        }

        // ======== Activity Trend Chart (Multi-line Chart) ========
        const activityTrendCtx = document.getElementById('activityTrendChart')?.getContext('2d');
        if (activityTrendCtx) {
            new Chart(activityTrendCtx, {
            type: 'line',
            data: {
                labels: @json($stats['activityTrend']['labels']),
                datasets: [
                    {
                        label: 'Thành viên mới',
                        data: @json($stats['activityTrend']['newMembers']),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Sự kiện mới',
                        data: @json($stats['activityTrend']['newEvents']),
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                ...commonOptions,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
            }
        });
        }
    });
</script>
@endpush