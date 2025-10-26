@extends('admin.layouts.app')

@section('title', 'Tạo giao dịch mới - CLB Admin')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-plus-circle"></i> Tạo giao dịch mới</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds') }}">Quản lý quỹ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds.show', $fund->id) }}">{{ $fund->name }}</a></li>
                <li class="breadcrumb-item active">Tạo giao dịch</li>
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> Có lỗi xảy ra:
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Thông tin giao dịch</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.funds.transactions.store', $fund->id) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Loại giao dịch <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="">Chọn loại giao dịch</option>
                                        <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Thu (Tiền vào)</option>
                                        <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Chi (Tiền ra)</option>
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
                                               value="{{ old('amount') }}" 
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
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả chi tiết</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" 
                                      id="description"
                                      rows="5" 
                                      placeholder="Mô tả chi tiết về giao dịch...">{{ old('description') }}</textarea>
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
                                           value="{{ old('category') }}" 
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
                                           value="{{ old('transaction_date', date('Y-m-d')) }}" 
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
                                <option value="">Chọn sự kiện (không bắt buộc) - {{ count($events) }} sự kiện</option>
                                @forelse($events as $event)
                                    <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                        {{ $event->title }} - {{ $event->club_id ? 'CLB: ' . ($event->club->name ?? 'N/A') : 'Chung' }}
                                    </option>
                                @empty
                                    <option value="">Không có sự kiện nào</option>
                                @endforelse
                            </select>
                            @error('event_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($fund->club_id)
                                <small class="text-muted">Đang hiển thị sự kiện của <strong>{{ $fund->club->name }}</strong> hoặc sự kiện chung</small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Chứng từ/Hóa đơn</label>
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
                                <i class="fas fa-save"></i> Tạo giao dịch
                            </button>
                            <a href="{{ route('admin.funds.transactions', $fund->id) }}" class="btn btn-secondary">
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


        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo CKEditor cho mô tả
    ClassicEditor
        .create(document.querySelector('#description'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                ]
            }
        })
        .then(editor => {
            console.log('CKEditor initialized successfully');
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });
});
</script>
@endsection
