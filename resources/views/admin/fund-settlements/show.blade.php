@extends('admin.layouts.app')

@section('title', 'Chi tiết quyết toán - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Chi tiết quyết toán</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fund-settlements') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('admin.fund-requests.show', $fundRequest->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Xem yêu cầu gốc
            </a>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Thông tin yêu cầu -->
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-info"></i> Thông tin yêu cầu
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Tiêu đề:</strong></td>
                        <td>{{ $fundRequest->title }}</td>
                    </tr>
                    <tr>
                        <td><strong>Mô tả:</strong></td>
                        <td>{{ $fundRequest->description ?: 'Không có' }}</td>
                    </tr>
                    <tr>
                        <td><strong>CLB:</strong></td>
                        <td>
                            <span class="badge bg-primary">{{ $fundRequest->club->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Sự kiện:</strong></td>
                        <td>
                            @if($fundRequest->event)
                                <span class="badge bg-info">{{ $fundRequest->event->name }}</span>
                            @else
                                <span class="text-muted">Không có</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Người tạo:</strong></td>
                        <td>{{ $fundRequest->creator->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Ngày tạo:</strong></td>
                        <td>{{ $fundRequest->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Thông tin duyệt -->
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle text-success"></i> Thông tin duyệt
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Số tiền yêu cầu:</strong></td>
                        <td>
                            <span class="text-primary">{{ number_format($fundRequest->requested_amount) }} VNĐ</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Số tiền duyệt:</strong></td>
                        <td>
                            <span class="text-success fw-bold">{{ number_format($fundRequest->approved_amount) }} VNĐ</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Trạng thái:</strong></td>
                        <td>
                            @if($fundRequest->status === 'approved')
                                <span class="badge bg-success">Đã duyệt</span>
                            @elseif($fundRequest->status === 'partially_approved')
                                <span class="badge bg-warning">Duyệt một phần</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Người duyệt:</strong></td>
                        <td>{{ $fundRequest->approver->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Ngày duyệt:</strong></td>
                        <td>{{ $fundRequest->approved_at ? $fundRequest->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
                    </tr>
                    @if($fundRequest->approval_notes)
                        <tr>
                            <td><strong>Ghi chú duyệt:</strong></td>
                            <td>{{ $fundRequest->approval_notes }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Thông tin quyết toán -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calculator text-warning"></i> Thông tin quyết toán
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <!-- Thông tin số tiền -->
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Số tiền duyệt</h6>
                            <h4 class="text-primary mb-0">{{ number_format($fundRequest->approved_amount) }}</h4>
                            <small class="text-muted">VNĐ</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Số tiền thực tế</h6>
                            <h4 class="text-success mb-0">{{ number_format($fundRequest->actual_amount) }}</h4>
                            <small class="text-muted">VNĐ</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">Chênh lệch</h6>
                            @php
                                $difference = $fundRequest->approved_amount - $fundRequest->actual_amount;
                            @endphp
                            @if($difference > 0)
                                <h4 class="text-info mb-0">-{{ number_format($difference) }}</h4>
                                <small class="text-muted">Tiền thừa</small>
                            @elseif($difference < 0)
                                <h4 class="text-danger mb-0">+{{ number_format(abs($difference)) }}</h4>
                                <small class="text-muted">Vượt quá</small>
                            @else
                                <h4 class="text-success mb-0">0</h4>
                                <small class="text-muted">Khớp</small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Ghi chú và thông tin người quyết toán -->
                <div class="row">
                    <div class="col-md-6">
                        @if($fundRequest->settlement_notes)
                            <div class="mb-3">
                                <label class="form-label"><strong><i class="fas fa-file-alt text-primary"></i> Ghi chú quyết toán:</strong></label>
                                <div class="p-3 bg-light rounded border-start border-primary border-3">
                                    {{ $fundRequest->settlement_notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td class="bg-light"><strong>Trạng thái:</strong></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Đã quyết toán
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>Người quyết toán:</strong></td>
                                    <td>{{ $fundRequest->settler->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>Ngày quyết toán:</strong></td>
                                    <td>{{ $fundRequest->settlement_date ? $fundRequest->settlement_date->format('d/m/Y H:i') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Hóa đơn/Chứng từ -->
                @if($fundRequest->settlement_documents && count($fundRequest->settlement_documents) > 0)
                    <div class="mt-4">
                        <h6 class="mb-3">
                            <i class="fas fa-file-alt text-primary"></i> 
                            <strong>Hóa đơn/Chứng từ ({{ count($fundRequest->settlement_documents) }} file)</strong>
                        </h6>
                        <div class="row g-3">
                            @foreach($fundRequest->settlement_documents as $index => $document)
                                <div class="col-6 col-md-3">
                                    <div class="card border hover-shadow h-100">
                                        <div class="card-body text-center p-3">
                                            @if(str_contains($document, '.pdf'))
                                                <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            @else
                                                <i class="fas fa-file-image fa-3x text-primary mb-2"></i>
                                            @endif
                                            <p class="mb-2 small text-truncate" title="{{ basename($document) }}">
                                                {{ basename($document) }}
                                            </p>
                                            <a href="{{ asset('storage/' . $document) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary w-100">
                                                <i class="fas fa-eye"></i> Xem
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.row {
    margin-left: 0;
    margin-right: 0;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: all 0.3s ease;
    max-width: 100%;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}

.table {
    width: 100%;
    max-width: 100%;
    table-layout: fixed;
}

.table td {
    padding: 0.5rem;
    vertical-align: middle;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.table-sm th,
.table-sm td {
    padding: 0.5rem 0.75rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

h4 {
    font-weight: 600;
}

h6 {
    font-weight: 600;
}

@media (max-width: 991.98px) {
    .col-lg-6 {
        margin-bottom: 1rem;
    }
}
</style>
@endsection
