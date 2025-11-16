@extends('layouts.student')

@section('title', 'Tạo Câu lạc bộ mới - UniClubs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-plus-circle text-teal"></i> Tạo Câu lạc bộ mới
                    </h2>
                    <p class="text-muted mb-0">Điền thông tin dưới đây để gửi yêu cầu thành lập CLB của bạn.</p>
                </div>
                <a href="{{ route('student.clubs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('student.clubs.store') }}" method="POST" enctype="multipart/form-data" id="createClubForm">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <!-- Tên CLB -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Tên Câu lạc bộ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mô tả ngắn -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Mô tả ngắn <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                            <small class="form-text text-muted">Mô tả ngắn gọn về CLB của bạn (tối đa 255 ký tự).</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Giới thiệu chi tiết -->
                        <div class="mb-3">
                            <label for="introduction" class="form-label fw-bold">Giới thiệu chi tiết</label>
                            <textarea class="form-control @error('introduction') is-invalid @enderror" id="introduction" name="introduction" rows="10">{{ old('introduction') }}</textarea>
                            <small class="form-text text-muted">Bài viết chi tiết giới thiệu về mục đích, hoạt động, cách thức tham gia...</small>
                            @error('introduction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- Lĩnh vực -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="field_id" class="form-label fw-bold mb-0">Lĩnh vực <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createFieldModal">
                                    <i class="fas fa-plus me-1"></i> Tạo lĩnh vực mới
                                </button>
                            </div>
                            <select class="form-select @error('field_id') is-invalid @enderror" id="field_id" name="field_id" required>
                                <option value="" disabled selected>-- Chọn lĩnh vực --</option>
                                @foreach($fields as $field)
                                    <option value="{{ $field->id }}" {{ old('field_id') == $field->id ? 'selected' : '' }}>{{ $field->name }}</option>
                                @endforeach
                            </select>
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const fieldSelect = document.getElementById('field_id');
                                const mainForm = document.getElementById('createClubForm');
                                
                                fieldSelect.addEventListener('change', function() {
                                    const selectedValue = this.value;
                                    const hiddenInput = mainForm.querySelector('input[name="new_field_name"]');
                                    
                                    // Nếu chọn một lĩnh vực có sẵn (có ID số), xóa hidden input và khôi phục required
                                    if (selectedValue && !selectedValue.startsWith('new_')) {
                                        if (hiddenInput) {
                                            hiddenInput.remove();
                                        }
                                        this.setAttribute('required', 'required');
                                        
                                        // Xóa option tạm thời nếu có
                                        const tempOptions = this.querySelectorAll('option[value^="new_"]');
                                        tempOptions.forEach(opt => opt.remove());
                                    }
                                });
                            });
                            </script>
                            @error('field_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('new_field_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Logo CLB -->
                        <div class="mb-3">
                            <label for="logo" class="form-label fw-bold">Logo CLB</label>
                            <input class="form-control @error('logo') is-invalid @enderror" type="file" id="logo" name="logo" accept="image/*">
                            <small class="form-text text-muted">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</small>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Sau khi tạo, yêu cầu của bạn sẽ được gửi đến quản trị viên để xét duyệt. Bạn sẽ tự động trở thành <strong>Trưởng CLB</strong> sau khi yêu cầu được chấp thuận.
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Gửi yêu cầu tạo CLB</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal tạo lĩnh vực mới -->
<div class="modal fade" id="createFieldModal" tabindex="-1" aria-labelledby="createFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFieldModalLabel">
                    <i class="fas fa-plus-circle text-teal me-2"></i>Tạo lĩnh vực mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createFieldForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_field_name" class="form-label fw-bold">Tên lĩnh vực <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="new_field_name" name="new_field_name" placeholder="Nhập tên lĩnh vực mới" maxlength="100" required>
                        <small class="form-text text-muted">Tên lĩnh vực sẽ được hiển thị trong danh sách lĩnh vực.</small>
                        <div id="new_field_name_error" class="invalid-feedback d-none"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>Tạo lĩnh vực
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
@include('partials.ckeditor-upload-adapter', ['uploadUrl' => route('student.posts.upload-image'), 'csrfToken' => csrf_token()])
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tạo upload adapter plugin
    const SimpleUploadAdapterPlugin = window.CKEditorUploadAdapterFactory('{{ route("student.posts.upload-image") }}', '{{ csrf_token() }}');
    
    let descriptionEditor = null;
    let introductionEditor = null;
    
    // Khởi tạo CKEditor cho mô tả ngắn
    const descriptionTextarea = document.querySelector('#description');
    if (descriptionTextarea) {
        ClassicEditor
            .create(descriptionTextarea, {
                extraPlugins: [SimpleUploadAdapterPlugin],
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'uploadImage', '|',
                        'undo', 'redo'
                    ]
                },
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'toggleImageCaption',
                        'imageStyle:inline',
                        'imageStyle:block',
                        'imageStyle:side'
                    ]
                },
                language: 'vi'
            })
            .then(editor => {
                descriptionEditor = editor;
                console.log('CKEditor initialized for description');
            })
            .catch(error => {
                console.error('Error initializing CKEditor for description:', error);
            });
    }
    
    // Khởi tạo CKEditor cho giới thiệu chi tiết
    const introductionTextarea = document.querySelector('#introduction');
    if (introductionTextarea) {
        ClassicEditor
            .create(introductionTextarea, {
                extraPlugins: [SimpleUploadAdapterPlugin],
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'blockQuote', 'insertTable', '|',
                        'uploadImage', '|',
                        'undo', 'redo'
                    ]
                },
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'toggleImageCaption',
                        'imageStyle:inline',
                        'imageStyle:block',
                        'imageStyle:side'
                    ]
                },
                language: 'vi'
            })
            .then(editor => {
                introductionEditor = editor;
                console.log('CKEditor initialized for introduction');
            })
            .catch(error => {
                console.error('Error initializing CKEditor for introduction:', error);
            });
    }
    
    // Đảm bảo sync dữ liệu từ CKEditor vào textarea trước khi submit
    const createClubForm = document.getElementById('createClubForm');
    if (createClubForm) {
        createClubForm.addEventListener('submit', function(e) {
            // Sync description editor
            if (descriptionEditor) {
                const descriptionData = descriptionEditor.getData();
                // Lấy text thuần (không có HTML) để kiểm tra độ dài
                const descriptionText = descriptionData.replace(/<[^>]*>/g, '').trim();
                if (descriptionText.length > 255) {
                    e.preventDefault();
                    alert('Mô tả ngắn không được vượt quá 255 ký tự. Hiện tại: ' + descriptionText.length + ' ký tự.');
                    return false;
                }
                descriptionEditor.updateSourceElement();
            }
            
            // Sync introduction editor
            if (introductionEditor) {
                introductionEditor.updateSourceElement();
            }
        });
    }
    
    // Code cho modal tạo lĩnh vực mới
    const createFieldForm = document.getElementById('createFieldForm');
    const newFieldNameInput = document.getElementById('new_field_name');
    const fieldSelect = document.getElementById('field_id');
    const modal = new bootstrap.Modal(document.getElementById('createFieldModal'));
    const errorDiv = document.getElementById('new_field_name_error');

    createFieldForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fieldName = newFieldNameInput.value.trim();
        
        if (!fieldName) {
            showError('Vui lòng nhập tên lĩnh vực.');
            return;
        }

        // Kiểm tra xem lĩnh vực đã tồn tại chưa (client-side check)
        const existingOptions = Array.from(fieldSelect.options).map(opt => opt.text.toLowerCase());
        if (existingOptions.includes(fieldName.toLowerCase())) {
            showError('Lĩnh vực này đã tồn tại trong danh sách.');
            return;
        }

        // Tạo option mới và thêm vào select
        const newOption = document.createElement('option');
        newOption.value = 'new_' + Date.now(); // Temporary value
        newOption.textContent = fieldName;
        newOption.selected = true;
        fieldSelect.appendChild(newOption);

        // Tạo hidden input để gửi tên lĩnh vực mới (thêm vào form chính)
        const mainForm = document.getElementById('createClubForm');
        let hiddenInput = mainForm.querySelector('input[name="new_field_name"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'new_field_name';
            mainForm.appendChild(hiddenInput);
        }
        hiddenInput.value = fieldName;

        // Xóa required từ select vì đã có new_field_name
        fieldSelect.removeAttribute('required');

        // Đóng modal và reset form
        modal.hide();
        newFieldNameInput.value = '';
        hideError();

        // Hiển thị thông báo thành công
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            Đã thêm lĩnh vực "${fieldName}" vào danh sách. Bạn có thể tiếp tục điền form.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        createFieldForm.parentElement.insertBefore(alertDiv, createFieldForm);
    });

    function showError(message) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('d-none');
        newFieldNameInput.classList.add('is-invalid');
    }

    function hideError() {
        errorDiv.classList.add('d-none');
        newFieldNameInput.classList.remove('is-invalid');
    }

    // Reset khi đóng modal
    document.getElementById('createFieldModal').addEventListener('hidden.bs.modal', function() {
        newFieldNameInput.value = '';
        hideError();
    });
});
</script>
@endpush
@endsection