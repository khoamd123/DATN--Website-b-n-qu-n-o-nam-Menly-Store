@extends('admin.layouts.app')

@section('title', 'Tạo câu lạc bộ mới')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-plus-circle"></i> Tạo câu lạc bộ mới</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.clubs') }}">Quản lý CLB</a></li>
                <li class="breadcrumb-item active">Tạo CLB mới</li>
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
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin câu lạc bộ</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.clubs.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Logo câu lạc bộ</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            <small class="text-muted">Chọn ảnh logo cho CLB (JPG, PNG, GIF, max 5MB)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên câu lạc bộ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="10" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lĩnh vực <span class="text-danger">*</span></label>
                                    <select class="form-select" id="field-select" name="field_id" onchange="toggleFieldInput()">
                                        <option value="">Chọn lĩnh vực có sẵn</option>
                                        <option value="new">+ Tạo lĩnh vực mới</option>
                                        @php
                                            $fields = \App\Models\Field::all();
                                        @endphp
                                        @foreach($fields as $field)
                                            <option value="{{ $field->id }}" {{ old('field_id') == $field->id ? 'selected' : '' }}>
                                                {{ $field->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3" id="new-field-input" style="display: none;">
                                    <label class="form-label">Tên lĩnh vực mới <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="new_field_name" placeholder="Nhập tên lĩnh vực mới">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trưởng câu lạc bộ</label>
                                    <select class="form-select" name="leader_id">
                                        <option value="">Chưa chọn</option>
                                        @php
                                            $users = \App\Models\User::where('is_admin', false)->get();
                                        @endphp
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('leader_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo câu lạc bộ
                            </button>
                            <a href="{{ route('admin.clubs') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
function toggleFieldInput() {
    const select = document.getElementById('field-select');
    const inputDiv = document.getElementById('new-field-input');
    
    if (select.value === 'new') {
        inputDiv.style.display = 'block';
        select.name = ''; // Disable select khi chọn "new"
        document.querySelector('input[name="new_field_name"]').required = true;
    } else {
        inputDiv.style.display = 'none';
        select.name = 'field_id'; // Enable select khi chọn field có sẵn
        document.querySelector('input[name="new_field_name"]').required = false;
    }
}

// Khởi tạo CKEditor
document.addEventListener('DOMContentLoaded', function() {
    CKEDITOR.replace('description', {
        height: 300,
        language: 'vi',
        filebrowserImageBrowseUrl: '{{ route("admin.posts.upload-image") }}',
        filebrowserImageUploadUrl: '{{ route("admin.posts.upload-image") }}?_token={{ csrf_token() }}',
        toolbar: [
            { name: 'document', items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
            { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
            { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
            { name: 'forms', items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'] },
            '/',
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language'] },
            { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
            { name: 'insert', items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'] },
            '/',
            { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'tools', items: ['Maximize', 'ShowBlocks'] },
            { name: 'about', items: ['About'] }
        ]
    });
});
</script>
@endsection
