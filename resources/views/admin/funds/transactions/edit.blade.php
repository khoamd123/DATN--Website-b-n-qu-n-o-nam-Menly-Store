@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa giao dịch - CLB Admin')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-edit"></i> Chỉnh sửa giao dịch</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds') }}">Quản lý quỹ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds.show', $fund->id) }}">{{ $fund->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds.transactions', $fund->id) }}">Giao dịch</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin giao dịch</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.funds.transactions.update', [$fund->id, $transaction->id]) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Loại giao dịch <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="">Chọn loại giao dịch</option>
                                        <option value="income" {{ old('type', $transaction->type) == 'income' ? 'selected' : '' }}>Thu (Tiền vào)</option>
                                        <option value="expense" {{ old('type', $transaction->type) == 'expense' ? 'selected' : '' }}>Chi (Tiền ra)</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số tiền <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('amount') is-invalid @enderror" 
                                               name="amount" 
                                               value="{{ old('amount', $transaction->amount) }}" 
                                               min="0" 
                                               step="1000" 
                                               required>
                                        <span class="input-group-text">VNĐ</span>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tiêu đề giao dịch <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   name="title" 
                                   value="{{ old('title', $transaction->title) }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả chi tiết</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Mô tả chi tiết về giao dịch...">{{ old('description', $transaction->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Danh mục</label>
                                    <input type="text" 
                                           class="form-control @error('category') is-invalid @enderror" 
                                           name="category" 
                                           value="{{ old('category', $transaction->category) }}" 
                                           placeholder="Ví dụ: Ăn uống, Vận chuyển, Vật liệu...">
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ngày giao dịch <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('transaction_date') is-invalid @enderror" 
                                           name="transaction_date" 
                                           value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" 
                                           required>
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sự kiện liên quan</label>
                            <select name="event_id" class="form-select @error('event_id') is-invalid @enderror">
                                <option value="">Chọn sự kiện (không bắt buộc)</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ old('event_id', $transaction->event_id) == $event->id ? 'selected' : '' }}>
                                        {{ $event->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('event_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($transaction->receipt_paths && count($transaction->receipt_paths) > 0)
                            <div class="mb-3">
                                <label class="form-label">Chứng từ hiện tại</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($transaction->receipt_paths as $index => $path)
                                        @if(file_exists(public_path($path)))
                                            <a href="{{ asset($path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-file-pdf"></i> Chứng từ {{ $index + 1 }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Chứng từ/Hóa đơn mới</label>
                            <input type="file" 
                                   class="form-control @error('receipts') is-invalid @enderror" 
                                   name="receipts[]" 
                                   multiple
                                   accept=".jpg,.jpeg,.png,.pdf">
                            @error('receipts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('receipts.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="{{ route('admin.funds.transactions.show', [$fund->id, $transaction->id]) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin quỹ</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Tên quỹ:</strong><br>
                        <span class="text-primary">{{ $fund->name }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Số dư hiện tại:</strong><br>
                        <span class="text-success fw-bold">{{ number_format($fund->current_amount, 0, ',', '.') }} VNĐ</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>CLB:</strong><br>
                        @if($fund->club)
                            <span class="badge bg-info">{{ $fund->club->name }}</span>
                        @else
                            <span class="text-muted">Quỹ chung</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Lưu ý</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-info-circle"></i> Quan trọng:</h6>
                        <ul class="mb-0">
                            <li>Chỉ có thể chỉnh sửa giao dịch chưa được duyệt</li>
                            <li>Thay đổi sẽ không ảnh hưởng đến số dư quỹ</li>
                            <li>Giao dịch vẫn ở trạng thái "Chờ duyệt"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
