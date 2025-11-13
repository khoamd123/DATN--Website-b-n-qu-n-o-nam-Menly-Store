@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa quỹ #' . $fund->id) 

@section('content')
<div class="content-header">
    <h1>Chỉnh sửa quỹ — #{{ $fund->id }}</h1>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.fund-management.update', $fund->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $fund->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        // Lấy mục đầu tiên của quỹ, hoặc một mục trống nếu không có
                        $firstItem = $fund->items->first();
                    @endphp

                    <div class="mb-3">
                        <label for="amount" class="form-label">Số tiền</label>
                        <input type="number" name="items[{{ $firstItem->id ?? 'new-0' }}][amount]" class="form-control @error('items.*.amount') is-invalid @enderror" step="0.01" placeholder="Số tiền" value="{{ old('items.'.($firstItem->id ?? 'new-0').'.amount', $firstItem->amount ?? '') }}" required>
                        @if($firstItem)
                            <input type="hidden" name="items[{{ $firstItem->id }}][id]" value="{{ $firstItem->id }}">
                            <input type="hidden" name="items[{{ $firstItem->id }}][description]" value="{{ $fund->title }}">
                        @endif
                        @error('items.*.amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="transaction_type" class="form-label">Loại giao dịch</label>
                        <select class="form-select @error('transaction_type') is-invalid @enderror" id="transaction_type" name="transaction_type" required>
                            <option value="thu" {{ old('transaction_type', $fund->transaction_type) == 'thu' ? 'selected' : '' }}>Thu</option>
                            <option value="chi" {{ old('transaction_type', $fund->transaction_type) == 'chi' ? 'selected' : '' }}>Chi</option>
                        </select>
                        @error('transaction_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="club_id" class="form-label">Câu lạc bộ</label>
                        <select class="form-select @error('club_id') is-invalid @enderror" id="club_id" name="club_id" required>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ old('club_id', $fund->club_id) == $club->id ? 'selected' : '' }}>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('club_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Ghi chú</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="3">{{ old('content', $fund->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.fund-management.show', $fund->id) }}" class="btn btn-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
