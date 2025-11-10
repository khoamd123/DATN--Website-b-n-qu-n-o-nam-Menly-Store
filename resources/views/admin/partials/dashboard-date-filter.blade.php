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
            <div class="card-body">
                @if(request('start_date') || request('end_date'))
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Khoảng thời gian đã chọn:</strong>
                        @if(request('start_date') && request('end_date'))
                            {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                        @elseif(request('start_date'))
                            Từ {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }}
                        @elseif(request('end_date'))
                            Đến {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
