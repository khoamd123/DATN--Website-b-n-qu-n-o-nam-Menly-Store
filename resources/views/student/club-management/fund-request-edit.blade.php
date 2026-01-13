@extends('layouts.student')

@section('title', 'Sửa yêu cầu cấp kinh phí - UniClubs')

@php
    $expenseItems = old('expense_items', $fundRequest->expense_items ?? []);
    if (empty($expenseItems)) {
        $expenseItems = [['item' => '', 'amount' => '']];
    }
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-edit text-warning"></i>
                        Sửa yêu cầu cấp kinh phí
                    </h3>
                    <small class="text-muted">CLB: <strong>{{ $club->name }}</strong></small>
                </div>
                <a href="{{ route('student.club-management.fund-requests.show', $fundRequest->id) }}" class="btn btn-secondary btn-sm text-white">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        @if($fundRequest->rejection_reason)
        <div class="content-card mb-3">
            <div class="alert alert-danger">
                <h6 class="alert-heading">
                    <i class="fas fa-times-circle me-2"></i>Lý do từ chối trước đó:
                </h6>
                <p class="mb-0">{{ $fundRequest->rejection_reason }}</p>
            </div>
        </div>
        @endif

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

            <form action="{{ route('student.club-management.fund-requests.update', $fundRequest->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề yêu cầu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $fundRequest->title) }}" 
                                   placeholder="Nhập tiêu đề yêu cầu cấp kinh phí" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả chi tiết <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="6" 
                                      placeholder="Mô tả chi tiết về mục đích sử dụng kinh phí" required>{{ old('description', $fundRequest->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="requested_amount" class="form-label">Số tiền yêu cầu (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('requested_amount') is-invalid @enderror" 
                                           id="requested_amount" name="requested_amount" 
                                           value="{{ old('requested_amount', $fundRequest->requested_amount) }}" min="0" step="1000"
                                           placeholder="Nhập số tiền yêu cầu" required>
                                    @error('requested_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="event_id" class="form-label">Sự kiện liên quan <span class="text-danger">*</span></label>
                                    <select class="form-select @error('event_id') is-invalid @enderror" 
                                            id="event_id" name="event_id" required>
                                        <option value="">Chọn sự kiện</option>
                                        @forelse($events as $event)
                                            <option value="{{ $event->id }}" {{ old('event_id', $fundRequest->event_id) == $event->id ? 'selected' : '' }}>
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
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3" style="background: #F8F9FA; border: 1px solid #E9ECEF;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Chi tiết chi phí</h6>
                            </div>
                            <div class="card-body">
                                <div id="expense-items">
                                    @foreach($expenseItems as $index => $item)
                                        <div class="expense-item mb-3">
                                            <div class="mb-2">
                                                <label class="form-label small">Khoản mục</label>
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="expense_items[{{ $index }}][item]" 
                                                       value="{{ $item['item'] ?? '' }}"
                                                       placeholder="VD: Thuê địa điểm">
                                            </div>
                                            <div>
                                                <label class="form-label small">Số tiền (VNĐ)</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="expense_items[{{ $index }}][amount]" 
                                                       value="{{ $item['amount'] ?? '' }}"
                                                       min="0" step="1000" placeholder="0">
                                            </div>
                                            @if($index > 0)
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-expense-item">
                                                    <i class="fas fa-trash me-1"></i> Xóa
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary w-100" id="add-expense-item">
                                    <i class="fas fa-plus me-1"></i> Thêm khoản mục
                                </button>
                            </div>
                        </div>

                        <div class="card" style="background: #F8F9FA; border: 1px solid #E9ECEF;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-file me-2"></i>Tài liệu hỗ trợ</h6>
                            </div>
                            <div class="card-body">
                                @if($fundRequest->supporting_documents && count($fundRequest->supporting_documents) > 0)
                                    <div class="mb-3">
                                        <label class="form-label small">Tài liệu hiện tại:</label>
                                        <div class="list-group">
                                            @foreach($fundRequest->supporting_documents as $index => $document)
                                                @php
                                                    $docPath = is_array($document) ? ($document['path'] ?? $document[0] ?? reset($document)) : $document;
                                                    $docName = is_array($document) ? ($document['name'] ?? 'Tài liệu ' . ($index + 1)) : basename($document);
                                                @endphp
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <small>{{ $docName }}</small>
                                                    <a href="{{ asset('storage/' . $docPath) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Tài liệu mới sẽ được thêm vào danh sách hiện có
                                        </small>
                                    </div>
                                @endif
                                <div class="mb-2">
                                    <label for="supporting_documents" class="form-label small">Thêm tài liệu mới</label>
                                    <input type="file" class="form-control form-control-sm @error('supporting_documents.*') is-invalid @enderror" 
                                           id="supporting_documents" name="supporting_documents[]" 
                                           multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <small class="text-muted d-block mt-1">
                                        Hỗ trợ: PDF, JPG, PNG, DOC, DOCX (tối đa 10MB mỗi file)
                                    </small>
                                    @error('supporting_documents.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="supporting_documents_preview" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    @if($fundRequest->status === 'rejected')
                        <button type="submit" class="btn btn-primary text-white">
                            <i class="fas fa-paper-plane me-1"></i> Lưu và gửi lại để duyệt
                        </button>
                    @else
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="fas fa-save me-1"></i> Lưu thay đổi
                        </button>
                    @endif
                    <a href="{{ route('student.club-management.fund-requests.show', $fundRequest->id) }}" class="btn btn-secondary text-white">
                        <i class="fas fa-times me-1"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Expense items handling
    let expenseItemCount = {{ count($expenseItems) }};
    
    document.getElementById('add-expense-item').addEventListener('click', function() {
        const container = document.getElementById('expense-items');
        const newItem = document.createElement('div');
        newItem.className = 'expense-item mb-3';
        newItem.innerHTML = `
            <div class="mb-2">
                <label class="form-label small">Khoản mục</label>
                <input type="text" class="form-control form-control-sm" name="expense_items[${expenseItemCount}][item]" 
                       placeholder="VD: Thuê địa điểm">
            </div>
            <div>
                <label class="form-label small">Số tiền (VNĐ)</label>
                <input type="number" class="form-control form-control-sm" name="expense_items[${expenseItemCount}][amount]" 
                       min="0" step="1000" placeholder="0">
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-expense-item">
                <i class="fas fa-trash me-1"></i> Xóa
            </button>
        `;
        container.appendChild(newItem);
        expenseItemCount++;
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-expense-item') || e.target.closest('.remove-expense-item')) {
            const btn = e.target.classList.contains('remove-expense-item') ? e.target : e.target.closest('.remove-expense-item');
            btn.closest('.expense-item').remove();
        }
    });

    // Multi-file preview
    const fileInput = document.getElementById('supporting_documents');
    const previewContainer = document.getElementById('supporting_documents_preview');

    function renderPreviews(files) {
        previewContainer.innerHTML = '';
        Array.from(files).forEach((file, idx) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'd-flex align-items-center mb-2 p-2 border rounded bg-white';

            const info = document.createElement('div');
            info.className = 'flex-grow-1 small';
            info.textContent = file.name;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger ms-2';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            removeBtn.addEventListener('click', function() {
                const dt = new DataTransfer();
                Array.from(fileInput.files).forEach((f, i) => { if (i !== idx) dt.items.add(f); });
                fileInput.files = dt.files;
                renderPreviews(fileInput.files);
            });

            wrapper.appendChild(info);
            wrapper.appendChild(removeBtn);
            previewContainer.appendChild(wrapper);
        });
    }

    fileInput.addEventListener('change', function() {
        renderPreviews(fileInput.files);
    });
});
</script>
@endpush
@endsection

