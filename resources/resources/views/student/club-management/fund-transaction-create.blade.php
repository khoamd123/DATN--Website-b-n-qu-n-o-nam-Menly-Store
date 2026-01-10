@extends('layouts.student')

@section('title', 'Tạo giao dịch quỹ - ' . $club->name)
@section('page_title', 'Tạo giao dịch quỹ')

@section('content')
<div class="content-card mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-0">{{ $club->name }}</h5>
        <small class="text-muted">Chỉ Trưởng/Phó/Cán sự có thể tạo giao dịch</small>
    </div>
    <a href="{{ route('student.club-management.fund-transactions') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Danh sách giao dịch
    </a>
</div>

<div class="content-card">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('student.club-management.fund-transactions.store') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        <div class="col-md-3">
            <label class="form-label">Loại giao dịch <span class="text-danger">*</span></label>
            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                <option value="income" {{ old('type')==='income' ? 'selected' : '' }}>Thu</option>
                <option value="expense" {{ old('type')==='expense' ? 'selected' : '' }}>Chi</option>
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Số tiền (VNĐ) <span class="text-danger">*</span></label>
            <input type="number" min="1" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}" required>
            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Danh mục</label>
            <input type="text" class="form-control @error('category') is-invalid @enderror" name="category" value="{{ old('category') }}" placeholder="VD: tài trợ, phí thành viên...">
            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Ngày giao dịch</label>
            <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" name="transaction_date" value="{{ old('transaction_date') }}">
            @error('transaction_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">Mô tả</label>
            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Ghi chú, nội dung giao dịch...">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Đính kèm chứng từ (ảnh/PDF, tối đa 5MB)</label>
            <input type="file" class="form-control @error('attachment') is-invalid @enderror" name="attachment" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
            @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <button class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Tạo giao dịch
            </button>
        </div>
    </form>
</div>
@endsection

