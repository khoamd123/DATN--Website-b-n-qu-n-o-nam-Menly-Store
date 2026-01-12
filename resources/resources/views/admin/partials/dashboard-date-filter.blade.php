{{-- Dashboard Date Filter Component --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Thống kê theo khoảng thời gian
                </h5>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2 align-items-center">
                    {{-- Giữ lại các tham số hiện tại --}}
                    @foreach(request()->except(['start_date', 'end_date']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    
                    <label for="start_date" class="form-label mb-0 me-2">Từ ngày:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" 
                           value="{{ request('start_date') }}" style="width: 160px;">
                    
                    <label for="end_date" class="form-label mb-0 me-2">Đến ngày:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" 
                           value="{{ request('end_date') }}" style="width: 160px;">
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter me-1"></i>Lọc
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Xóa
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
