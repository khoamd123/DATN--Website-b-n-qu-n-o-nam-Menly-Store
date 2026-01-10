@extends('admin.layouts.app')

@section('title', 'Test Date Filter')

@section('content')
<div class="content-header">
    <h1>Test Date Filter</h1>
    <p class="text-muted">Kiểm tra bộ lọc thời gian</p>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Test Date Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.test-date-filter') }}">
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
                    <a href="{{ route('admin.test-date-filter') }}" class="btn btn-secondary">Clear</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Test Results</h5>
            </div>
            <div class="card-body">
                @if($testResults['has_filter'])
                    <div class="alert alert-success">
                        <h6>✅ Filter Applied Successfully!</h6>
                        <ul>
                            <li><strong>Start Date:</strong> {{ $testResults['start_date'] ?? 'N/A' }}</li>
                            <li><strong>End Date:</strong> {{ $testResults['end_date'] ?? 'N/A' }}</li>
                            <li><strong>Users in Period:</strong> {{ $testResults['users_in_period'] ?? 0 }}</li>
                            <li><strong>Clubs in Period:</strong> {{ $testResults['clubs_in_period'] ?? 0 }}</li>
                            <li><strong>Events in Period:</strong> {{ $testResults['events_in_period'] ?? 0 }}</li>
                        </ul>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <h6>⚠️ No Filter Applied</h6>
                        <p>{{ $testResults['message'] ?? 'Please select a date range to test.' }}</p>
                    </div>
                @endif
                
                <div class="alert alert-info">
                    <h6>🕐 Time Information</h6>
                    <ul>
                        <li><strong>Current Time:</strong> {{ $testResults['current_time'] ?? 'N/A' }}</li>
                        <li><strong>Yesterday Start:</strong> {{ $testResults['yesterday_start'] ?? 'N/A' }}</li>
                        <li><strong>Yesterday End:</strong> {{ $testResults['yesterday_end'] ?? 'N/A' }}</li>
                    </ul>
                </div>
                
                <details class="mt-3">
                    <summary>Debug Information</summary>
                    <pre class="mt-2">{{ json_encode($testResults, JSON_PRETTY_PRINT) }}</pre>
                </details>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Quick Test Links</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('admin.test-date-filter', ['date_range' => 'today']) }}" class="btn btn-outline-primary btn-sm w-100">Test Today</a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.test-date-filter', ['date_range' => 'this_month']) }}" class="btn btn-outline-primary btn-sm w-100">Test This Month</a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.test-date-filter', ['date_range' => 'last_month']) }}" class="btn btn-outline-primary btn-sm w-100">Test Last Month</a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.test-date-filter', ['date_range' => 'this_year']) }}" class="btn btn-outline-primary btn-sm w-100">Test This Year</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
