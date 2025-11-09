@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa quỹ - CLB Admin')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-edit"></i> Chỉnh sửa quỹ: {{ $fund->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds') }}">Quản lý quỹ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.funds.show', $fund->id) }}">{{ $fund->name }}</a></li>
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
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin quỹ</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.funds.update', $fund->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tên quỹ <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           name="name" 
                                           value="{{ old('name', $fund->name) }}" 
                                           required>
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
                                            <option value="{{ $club->id }}" {{ old('club_id', $fund->club_id) == $club->id ? 'selected' : '' }}>
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
                                      placeholder="Mô tả về quỹ, mục đích sử dụng...">{{ old('description', $fund->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            @if($fund->initial_amount > 0)
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nguồn tiền</label>
                                        <select name="source" class="form-select @error('source') is-invalid @enderror">
                                            <option value="">Chọn nguồn tiền</option>
                                            <option value="Nhà trường" {{ old('source', $fund->source) == 'Nhà trường' ? 'selected' : '' }}>Nhà trường</option>
                                            <option value="Đóng góp" {{ old('source', $fund->source) == 'Đóng góp' ? 'selected' : '' }}>Đóng góp</option>
                                        </select>
                                        @error('source')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            <div class="{{ $fund->initial_amount > 0 ? 'col-md-6' : 'col-md-12' }}">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ old('status', $fund->status) == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                        <option value="inactive" {{ old('status', $fund->status) == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                                        <option value="closed" {{ old('status', $fund->status) == 'closed' ? 'selected' : '' }}>Đã đóng</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="{{ route('admin.funds.show', $fund->id) }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin hiện tại</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Số tiền ban đầu:</strong><br>
                        <span class="text-success">{{ number_format($fund->initial_amount, 0, ',', '.') }} VNĐ</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Số tiền hiện tại:</strong><br>
                        <span class="text-primary">{{ number_format($fund->current_amount, 0, ',', '.') }} VNĐ</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Người tạo:</strong><br>
                        <span class="text-muted">{{ $fund->creator->name ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Ngày tạo:</strong><br>
                        <span class="text-muted">{{ $fund->created_at ? $fund->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Cập nhật lần cuối:</strong><br>
                        <span class="text-muted">{{ $fund->updated_at ? $fund->updated_at->format('d/m/Y H:i') : 'N/A' }}</span>
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
