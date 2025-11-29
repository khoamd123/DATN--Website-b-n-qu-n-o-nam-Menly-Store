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
                @if($fundRequest->settlement_status === 'settled' && $fundRequest->settler)
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Người quyết toán</label>
                    <p class="mb-0">{{ $fundRequest->settler->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small">Ngày quyết toán</label>
                    <p class="mb-0">{{ $fundRequest->settlement_date ? $fundRequest->settlement_date->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($fundRequest->settlement_status === 'settled')
        <div class="content-card mb-3">
            <div class="d-flex align-items-center mb-3">
                <div class="settlement-icon-wrapper me-3">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
                <div>
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Thông tin quyết toán</h5>
                    <small class="text-muted">Yêu cầu đã được quyết toán</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="settlement-card bg-light border rounded p-3 text-center">
                        <div class="text-muted small mb-2">Số tiền được duyệt</div>
                        <div class="text-primary fw-bold fs-4">{{ number_format($fundRequest->approved_amount ?? 0, 0, ',', '.') }} VNĐ</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="settlement-card bg-light border rounded p-3 text-center">
                        <div class="text-muted small mb-2">Số tiền thực tế</div>
                        <div class="text-info fw-bold fs-4">{{ number_format($fundRequest->actual_amount ?? 0, 0, ',', '.') }} VNĐ</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="settlement-card bg-light border rounded p-3 text-center">
                        <div class="text-muted small mb-2">Chênh lệch</div>
                        @php
                            $difference = ($fundRequest->approved_amount ?? 0) - ($fundRequest->actual_amount ?? 0);
                        @endphp
                        @if($difference > 0)
                            <div class="text-success fw-bold fs-4">-{{ number_format($difference, 0, ',', '.') }} VNĐ</div>
                            <small class="text-success">Tiền thừa</small>
                        @elseif($difference < 0)
                            <div class="text-danger fw-bold fs-4">+{{ number_format(abs($difference), 0, ',', '.') }} VNĐ</div>
                            <small class="text-danger">Vượt quá</small>
                        @else
                            <div class="text-success fw-bold fs-4">0 VNĐ</div>
                            <small class="text-success">Khớp</small>
                        @endif
                    </div>
                </div>
            </div>
            @if($fundRequest->settlement_notes)
            <div class="mt-3">
                <label class="text-muted small"><i class="fas fa-file-alt me-1"></i>Ghi chú quyết toán</label>
                <div class="border rounded p-3 bg-light">
                    {!! nl2br(e($fundRequest->settlement_notes)) !!}
                </div>
            </div>
            @endif
            @if($fundRequest->settlement_documents && count($fundRequest->settlement_documents) > 0)
            <div class="mt-3">
                <label class="text-muted small"><i class="fas fa-file-invoice me-1"></i>Hóa đơn/Chứng từ quyết toán</label>
                <div class="row g-2 mt-2">
                    @foreach($fundRequest->settlement_documents as $index => $document)
                        @php
                            $docPath = is_array($document) ? ($document['path'] ?? $document[0] ?? reset($document)) : $document;
                            $docName = is_array($document) ? ($document['name'] ?? 'Hóa đơn ' . ($index + 1)) : basename($document);
                        @endphp
                        <div class="col-md-3">
                            <a href="{{ asset('storage/' . $docPath) }}" target="_blank" 
                               class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-file-pdf me-1"></i> Hóa đơn {{ $index + 1 }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @elseif($fundRequest->settlement_status === 'settlement_pending')
        <div class="content-card mb-3">
            <div class="alert alert-info mb-0">
                <i class="fas fa-clock me-2"></i>
                <strong>Đang chờ quyết toán:</strong> Yêu cầu này đã được duyệt và đang chờ quản trị viên quyết toán.
            </div>
        </div>
        @endif

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
            <div class="d-flex align-items-center mb-4">
                <div class="document-icon-wrapper me-3">
                    <i class="fas fa-folder-open fa-2x text-teal"></i>
                </div>
                <div>
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Tài liệu hỗ trợ</h5>
                    <small class="text-muted">{{ count($fundRequest->supporting_documents) }} tài liệu</small>
                </div>
            </div>
            <div class="row g-3">
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
                        
                        // Xác định loại file và icon
                        $fileExtension = strtolower(pathinfo($docPath, PATHINFO_EXTENSION));
                        $fileIcon = 'fa-file';
                        $fileColor = 'text-primary';
                        
                        switch ($fileExtension) {
                            case 'pdf':
                                $fileIcon = 'fa-file-pdf';
                                $fileColor = 'text-danger';
                                break;
                            case 'doc':
                            case 'docx':
                                $fileIcon = 'fa-file-word';
                                $fileColor = 'text-primary';
                                break;
                            case 'xls':
                            case 'xlsx':
                                $fileIcon = 'fa-file-excel';
                                $fileColor = 'text-success';
                                break;
                            case 'ppt':
                            case 'pptx':
                                $fileIcon = 'fa-file-powerpoint';
                                $fileColor = 'text-warning';
                                break;
                            case 'jpg':
                            case 'jpeg':
                            case 'png':
                            case 'gif':
                                $fileIcon = 'fa-file-image';
                                $fileColor = 'text-info';
                                break;
                            default:
                                $fileIcon = 'fa-file';
                                $fileColor = 'text-secondary';
                        }
                        
                        // Lấy kích thước file nếu có
                        $fileSize = null;
                        $fullPath = storage_path('app/public/' . $docPath);
                        if (file_exists($fullPath)) {
                            $size = filesize($fullPath);
                            if ($size < 1024) {
                                $fileSize = $size . ' B';
                            } elseif ($size < 1048576) {
                                $fileSize = round($size / 1024, 2) . ' KB';
                            } else {
                                $fileSize = round($size / 1048576, 2) . ' MB';
                            }
                        }
                    @endphp
                    <div class="col-md-4 col-lg-3">
                        <a href="{{ asset('storage/' . $docPath) }}" target="_blank" 
                           class="document-card text-decoration-none">
                            <div class="document-card-inner">
                                <div class="document-icon {{ $fileColor }}">
                                    <i class="fas {{ $fileIcon }} fa-3x"></i>
                                </div>
                                <div class="document-info">
                                    <div class="document-name" title="{{ $docName }}">
                                        @php
                                            $displayName = mb_strlen($docName) > 25 ? mb_substr($docName, 0, 25) . '...' : $docName;
                                        @endphp
                                        {{ $displayName }}
                                    </div>
                                    @if($fileSize)
                                        <div class="document-size text-muted">
                                            <i class="fas fa-hdd me-1"></i>{{ $fileSize }}
                                        </div>
                                    @endif
                                    <div class="document-action mt-2">
                                        <span class="badge bg-teal">
                                            <i class="fas fa-download me-1"></i>Xem tài liệu
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($fundRequest->status === 'rejected')
        <div class="content-card">
            <div class="alert alert-warning mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Yêu cầu đã bị từ chối.</strong> Bạn có thể sửa lại yêu cầu và gửi lại để duyệt.
            </div>
            <div class="d-flex gap-2">
                @php
                    $position = $user->getPositionInClub($fundRequest->club_id);
                @endphp
                @if($position === 'leader')
                    <a href="{{ route('student.club-management.fund-requests.edit', $fundRequest->id) }}" 
                       class="btn btn-warning text-white">
                        <i class="fas fa-edit me-1"></i> Sửa yêu cầu
                    </a>
                    @if($fundRequest->status === 'rejected')
                        <form action="{{ route('student.club-management.fund-requests.resubmit', $fundRequest->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Bạn có chắc chắn muốn gửi lại yêu cầu này để duyệt?');">
                            @csrf
                            <button type="submit" class="btn btn-primary text-white">
                                <i class="fas fa-paper-plane me-1"></i> Gửi lại để duyệt
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .document-icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(20, 184, 166, 0.2);
    }
    
    .document-icon-wrapper i {
        color: white;
    }
    
    .document-card {
        display: block;
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .document-card:hover {
        transform: translateY(-5px);
        text-decoration: none;
    }
    
    .document-card-inner {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .document-card:hover .document-card-inner {
        border-color: #14b8a6;
        box-shadow: 0 8px 16px rgba(20, 184, 166, 0.15);
        background: linear-gradient(to bottom, #ffffff 0%, #f0fdfa 100%);
    }
    
    .document-icon {
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }
    
    .document-card:hover .document-icon {
        transform: scale(1.1);
    }
    
    .document-info {
        width: 100%;
    }
    
    .document-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 8px;
        font-size: 0.95rem;
        word-break: break-word;
    }
    
    .document-size {
        font-size: 0.85rem;
        margin-bottom: 10px;
    }
    
    .document-action {
        margin-top: auto;
    }
    
    .badge.bg-teal {
        background-color: #14b8a6 !important;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.85rem;
    }
    
    .document-card:hover .badge.bg-teal {
        background-color: #0d9488 !important;
        transform: scale(1.05);
    }
    
    @media (max-width: 768px) {
        .document-card-inner {
            padding: 15px;
        }
        
        .document-icon i {
            font-size: 2rem !important;
        }
    }
    
    .settlement-icon-wrapper {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
    }
    
    .settlement-icon-wrapper i {
        color: white;
    }
    
    .settlement-card {
        transition: all 0.3s ease;
    }
    
    .settlement-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

