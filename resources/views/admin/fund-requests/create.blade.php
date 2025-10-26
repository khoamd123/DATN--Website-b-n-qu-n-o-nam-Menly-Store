@extends('admin.layouts.app')

@section('title', 'Tạo yêu cầu cấp kinh phí')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i>
                        Tạo yêu cầu cấp kinh phí mới
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.fund-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Tiêu đề yêu cầu <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" 
                                           placeholder="Nhập tiêu đề yêu cầu cấp kinh phí">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Mô tả chi tiết <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="5" 
                                              placeholder="Mô tả chi tiết về mục đích sử dụng kinh phí">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="requested_amount">Số tiền yêu cầu (VNĐ) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('requested_amount') is-invalid @enderror" 
                                                   id="requested_amount" name="requested_amount" 
                                                   value="{{ old('requested_amount') }}" min="0" step="1000"
                                                   placeholder="Nhập số tiền yêu cầu">
                                            @error('requested_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="event_id">Sự kiện liên quan <span class="text-danger">*</span></label>
                                            <select class="form-control @error('event_id') is-invalid @enderror" 
                                                    id="event_id" name="event_id" required>
                                                <option value="">Chọn sự kiện ({{ count($events) }} sự kiện có sẵn)</option>
                                                @forelse($events as $event)
                                                    <option value="{{ $event->id }}" {{ (old('event_id') ?: request('event_id')) == $event->id ? 'selected' : '' }}>
                                                        {{ $event->title }} ({{ $event->start_time ? $event->start_time->format('d/m/Y') : 'Chưa có ngày' }})
                                                    </option>
                                                @empty
                                                    <option value="" disabled>Không có sự kiện nào</option>
                                                @endforelse
                                            </select>
                                            @error('event_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="club_id">CLB <span class="text-danger">*</span></label>
                                    <select class="form-control @error('club_id') is-invalid @enderror" 
                                            id="club_id" name="club_id">
                                        <option value="">Chọn CLB</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ (old('club_id') ?: request('club_id')) == $club->id ? 'selected' : '' }}>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('club_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Chi tiết chi phí</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="expense-items">
                                            <div class="expense-item mb-3">
                                                <div class="form-group">
                                                    <label>Khoản mục</label>
                                                    <input type="text" class="form-control" name="expense_items[0][item]" 
                                                           placeholder="VD: Thuê địa điểm">
                                                </div>
                                                <div class="form-group">
                                                    <label>Số tiền (VNĐ)</label>
                                                    <input type="number" class="form-control" name="expense_items[0][amount]" 
                                                           min="0" step="1000" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-expense-item">
                                            <i class="fas fa-plus"></i> Thêm khoản mục
                                        </button>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Tài liệu hỗ trợ</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="supporting_documents">Tải lên tài liệu</label>
                                            <input type="file" class="form-control @error('supporting_documents.*') is-invalid @enderror" 
                                                   id="supporting_documents" name="supporting_documents[]" 
                                                   multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            <small class="form-text text-muted">
                                                Hỗ trợ: PDF, JPG, PNG, DOC, DOCX (tối đa 10MB mỗi file)
                                            </small>
                                            @error('supporting_documents.*')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo yêu cầu
                            </button>
                            <a href="{{ route('admin.fund-requests') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
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

    // Expense items handling
    let expenseItemCount = 1;
    
    document.getElementById('add-expense-item').addEventListener('click', function() {
        const container = document.getElementById('expense-items');
        const newItem = document.createElement('div');
        newItem.className = 'expense-item mb-3';
        newItem.innerHTML = `
            <div class="form-group">
                <label>Khoản mục</label>
                <input type="text" class="form-control" name="expense_items[${expenseItemCount}][item]" 
                       placeholder="VD: Thuê địa điểm">
            </div>
            <div class="form-group">
                <label>Số tiền (VNĐ)</label>
                <input type="number" class="form-control" name="expense_items[${expenseItemCount}][amount]" 
                       min="0" step="1000" placeholder="0">
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-expense-item mt-2">
                <i class="fas fa-trash"></i> Xóa
            </button>
        `;
        container.appendChild(newItem);
        expenseItemCount++;
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-expense-item')) {
            e.target.closest('.expense-item').remove();
        }
    });
});
</script>
@endsection
