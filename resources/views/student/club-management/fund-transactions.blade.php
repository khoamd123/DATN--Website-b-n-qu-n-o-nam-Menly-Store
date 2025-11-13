@extends('layouts.student')

@section('title', 'Giao dịch quỹ - ' . $club->name)
@section('page_title', 'Giao dịch quỹ')

@section('content')
<div class="content-card mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-0">{{ $club->name }}</h5>
        <small class="text-muted">Tổng thu: {{ number_format($summary['income'], 0, ',', '.') }} VNĐ &nbsp;&middot;&nbsp; Tổng chi: {{ number_format($summary['expense'], 0, ',', '.') }} VNĐ &nbsp;&middot;&nbsp; Số dư: {{ number_format($summary['balance'], 0, ',', '.') }} VNĐ</small>
    </div>
    <div class="d-flex gap-2">
        @if(isset($position) && $position === 'leader')
            <a href="{{ route('student.club-management.fund-requests') }}" class="btn btn-success btn-sm text-white">
                <i class="fas fa-plus me-1"></i> Yêu cầu cấp kinh phí
            </a>
            <a href="{{ route('student.club-management.fund-requests') }}?settlement=settled" class="btn btn-info btn-sm text-white">
                <i class="fas fa-calculator me-1"></i> Xem quyết toán
            </a>
        @endif
        <a href="{{ route('student.club-management.fund-transactions.create') }}" class="btn btn-primary btn-sm text-white">
            <i class="fas fa-plus me-1"></i> Tạo giao dịch
        </a>
        <a href="{{ route('student.club-management.index') }}" class="btn btn-secondary btn-sm text-white">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
    </div>

<div class="content-card mb-3">
    <form method="GET" action="{{ route('student.club-management.fund-transactions') }}" class="row g-2 align-items-end">
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
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="d-flex flex-column gap-1 align-items-center">
                                <span class="badge bg-{{ $t->status === 'approved' ? 'success' : ($t->status === 'rejected' ? 'secondary' : 'warning') }}">{{ ucfirst($t->status) }}</span>
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
@endsection

