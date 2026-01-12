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
            <div class="mb-3">
                <div class="text-muted">Thời gian</div>
                <div>{{ $tx->transaction_date ? \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y H:i') : ($tx->created_at?->format('d/m/Y H:i')) }}</div>
            </div>
            <div class="mb-3">
                <div class="text-muted">Loại</div>
                <div>
                    <span class="badge bg-{{ $tx->type === 'income' ? 'success' : 'danger' }}">{{ $tx->type === 'income' ? 'Thu' : 'Chi' }}</span>
                </div>
            </div>
            <div class="mb-3">
                <div class="text-muted">Danh mục</div>
                <div>{{ $tx->category ?: '-' }}</div>
            </div>
            <div class="mb-3">
                <div class="text-muted">Trạng thái</div>
                <div>
                    @php
                        $statusLabels = [
                            'pending' => 'Chờ duyệt',
                            'approved' => 'Đã duyệt',
                            'rejected' => 'Đã từ chối'
                        ];
                    @endphp
                    <span class="badge bg-{{ $tx->status === 'approved' ? 'success' : ($tx->status === 'rejected' ? 'secondary' : 'warning') }}">
                        {{ $statusLabels[$tx->status] ?? ucfirst($tx->status) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <div class="text-muted">Số tiền</div>
                <div class="{{ $tx->type === 'income' ? 'text-success' : 'text-danger' }}">{{ number_format($tx->amount, 0, ',', '.') }} VNĐ</div>
            </div>
            <div class="mb-3">
                <div class="text-muted">Người tạo</div>
                <div>#{{ $tx->created_by }}</div>
            </div>
            <div class="mb-3">
                <div class="text-muted">Chứng từ</div>
                @php
                    // Kiểm tra cột attachment_path hoặc attachment trước
                    $att = $tx->attachment_path ?? $tx->attachment ?? null;
                    
                    // Nếu không có trong cột, parse từ description
                    if (!$att && $tx->description) {
                        // Tìm pattern: (chứng từ: path/to/file)
                        if (preg_match('/\(chứng từ:\s*([^)]+)\)/i', $tx->description, $matches)) {
                            $att = trim($matches[1]);
                        }
                    }
                @endphp
                <div>
                    @if($att)
                        <a href="{{ asset($att) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-pdf me-1"></i> Xem chứng từ
                        </a>
                    @else
                        <span class="text-muted">Không có</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <div class="text-muted">Mô tả</div>
        @php
            $descRaw = $tx->description ?? '-';
            // Loại bỏ pattern (chứng từ: ...) khỏi description khi hiển thị
            $descRaw = preg_replace('/\s*\(chứng từ:\s*[^)]+\)/i', '', $descRaw);
            $desc = trim(strip_tags(html_entity_decode($descRaw, ENT_QUOTES, 'UTF-8')));
        @endphp
        <div>{{ $desc !== '' ? $desc : '-' }}</div>
    </div>
</div>
@endsection

