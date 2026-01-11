@extends('layouts.student')

@section('title', 'Giao dịch quỹ - ' . $club->name)
@section('page_title', 'Giao dịch quỹ')

@section('content')
<div class="content-card mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ $club->name }}</h5>
        <div class="d-flex gap-2">
            @if(isset($position) && in_array($position, ['leader', 'treasurer']))
                <a href="{{ route('student.club-management.fund-deposit-requests') }}?club={{ $club->id }}" class="btn btn-success btn-sm text-white">
                    <i class="fas fa-money-bill-wave me-1"></i> Yêu cầu nộp quỹ
                </a>
                <a href="{{ route('student.club-management.fund-requests') }}?club={{ $club->id }}" class="btn btn-success btn-sm text-white">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Yêu cầu cấp kinh phí
                </a>
                <a href="{{ route('student.club-management.fund-requests') }}?settlement=settled&club={{ $club->id }}" class="btn btn-success btn-sm text-white">
                    <i class="fas fa-calculator me-1"></i> Xem quyết toán
                </a>
            @endif
            @if(isset($position) && in_array($position, ['leader', 'vice_president', 'treasurer']))
                <a href="{{ route('student.club-management.fund-transactions.create', ['club' => $club->id]) }}" class="btn btn-success btn-sm text-white">
                    <i class="fas fa-plus me-1"></i> Tạo giao dịch
                </a>
            @endif
            <a href="{{ route('student.club-management.index') }}" class="btn btn-secondary btn-sm text-white">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>
    <div class="fund-summary-stats">
        <div class="fund-stat-item">
            <span class="fund-stat-label">Tổng thu:</span>
            <span class="fund-stat-value text-success">{{ number_format($summary['income'], 0, ',', '.') }} VNĐ</span>
        </div>
        <div class="fund-stat-item">
            <span class="fund-stat-label">Tổng chi:</span>
            <span class="fund-stat-value text-danger">{{ number_format($summary['expense'], 0, ',', '.') }} VNĐ</span>
        </div>
        <div class="fund-stat-item">
            <span class="fund-stat-label">Số dư:</span>
            <span class="fund-stat-value text-primary">{{ number_format($summary['balance'], 0, ',', '.') }} VNĐ</span>
        </div>
    </div>
</div>

<div class="content-card mb-3">
    <form method="GET" action="{{ route('student.club-management.fund-transactions', ['club' => $club->id]) }}" class="row g-2 align-items-end">
        <input type="hidden" name="club" value="{{ $club->id }}">
        <div class="col-md-3">
            <label class="form-label">Loại</label>
            <select class="form-select" name="type">
                <option value="">Tất cả</option>
                <option value="income" @selected($filterType=='income')>Thu</option>
                <option value="expense" @selected($filterType=='expense')>Chi</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary text-white w-100">
                <i class="fas fa-filter me-1"></i> Lọc
            </button>
        </div>
    </form>
</div>

<div class="content-card">
    @if($transactions->count())
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Loại</th>
                    <th>Danh mục</th>
                    <th>Mô tả</th>
                    <th class="text-end">Số tiền</th>
                    <th class="text-center">Chứng từ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                <tr onclick="window.location='{{ route('student.club-management.fund-transactions.show', $t->id) }}'" style="cursor:pointer;">
                    <td><small class="text-muted">{{ $t->created_at?->format('d/m/Y H:i') }}</small></td>
                    <td>
                        <span class="badge bg-{{ $t->type === 'income' ? 'success' : 'danger' }}">
                            {{ $t->type === 'income' ? 'Thu' : 'Chi' }}
                        </span>
                    </td>
                    <td>{{ $t->category ?? '-' }}</td>
                    @php
                        $descRaw = $t->description ?? '-';
                        // Loại bỏ pattern (chứng từ: ...) khỏi description khi hiển thị
                        $descRaw = preg_replace('/\s*\(chứng từ:\s*[^)]+\)/i', '', $descRaw);
                        $desc = trim(strip_tags(html_entity_decode($descRaw, ENT_QUOTES, 'UTF-8')));
                    @endphp
                    <td>{{ $desc !== '' ? $desc : '-' }}</td>
                    <td class="text-end {{ $t->type === 'income' ? 'text-success' : 'text-danger' }}">
                        {{ number_format($t->amount, 0, ',', '.') }} VNĐ
                    </td>
                    @php 
                        $canApprove = ($user->getPositionInClub($club->id) === 'leader') && $t->status === 'pending';
                        // Kiểm tra file đính kèm
                        $att = $t->attachment_path ?? $t->attachment ?? null;
                        if (!$att && $t->description) {
                            if (preg_match('/\(chứng từ:\s*([^)]+)\)/i', $t->description, $matches)) {
                                $att = trim($matches[1]);
                            }
                        }
                    @endphp
                    <td class="text-center">
                        @if($canApprove)
                            <div class="d-flex flex-column gap-1 align-items-center">
                                <form method="POST" action="{{ route('student.club-management.fund-transactions.approve', $t->id) }}" class="d-inline w-100" onclick="event.stopPropagation();">
                                    @csrf
                                    <button class="btn btn-success btn-sm text-white w-100">
                                        <i class="fas fa-check"></i> Duyệt
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('student.club-management.fund-transactions.reject', $t->id) }}" class="d-inline w-100" onclick="event.stopPropagation();">
                                    @csrf
                                    <button class="btn btn-danger btn-sm text-white w-100">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </form>
                                @if($att)
                                    <a href="{{ asset($att) }}" target="_blank" class="btn btn-sm btn-primary text-white w-100" onclick="event.stopPropagation();">
                                        <i class="fas fa-file-pdf"></i> Tài liệu
                                    </a>
                                @endif
                            </div>
                        @else
                            @php
                                $statusLabels = [
                                    'approved' => 'Đã duyệt',
                                    'pending' => 'Chờ duyệt',
                                    'rejected' => 'Từ chối',
                                ];
                                $statusLabel = $statusLabels[$t->status] ?? ucfirst($t->status);
                            @endphp
                            <div class="d-flex flex-column gap-1 align-items-center">
                                <span class="badge bg-{{ $t->status === 'approved' ? 'success' : ($t->status === 'rejected' ? 'secondary' : 'warning') }}">{{ $statusLabel }}</span>
                                @if($att)
                                    <a href="{{ asset($att) }}" target="_blank" class="btn btn-sm btn-primary text-white" onclick="event.stopPropagation();">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $transactions->links('vendor.pagination.bootstrap-5') }}
    </div>
    @else
        <p class="mb-0 text-muted">Chưa có giao dịch phù hợp.</p>
    @endif
</div>

@push('styles')
<style>
    .fund-summary-stats {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }
    
    .fund-stat-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .fund-stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .fund-stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        line-height: 1.2;
    }
    
    .fund-stat-value.text-success {
        color: #198754 !important;
    }
    
    .fund-stat-value.text-danger {
        color: #dc3545 !important;
    }
    
    .fund-stat-value.text-primary {
        color: #0d6efd !important;
    }
    
    @media (max-width: 768px) {
        .fund-summary-stats {
            gap: 1.5rem;
        }
        
        .fund-stat-value {
            font-size: 1.25rem;
        }
    }
    
    @media (max-width: 576px) {
        .fund-summary-stats {
            flex-direction: column;
            gap: 1rem;
        }
    }
</style>
@endpush
@endsection

