@extends('admin.layouts.app')

@section('title', 'Chi tiết quỹ #'.$fund->id)

@section('content')
<div class="content-header">
    <h1>Chi tiết quỹ — #{{ $fund->id }}</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-8">
                <h4>{{ $fund->title }}</h4>
                <p class="text-muted">{{ $fund->content }}</p>
                <p><strong>Câu lạc bộ:</strong> {{ $fund->club->name ?? 'Không xác định' }}</p>
                <p><strong>Người tạo:</strong> {{ $fund->user->name ?? 'Không xác định' }} — {{ $fund->created_at->format('d/m/Y H:i') }}</p>
                <p>
                    <strong>Loại:</strong>
                    <span class="badge bg-{{ ($fund->transaction_type === 'thu') ? 'success' : 'warning' }}">
                        {{ $fund->transaction_type === 'thu' ? 'Thu' : 'Chi' }}
                    </span>
                    &nbsp;
                    <strong>Số tiền:</strong> {{ number_format($fund->amount,0,',','.') }}đ
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.fund-management') }}" class="btn btn-secondary mb-2">Quay lại</a>
                <a href="{{ route('admin.fund-management.edit', $fund->id) }}" class="btn btn-warning mb-2">
                    <i class="fas fa-edit"></i> Sửa
                </a>
                <button class="btn btn-danger mb-2" onclick="deleteFund({{ $fund->id }})">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </div>
        </div>

        <hr>

        <h5>Mục chi / Thu</h5>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mô tả</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fund->items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ number_format($item->amount,0,',','.') }}đ</td>
                            <td>
                                @php
                                    $statusLabels = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Đã từ chối'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'secondary') }}">
                                    {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
// Xóa 
function deleteFund(id) {
    if (!confirm('Bạn có chắc muốn xóa giao dịch này? Hành động này không thể hoàn tác.')) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/fund-management/${id}`;
    form.style.display = 'none';
    const inputToken = document.createElement('input');
    inputToken.name = '_token';
    inputToken.value = token;
    form.appendChild(inputToken);
    const inputMethod = document.createElement('input');
    inputMethod.name = '_method';
    inputMethod.value = 'DELETE';
    form.appendChild(inputMethod);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection