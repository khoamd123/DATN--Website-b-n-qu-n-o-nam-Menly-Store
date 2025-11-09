{{-- Date Filter Component --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-calendar-alt me-2"></i>Bộ lọc thời gian
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ request()->url() }}" class="row g-3">
            {{-- Giữ lại các tham số hiện tại --}}
            @foreach(request()->except(['date_range', 'start_date', 'end_date']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            
            <div class="col-md-4">
                <label for="date_range" class="form-label">Khoảng thời gian</label>
                <select name="date_range" id="date_range" class="form-select" onchange="toggleCustomDate()">
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
            </div>
            
            <div class="col-md-3" id="start_date_group" style="display: {{ request('date_range') == 'custom' || (request('start_date') && request('end_date')) ? 'block' : 'none' }};">
                <label for="start_date" class="form-label">Từ ngày</label>
                <input type="date" name="start_date" id="start_date" class="form-control" 
                       value="{{ request('start_date') }}">
            </div>
            
            <div class="col-md-3" id="end_date_group" style="display: {{ request('date_range') == 'custom' || (request('start_date') && request('end_date')) ? 'block' : 'none' }};">
                <label for="end_date" class="form-label">Đến ngày</label>
                <input type="date" name="end_date" id="end_date" class="form-control" 
                       value="{{ request('end_date') }}">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i>Lọc
                </button>
                <a href="{{ request()->url() }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Xóa
                </a>
            </div>
        </form>
        
        @if(request('date_range') || request('start_date') || request('end_date'))
            <div class="mt-3">
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
            </div>
        @endif
    </div>
</div>

<script>
function toggleCustomDate() {
    const dateRange = document.getElementById('date_range').value;
    const startDateGroup = document.getElementById('start_date_group');
    const endDateGroup = document.getElementById('end_date_group');
    
    if (dateRange === 'custom') {
        startDateGroup.style.display = 'block';
        endDateGroup.style.display = 'block';
    } else {
        startDateGroup.style.display = 'none';
        endDateGroup.style.display = 'none';
    }
}

// Set default values for custom date inputs
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    if (!startDate.value) {
        startDate.value = new Date().toISOString().split('T')[0];
    }
    if (!endDate.value) {
        endDate.value = new Date().toISOString().split('T')[0];
    }
});
</script>
