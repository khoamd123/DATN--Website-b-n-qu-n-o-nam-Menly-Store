@extends('layouts.student')

@section('title', 'Chi tiết giao dịch - ' . $club->name)
@section('page_title', 'Chi tiết giao dịch')

@section('content')
<div class="content-card mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-0">{{ $club->name }}</h5>
        <small class="text-muted">Mã giao dịch: #{{ $tx->id }}</small>
    </div>
    <a href="{{ route('student.club-management.fund-transactions') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Danh sách giao dịch
    </a>
</div>

<div class="content-card">
    <div class="row">
        <div class="col-md-6">
            <h6 class="mb-3 border-bottom pb-2"><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin cơ bản</h6>
            
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Thời gian giao dịch</label>
                <div class="fw-semibold">
                    @if($tx->transaction_date)
                        {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y H:i') }}
                    @else
                        {{ $tx->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                    @endif
                </div>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Loại giao dịch</label>
                <div>
                    <span class="badge bg-{{ $tx->type === 'income' ? 'success' : 'danger' }} fs-6">
                        <i class="fas fa-{{ $tx->type === 'income' ? 'arrow-down' : 'arrow-up' }} me-1"></i>
                        {{ $tx->type === 'income' ? 'Thu' : 'Chi' }}
                    </span>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Danh mục</label>
                <div class="fw-semibold">
                    @if($tx->category)
                        <span class="badge bg-info">{{ $tx->category }}</span>
                    @else
                        <span class="text-muted fst-italic">Chưa phân loại</span>
                    @endif
                </div>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Trạng thái</label>
                @php
                    $statusLabels = [
                        'approved' => 'Đã duyệt',
                        'pending' => 'Chờ duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
                    ];
                    $statusLabel = $statusLabels[$tx->status] ?? ucfirst($tx->status);
                    $statusColors = [
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        'cancelled' => 'secondary',
                    ];
                    $statusColor = $statusColors[$tx->status] ?? 'secondary';
                @endphp
                <div>
                    <span class="badge bg-{{ $statusColor }} fs-6">
                        <i class="fas fa-{{ $tx->status === 'approved' ? 'check-circle' : ($tx->status === 'pending' ? 'clock' : ($tx->status === 'rejected' ? 'times-circle' : 'ban')) }} me-1"></i>
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
            
            @if($tx->event)
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Sự kiện liên quan</label>
                <div>
                    <a href="{{ route('student.events.show', $tx->event->id) }}" class="text-decoration-none">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i>
                        {{ $tx->event->title }}
                    </a>
                </div>
            </div>
            @endif
        </div>
        
        <div class="col-md-6">
            <h6 class="mb-3 border-bottom pb-2"><i class="fas fa-money-bill-wave me-2 text-success"></i>Thông tin tài chính</h6>
            
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Số tiền</label>
                <div class="fs-4 fw-bold {{ $tx->type === 'income' ? 'text-success' : 'text-danger' }}">
                    {{ $tx->type === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', '.') }} VNĐ
                </div>
            </div>
            
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Người tạo</label>
                <div class="fw-semibold">
                    @if($tx->creator)
                        <i class="fas fa-user me-1 text-primary"></i>
                        {{ $tx->creator->name }}
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </div>
                <small class="text-muted">Ngày tạo: {{ $tx->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</small>
            </div>
            
            @if($tx->status === 'approved' && $tx->approver)
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Người duyệt</label>
                <div class="fw-semibold">
                    <i class="fas fa-check-circle me-1 text-success"></i>
                    {{ $tx->approver->name }}
                </div>
                @if($tx->approved_at)
                    <small class="text-muted">Ngày duyệt: {{ $tx->approved_at->format('d/m/Y H:i') }}</small>
                @endif
            </div>
            @endif
            
            @if($tx->status === 'rejected' && $tx->rejection_reason)
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Lý do từ chối</label>
                <div class="alert alert-danger mb-0 py-2">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    {{ $tx->rejection_reason }}
                </div>
            </div>
            @endif
            
            <div class="mb-3">
                <label class="text-muted small d-block mb-1">Chứng từ / Tài liệu</label>
                @php
                    // Kiểm tra các cột có thể chứa file
                    $attachments = [];
                    
                    // Kiểm tra receipt_path
                    if ($tx->receipt_path) {
                        $attachments[] = ['path' => $tx->receipt_path, 'name' => 'Hóa đơn'];
                    }
                    
                    // Kiểm tra receipt_paths (array)
                    if ($tx->receipt_paths && is_array($tx->receipt_paths)) {
                        foreach ($tx->receipt_paths as $index => $path) {
                            if ($path) {
                                $attachments[] = ['path' => $path, 'name' => 'Hóa đơn ' . ($index + 1)];
                            }
                        }
                    }
                    
                    // Nếu không có trong cột, parse từ description
                    if (empty($attachments) && $tx->description) {
                        if (preg_match_all('/\(chứng từ:\s*([^)]+)\)/i', $tx->description, $matches)) {
                            foreach ($matches[1] as $path) {
                                $attachments[] = ['path' => trim($path), 'name' => 'Chứng từ'];
                            }
                        }
                    }
                @endphp
                <div>
                    @if(!empty($attachments))
                        @foreach($attachments as $attachment)
                            <a href="{{ asset($attachment['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-2">
                                <i class="fas fa-file-pdf me-1"></i> {{ $attachment['name'] }}
                            </a>
                        @endforeach
                    @else
                        <span class="text-muted fst-italic">Không có chứng từ</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if($tx->title)
    <div class="mb-3 mt-3 pt-3 border-top">
        <label class="text-muted small d-block mb-1">Tiêu đề</label>
        <div class="fw-semibold fs-5">{{ $tx->title }}</div>
    </div>
    @endif
    
    <div class="mb-3">
        <label class="text-muted small d-block mb-1">Mô tả chi tiết</label>
        @php
            $descRaw = $tx->description ?? '';
            // Loại bỏ pattern (chứng từ: ...) khỏi description khi hiển thị
            $descRaw = preg_replace('/\s*\(chứng từ:\s*[^)]+\)/i', '', $descRaw);
            $desc = trim(strip_tags(html_entity_decode($descRaw, ENT_QUOTES, 'UTF-8')));
        @endphp
        <div class="border rounded p-3 bg-light">
            @if($desc !== '')
                {!! nl2br(e($desc)) !!}
            @else
                <span class="text-muted fst-italic">Không có mô tả</span>
            @endif
        </div>
    </div>
</div>
@endsection

