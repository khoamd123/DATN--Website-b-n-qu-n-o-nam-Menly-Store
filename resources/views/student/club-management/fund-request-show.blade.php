@extends('layouts.student')

@section('title', 'Chi tiết yêu cầu cấp kinh phí - UniClubs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-money-bill-wave text-teal"></i>
                        Chi tiết yêu cầu cấp kinh phí
                    </h3>
                    <small class="text-muted">ID: #{{ $fundRequest->id }}</small>
                </div>
                <a href="{{ route('student.club-management.fund-requests') }}" class="btn btn-secondary btn-sm text-white">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="content-card mb-3">
            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Thông tin yêu cầu</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Tiêu đề</label>
                    <p class="mb-0 fw-semibold">{{ $fundRequest->title }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Trạng thái</label>
                    <p class="mb-0">
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'approved' => 'success',
                                'partially_approved' => 'info',
                                'rejected' => 'danger',
                            ];
                            $statusLabels = [
                                'pending' => 'Chờ duyệt',
                                'approved' => 'Đã duyệt',
                                'partially_approved' => 'Duyệt một phần',
                                'rejected' => 'Từ chối',
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$fundRequest->status] ?? 'secondary' }}">
                            {{ $statusLabels[$fundRequest->status] ?? $fundRequest->status }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Số tiền yêu cầu</label>
                    <p class="mb-0 text-primary fw-bold fs-5">{{ number_format($fundRequest->requested_amount, 0, ',', '.') }} VNĐ</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Số tiền duyệt</label>
                    <p class="mb-0">
                        @if($fundRequest->approved_amount)
                            <span class="text-success fw-bold fs-5">{{ number_format($fundRequest->approved_amount, 0, ',', '.') }} VNĐ</span>
                        @else
                            <span class="text-muted">Chưa duyệt</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Sự kiện</label>
                    <p class="mb-0">
                        @if($fundRequest->event)
                            <strong>{{ $fundRequest->event->title }}</strong>
                            <br><small class="text-muted">{{ $fundRequest->event->start_time ? $fundRequest->event->start_time->format('d/m/Y H:i') : 'Chưa có ngày' }}</small>
                        @else
                            <span class="text-muted">Không có</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">CLB</label>
                    <p class="mb-0">
                        <span class="badge bg-info text-dark">{{ $fundRequest->club->name ?? 'N/A' }}</span>
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Người tạo</label>
                    <p class="mb-0">{{ $fundRequest->creator->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Ngày tạo</label>
                    <p class="mb-0">{{ $fundRequest->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($fundRequest->approver)
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Người duyệt</label>
                    <p class="mb-0">{{ $fundRequest->approver->name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Ngày duyệt</label>
                    <p class="mb-0">{{ $fundRequest->approved_at ? $fundRequest->approved_at->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                @endif
                <div class="col-12 mb-3">
                    <label class="text-muted small">Mô tả chi tiết</label>
                    <div class="border rounded p-3 bg-light">
                        {!! nl2br(e($fundRequest->description)) !!}
                    </div>
                </div>
                @if($fundRequest->approval_notes)
                <div class="col-12 mb-3">
                    <label class="text-muted small">Ghi chú duyệt</label>
                    <div class="border rounded p-3 bg-info bg-opacity-10">
                        <p class="mb-0 text-info">{{ $fundRequest->approval_notes }}</p>
                    </div>
                </div>
                @endif
                @if($fundRequest->rejection_reason)
                <div class="col-12 mb-3">
                    <label class="text-muted small">Lý do từ chối</label>
                    <div class="border rounded p-3 bg-danger bg-opacity-10">
                        <p class="mb-0 text-danger">{{ $fundRequest->rejection_reason }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($fundRequest->expense_items && count($fundRequest->expense_items) > 0)
        <div class="content-card mb-3">
            <h5 class="mb-3"><i class="fas fa-list me-2"></i>Chi tiết chi phí</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Khoản mục</th>
                            <th class="text-end">Số tiền (VNĐ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fundRequest->expense_items as $item)
                            <tr>
                                <td>{{ $item['item'] ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($item['amount'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Tổng cộng</th>
                            <th class="text-end">{{ number_format($fundRequest->requested_amount, 0, ',', '.') }} VNĐ</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        @if($fundRequest->supporting_documents && count($fundRequest->supporting_documents) > 0)
        <div class="content-card">
            <h5 class="mb-3"><i class="fas fa-file me-2"></i>Tài liệu hỗ trợ ({{ count($fundRequest->supporting_documents) }})</h5>
            <div class="row g-2">
                @foreach($fundRequest->supporting_documents as $index => $document)
                    @php
                        if (is_array($document)) {
                            if (isset($document['path'])) {
                                $docPath = $document['path'];
                            } elseif (isset($document[0])) {
                                $docPath = $document[0];
                            } else {
                                $docPath = reset($document);
                            }
                            $docName = $document['name'] ?? ('Tài liệu ' . ($index + 1));
                        } else {
                            $docPath = $document;
                            $docName = basename($document);
                        }
                    @endphp
                    <div class="col-md-3">
                        <a href="{{ asset('storage/' . $docPath) }}" target="_blank" 
                           class="btn btn-outline-primary btn-sm w-100 text-white">
                            <i class="fas fa-file-pdf me-1"></i> Tài liệu {{ $index + 1 }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

