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
            <a href="{{ route('student.club-management.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> Quay lại
            </a>
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

    <!-- Fund Stats -->
    <div class="content-card">
        <h4 class="mb-4"><i class="fas fa-wallet text-info me-2"></i> Thống kê quỹ</h4>
        @if($stats['fund']['balance'] > 0 || $stats['fund']['totalIncome'] > 0 || $stats['fund']['totalExpense'] > 0)
            <div class="row">
                <div class="col-md-4 mb-4">
                    <a href="{{ route('student.club-management.fund-transactions', ['type' => 'income']) }}" class="text-decoration-none">
                        <div class="stat-card h-100 fund-stat-card border-success">
                            <div class="stat-icon bg-success"><i class="fas fa-arrow-down"></i></div>
                            <div class="stat-info">
                                <div class="stat-number text-success">{{ number_format($stats['fund']['totalIncome'], 0, ',', '.') }} VNĐ</div>
                                <div class="stat-label">Tổng thu</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-4">
                    <a href="{{ route('student.club-management.fund-transactions', ['type' => 'expense']) }}" class="text-decoration-none">
                        <div class="stat-card h-100 fund-stat-card border-danger">
                            <div class="stat-icon bg-danger"><i class="fas fa-arrow-up"></i></div>
                            <div class="stat-info">
                                <div class="stat-number text-danger">{{ number_format($stats['fund']['totalExpense'], 0, ',', '.') }} VNĐ</div>
                                <div class="stat-label">Tổng chi</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 mb-4">
                    <a href="{{ route('student.club-management.fund-transactions') }}" class="text-decoration-none">
                        <div class="stat-card h-100 fund-stat-card border-primary">
                            <div class="stat-icon bg-primary"><i class="fas fa-balance-scale"></i></div>
                            <div class="stat-info">
                                <div class="stat-number text-primary">{{ number_format($stats['fund']['balance'], 0, ',', '.') }} VNĐ</div>
                                <div class="stat-label">Số dư</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @if(!empty($stats['fund']['expenseByCategory']))
            <div class="mt-4">
                <h5 class="mb-3"><i class="fas fa-tags text-muted me-2"></i> Phân loại chi tiêu</h5>
                <div class="expense-category-list">
                    @foreach($stats['fund']['expenseByCategory'] as $category => $total)
                        <div class="expense-category-item">
                            <span class="category-name">{{ ucfirst(str_replace('_', ' ', $category)) }}</span>
                            <span class="category-amount">{{ number_format($total, 0, ',', '.') }} VNĐ</span>
                        </div>
                    @endforeach
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

    .expense-category-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    .expense-category-item {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #e9ecef;
    }
    .category-name {
        font-weight: 500;
        color: #495057;
    }
    .category-amount {
        font-weight: bold;
        color: #dc3545;
    }

    .chart-container {
        position: relative;
        height: 350px; /* Giới hạn chiều cao của biểu đồ */
        width: 100%;
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