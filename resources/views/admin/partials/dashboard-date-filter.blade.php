{{-- Dashboard Date Filter Component --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Thống kê theo khoảng thời gian
                </h5>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                    {{-- Giữ lại các tham số hiện tại --}}
                    @foreach(request()->except(['date_range', 'start_date', 'end_date']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    
                    <select name="date_range" class="form-select form-select-sm" style="width: auto;" onchange="toggleCustomDate()">
                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                        <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                        <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>Tuần này</option>
                        <option value="last_week" {{ request('date_range') == 'last_week' ? 'selected' : '' }}>Tuần trước</option>
                        <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                        <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                        <option value="this_quarter" {{ request('date_range') == 'this_quarter' ? 'selected' : '' }}>Quý này</option>
                        <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>Năm nay</option>
                        <option value="last_year" {{ request('date_range') == 'last_year' ? 'selected' : '' }}>Năm trước</option>
                        <option value="last_7_days" {{ request('date_range') == 'last_7_days' ? 'selected' : '' }}>7 ngày qua</option>
                        <option value="last_30_days" {{ request('date_range') == 'last_30_days' ? 'selected' : '' }}>30 ngày qua</option>
                        <option value="last_90_days" {{ request('date_range') == 'last_90_days' ? 'selected' : '' }}>90 ngày qua</option>
                        <option value="custom" {{ request('date_range') == 'custom' || (request('start_date') && request('end_date')) ? 'selected' : '' }}>Tùy chỉnh</option>
                    </select>
                    
                    <div id="custom_date_inputs" style="display: {{ request('date_range') == 'custom' || (request('start_date') && request('end_date')) ? 'flex' : 'none' }}; gap: 5px;">
                        <input type="date" name="start_date" class="form-control form-control-sm" 
                               value="{{ request('start_date') }}" placeholder="Từ ngày" style="width: 150px;">
                        <input type="date" name="end_date" class="form-control form-control-sm" 
                               value="{{ request('end_date') }}" placeholder="Đến ngày" style="width: 150px;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter me-1"></i>Lọc
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Xóa
                    </a>
                </form>
            </div>
            <div class="card-body">
                @if(request('date_range') || request('start_date') || request('end_date'))
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Khoảng thời gian đã chọn:</strong>
                        @if(request('date_range') == 'custom' || (request('start_date') && request('end_date')))
                            {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                        @else
                            @switch(request('date_range'))
                                @case('today') Hôm nay @break
                                @case('yesterday') Hôm qua @break
                                @case('this_week') Tuần này @break
                                @case('last_week') Tuần trước @break
                                @case('this_month') Tháng này @break
                                @case('last_month') Tháng trước @break
                                @case('this_quarter') Quý này @break
                                @case('this_year') Năm nay @break
                                @case('last_year') Năm trước @break
                                @case('last_7_days') 7 ngày qua @break
                                @case('last_30_days') 30 ngày qua @break
                                @case('last_90_days') 90 ngày qua @break
                            @endswitch
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function toggleCustomDate() {
    const dateRange = document.querySelector('select[name="date_range"]').value;
    const customDateInputs = document.getElementById('custom_date_inputs');
    
    if (dateRange === 'custom') {
        customDateInputs.style.display = 'flex';
    } else {
        customDateInputs.style.display = 'none';
    }
}

// Set default values for custom date inputs
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    
    if (!startDate.value) {
        startDate.value = new Date().toISOString().split('T')[0];
    }
    if (!endDate.value) {
        endDate.value = new Date().toISOString().split('T')[0];
    }
});
</script>
