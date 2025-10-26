@extends('admin.layouts.app')

@section('title', 'Tạo quỹ mới - CLB Admin')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-plus-circle"></i> Tạo quỹ mới</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds') }}">Quản lý quỹ</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin quỹ</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.funds.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tên quỹ</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Để trống nếu có CLB (sẽ tự động là 'Quỹ của [Tên CLB]')">

                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">CLB</label>
                                    <select name="club_id" class="form-select @error('club_id') is-invalid @enderror">
                                        <option value="">Chọn CLB (để trống nếu là quỹ chung)</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('club_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" 
                                      id="description"
                                      rows="5" 
                                      placeholder="Mô tả về quỹ, mục đích sử dụng...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số tiền ban đầu <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('initial_amount') is-invalid @enderror" 
                                               name="initial_amount" 
                                               id="initial_amount"
                                               value="{{ old('initial_amount', 0) }}" 
                                               min="0" 
                                               step="1000" 
                                               placeholder="0"
                                               required>
                                        <span class="input-group-text">VNĐ</span>
                                    </div>

                                    @error('initial_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3" id="source_field" style="display: none;">
                                    <label class="form-label">Nguồn tiền</label>
                                    <select name="source" class="form-select @error('source') is-invalid @enderror">
                                        <option value="">Chọn nguồn tiền</option>
                                        <option value="Nhà trường" {{ old('source') == 'Nhà trường' ? 'selected' : '' }}>Nhà trường</option>
                                        <option value="Đóng góp" {{ old('source') == 'Đóng góp' ? 'selected' : '' }}>Đóng góp</option>
                                    </select>
                                    @error('source')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Tạo quỹ
                            </button>
                            <a href="{{ route('admin.funds') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
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

    // Xử lý hiển thị/ẩn trường nguồn tiền
    const initialAmountInput = document.getElementById('initial_amount');
    const sourceField = document.getElementById('source_field');

    function toggleSourceField() {
        const amount = parseFloat(initialAmountInput.value) || 0;
        if (amount > 0) {
            sourceField.style.display = 'block';
        } else {
            sourceField.style.display = 'none';
            // Reset nguồn tiền về null khi ẩn
            const sourceSelect = sourceField.querySelector('select');
            if (sourceSelect) {
                sourceSelect.value = '';
            }
        }
    }

    // Gọi khi tải trang
    toggleSourceField();

    // Lắng nghe sự kiện thay đổi số tiền
    initialAmountInput.addEventListener('input', toggleSourceField);
    initialAmountInput.addEventListener('change', toggleSourceField);
});
</script>
@endsection
