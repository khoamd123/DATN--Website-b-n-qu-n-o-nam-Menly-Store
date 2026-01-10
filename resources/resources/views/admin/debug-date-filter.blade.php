@extends('admin.layouts.app')

@section('title', 'Debug Date Filter')

@section('content')
<div class="content-header">
    <h1>Debug Date Filter</h1>
    <p class="text-muted">Kiểm tra bộ lọc thời gian</p>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Test Date Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <div class="mb-3">
                        <label class="form-label">Date Range</label>
                        <select name="date_range" class="form-select">
                            <option value="">Không chọn</option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                            <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                            <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>Tuần này</option>
                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                            <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                            <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>Năm nay</option>
                            <option value="last_year" {{ request('date_range') == 'last_year' ? 'selected' : '' }}>Năm trước</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Custom Date Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-6">
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Test Filter</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Clear</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Current Request Data</h5>
            </div>
            <div class="card-body">
                <pre>{{ json_encode(request()->all(), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Test Results</h5>
            </div>
            <div class="card-body">
                @if(request()->hasAny(['date_range', 'start_date', 'end_date']))
                    <div class="alert alert-info">
                        <h6>Filter Applied:</h6>
                        <ul>
                            @if(request('date_range'))
                                <li>Date Range: {{ request('date_range') }}</li>
                            @endif
                            @if(request('start_date'))
                                <li>Start Date: {{ request('start_date') }}</li>
                            @endif
                            @if(request('end_date'))
                                <li>End Date: {{ request('end_date') }}</li>
                            @endif
                        </ul>
                    </div>
                @else
                    <div class="alert alert-warning">
                        No date filter applied
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
