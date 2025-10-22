@extends('admin.layouts.app')

@section('title', 'Thêm Tài nguyên - CLB Admin')

@section('content')
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">Thêm Tài nguyên Mới</h1>
        <p class="text-muted mb-0">Tạo mẫu đơn, tải lên hình ảnh, video, tài liệu cho CLB</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.club-resources.index') }}">Tài nguyên CLB</a></li>
            <li class="breadcrumb-item active">Thêm mới</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-edit"></i> Thông tin tài nguyên</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.club-resources.store') }}" enctype="multipart/form-data" id="resourceForm">
                    @csrf
                    
                    <!-- Tiêu đề -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">
                            Tiêu đề tài nguyên 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
                               placeholder="Ví dụ: Mẫu đơn xin gia nhập CLB"
                               minlength="5"
                               maxlength="255"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Tiêu đề ngắn gọn, mô tả rõ ràng tài nguyên</div>
                    </div>

                    <!-- Mô tả -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">
                            <i class="fas fa-align-left text-primary"></i> Mô tả chi tiết
                        </label>
                        
                        <!-- Rich Text Editor Toolbar -->
                        <div class="rich-text-toolbar mb-2">
                            <div class="toolbar-group">
                                <select class="form-select form-select-sm" id="formatSelect">
                                    <option value="p">Paragraph</option>
                                    <option value="h1">Heading 1</option>
                                    <option value="h2">Heading 2</option>
                                    <option value="h3">Heading 3</option>
                                </select>
                            </div>
                            
                            <div class="toolbar-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="boldBtn" title="Bold">
                                    <strong>B</strong>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="italicBtn" title="Italic">
                                    <em>I</em>
                                </button>
                            </div>
                            
                            <div class="toolbar-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="bulletListBtn" title="Bullet List">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="numberListBtn" title="Number List">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                            </div>
                            
                            <div class="toolbar-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="linkBtn" title="Link">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="imageBtn" title="Insert Image">
                                    <i class="fas fa-image"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Content Area -->
                        <div class="rich-text-content">
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="6" 
                                      placeholder="Mô tả chi tiết về tài nguyên này..."
                                      maxlength="2000">{{ old('description') }}</textarea>
                        </div>
                        
                        <!-- Content Preview -->
                        <div id="contentPreview" class="mt-3" style="display: none;">
                            <h6 class="mb-2">Preview:</h6>
                            <div id="previewContent" class="border rounded p-3 bg-light" style="min-height: 100px;"></div>
                        </div>
                        
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Sử dụng các nút trên để định dạng mô tả. Mô tả chi tiết giúp người dùng hiểu rõ hơn về tài nguyên.
                            </small>
                        </div>
                    </div>

                    <!-- Upload file hoặc link -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Tải lên file hoặc nhập link
                        </label>
                        
                        <ul class="nav nav-tabs mb-3" id="uploadTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-upload" type="button" role="tab">
                                    <i class="fas fa-file-upload"></i> Tải file lên
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-input" type="button" role="tab">
                                    <i class="fas fa-link"></i> Nhập link
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="uploadTabContent">
                            <!-- File Upload Tab -->
                            <div class="tab-pane fade show active" id="file-upload" role="tabpanel">
                                <div class="upload-area border-2 border-dashed rounded p-4 text-center">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Kéo thả file vào đây hoặc click để chọn</h6>
                                    <p class="text-muted small mb-3">Hỗ trợ: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, MP4 (Tối đa 20MB)</p>
                                    <input type="file" 
                                           id="file" 
                                           name="file" 
                                           class="d-none"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('file').click()">
                                        <i class="fas fa-plus"></i> Chọn file
                                    </button>
                                </div>
                                <div id="filePreview" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file"></i> <span id="fileName"></span>
                                        <button type="button" class="btn-close float-end" onclick="clearFile()"></button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Link Input Tab -->
                            <div class="tab-pane fade" id="link-input" role="tabpanel">
                                <input type="url" 
                                       class="form-control @error('external_link') is-invalid @enderror" 
                                       id="external_link" 
                                       name="external_link" 
                                       value="{{ old('external_link') }}" 
                                       placeholder="https://example.com/document.pdf">
                                @error('external_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Nhập link Google Drive, Dropbox, YouTube,...</div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
                
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-cog"></i> Cài đặt</h6>
            </div>
            <div class="card-body">
                <!-- Câu lạc bộ -->
                <div class="mb-3">
                    <label for="club_id" class="form-label fw-bold">
                        <i class="fas fa-users text-secondary"></i> Câu lạc bộ 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('club_id') is-invalid @enderror" id="club_id" name="club_id" required>
                        <option value="">Chọn câu lạc bộ</option>
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

                <!-- Loại tài nguyên -->
                <div class="mb-3">
                    <label for="resource_type" class="form-label fw-bold">
                        <i class="fas fa-tag text-secondary"></i> Loại tài nguyên 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('resource_type') is-invalid @enderror" id="resource_type" name="resource_type" required>
                        <option value="form" {{ old('resource_type') == 'form' ? 'selected' : '' }}>📋 Mẫu đơn</option>
                        <option value="image" {{ old('resource_type') == 'image' ? 'selected' : '' }}>🖼️ Hình ảnh</option>
                        <option value="video" {{ old('resource_type') == 'video' ? 'selected' : '' }}>🎥 Video</option>
                        <option value="pdf" {{ old('resource_type') == 'pdf' ? 'selected' : '' }}>📄 PDF</option>
                        <option value="document" {{ old('resource_type') == 'document' ? 'selected' : '' }}>📝 Tài liệu</option>
                        <option value="guide" {{ old('resource_type') == 'guide' ? 'selected' : '' }}>📖 Hướng dẫn</option>
                        <option value="other" {{ old('resource_type') == 'other' ? 'selected' : '' }}>📦 Khác</option>
                    </select>
                    @error('resource_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Trạng thái -->
                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">
                        <i class="fas fa-toggle-on text-secondary"></i> Trạng thái 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>✅ Hoạt động</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>⏸️ Tạm dừng</option>
                        <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>📦 Lưu trữ</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Nút lưu -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Lưu Tài nguyên
            </button>
            <a href="{{ route('admin.club-resources.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </div>
</div>
</form>
@endsection

@section('scripts')
<script>
// Rich Text Editor Functions
(function() {
    'use strict';
    
    // Initialize when DOM is ready
    function init() {
        console.log('Initializing rich text editor for description...');
        initializeToolbar();
    }
    
    // Try multiple initialization strategies
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Fallback initialization
    window.addEventListener('load', function() {
        console.log('Window loaded, re-initializing...');
        setTimeout(init, 100);
    });
})();

function initializeToolbar() {
    console.log('Initializing toolbar...');
    const contentTextarea = document.getElementById('description');
    if (!contentTextarea) {
        console.error('Description textarea not found');
        return;
    }
    
    // Format Select
    const formatSelect = document.getElementById('formatSelect');
    if (formatSelect) {
        formatSelect.addEventListener('change', function() {
            const format = this.value;
            const selectedText = getSelectedText();
            if (selectedText) {
                let formattedText = '';
                switch(format) {
                    case 'h1':
                        formattedText = '# ' + selectedText;
                        break;
                    case 'h2':
                        formattedText = '## ' + selectedText;
                        break;
                    case 'h3':
                        formattedText = '### ' + selectedText;
                        break;
                    default:
                        formattedText = selectedText;
                }
                insertText(formattedText);
            }
            this.value = 'p'; // Reset to paragraph
        });
    }
    
    // Bold Button
    const boldBtn = document.getElementById('boldBtn');
    if (boldBtn) {
        boldBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            wrapSelectedText('**', '**');
            toggleButtonActive(this);
        });
    }
    
    // Italic Button
    const italicBtn = document.getElementById('italicBtn');
    if (italicBtn) {
        italicBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            wrapSelectedText('*', '*');
            toggleButtonActive(this);
        });
    }
    
    // List Buttons
    const bulletListBtn = document.getElementById('bulletListBtn');
    if (bulletListBtn) {
        bulletListBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            if (selectedText) {
                const lines = selectedText.split('\n');
                const bulletList = lines.map(line => '- ' + line).join('\n');
                insertText(bulletList);
            } else {
                insertText('- ');
            }
            toggleButtonActive(this);
        });
    }
    
    const numberListBtn = document.getElementById('numberListBtn');
    if (numberListBtn) {
        numberListBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            if (selectedText) {
                const lines = selectedText.split('\n');
                const numberList = lines.map((line, index) => (index + 1) + '. ' + line).join('\n');
                insertText(numberList);
            } else {
                insertText('1. ');
            }
            toggleButtonActive(this);
        });
    }
    
    // Link Button
    const linkBtn = document.getElementById('linkBtn');
    if (linkBtn) {
        linkBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            const url = prompt('Nhập URL:', 'https://');
            if (url) {
                const linkText = selectedText || 'Link text';
                insertText(`[${linkText}](${url})`);
            }
            toggleButtonActive(this);
        });
    }
    
    // Image Button
    const imageBtn = document.getElementById('imageBtn');
    if (imageBtn) {
        imageBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Create a hidden file input for image selection
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.multiple = false;
            fileInput.style.display = 'none';
            
            fileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    // Validate file size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Ảnh vượt quá 5MB. Vui lòng chọn ảnh nhỏ hơn.');
                        return;
                    }
                    
                    // Validate file type
                    if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'].includes(file.type)) {
                        alert('Vui lòng chọn file ảnh hợp lệ (JPG, PNG, GIF, WebP).');
                        return;
                    }
                    
                    // Convert to base64 and insert into content
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageMarkdown = `![${file.name}](${e.target.result})`;
                        insertText(imageMarkdown);
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Trigger file selection
            document.body.appendChild(fileInput);
            fileInput.click();
            document.body.removeChild(fileInput);
            
            toggleButtonActive(this);
        });
    }
}

// Helper Functions
function getSelectedText() {
    const textarea = document.getElementById('description');
    if (!textarea) return '';
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    return textarea.value.substring(start, end);
}

function insertText(text) {
    const textarea = document.getElementById('description');
    if (!textarea) return;
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const before = textarea.value.substring(0, start);
    const after = textarea.value.substring(end);
    
    textarea.value = before + text + after;
    
    // Set cursor position after inserted text
    const newCursorPos = start + text.length;
    textarea.selectionStart = textarea.selectionEnd = newCursorPos;
    textarea.focus();
    
    // Trigger input event for character counter
    textarea.dispatchEvent(new Event('input'));
}

function wrapSelectedText(prefix, suffix) {
    const textarea = document.getElementById('description');
    if (!textarea) return;
    
    const selectedText = getSelectedText();
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    
    // Check if text is already wrapped with the same prefix/suffix
    if (selectedText) {
        // Check if already wrapped
        if (selectedText.startsWith(prefix) && selectedText.endsWith(suffix)) {
            // Remove wrapping
            const unwrappedText = selectedText.substring(prefix.length, selectedText.length - suffix.length);
            const before = textarea.value.substring(0, start);
            const after = textarea.value.substring(end);
            const newText = before + unwrappedText + after;
            
            textarea.value = newText;
            textarea.selectionStart = start;
            textarea.selectionEnd = start + unwrappedText.length;
        } else {
            // Add wrapping
            const before = textarea.value.substring(0, start);
            const after = textarea.value.substring(end);
            const newText = before + prefix + selectedText + suffix + after;
            
            textarea.value = newText;
            textarea.selectionStart = start + prefix.length;
            textarea.selectionEnd = start + prefix.length + selectedText.length;
        }
    } else {
        // Insert at cursor position
        const before = textarea.value.substring(0, start);
        const after = textarea.value.substring(end);
        const newText = before + prefix + suffix + after;
        
        textarea.value = newText;
        textarea.selectionStart = textarea.selectionEnd = start + prefix.length;
    }
    
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}

function toggleButtonActive(button) {
    if (!button) return;
    
    button.classList.add('active');
    setTimeout(() => {
        button.classList.remove('active');
    }, 200);
}

// File upload handling
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('fileName').textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        document.getElementById('filePreview').style.display = 'block';
    }
});

function clearFile() {
    document.getElementById('file').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Drag and drop
const uploadArea = document.querySelector('.upload-area');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('border-primary', 'bg-light');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-primary', 'bg-light');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-primary', 'bg-light');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('file').files = files;
        document.getElementById('fileName').textContent = files[0].name + ' (' + formatFileSize(files[0].size) + ')';
        document.getElementById('filePreview').style.display = 'block';
    }
});

// Form validation
document.getElementById('resourceForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const clubId = document.getElementById('club_id').value;
    const resourceType = document.getElementById('resource_type').value;
    const file = document.getElementById('file').files[0];
    const externalLink = document.getElementById('external_link').value.trim();
    
    if (title.length < 5) {
        alert('Tiêu đề phải có ít nhất 5 ký tự');
        e.preventDefault();
        return false;
    }
    
    if (!clubId) {
        alert('Vui lòng chọn câu lạc bộ');
        e.preventDefault();
        return false;
    }
    
    if (!resourceType) {
        alert('Vui lòng chọn loại tài nguyên');
        e.preventDefault();
        return false;
    }
    
    if (!file && !externalLink) {
        alert('Vui lòng tải lên file hoặc nhập link');
        e.preventDefault();
        return false;
    }
    
    if (file && file.size > 20 * 1024 * 1024) {
        alert('File không được vượt quá 20MB');
        e.preventDefault();
        return false;
    }
    
    return true;
});
</script>

<style>
.upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

.upload-area.border-primary {
    border-color: #0d6efd !important;
    background-color: #e7f3ff;
}

.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    font-weight: 600;
}

/* Rich Text Toolbar Styles */
.rich-text-toolbar {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem 0.375rem 0 0;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.toolbar-group {
    display: flex;
    align-items: center;
    gap: 4px;
    padding-right: 8px;
    border-right: 1px solid #dee2e6;
}

.toolbar-group:last-child {
    border-right: none;
    padding-right: 0;
}

.toolbar-group .btn {
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    border: 1px solid #dee2e6;
    background-color: #ffffff;
    color: #495057;
    transition: all 0.2s ease;
}

.toolbar-group .btn:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
    color: #212529;
}

.toolbar-group .btn.active {
    background-color: #007bff;
    border-color: #007bff;
    color: #ffffff;
}

.toolbar-group .form-select {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    background-color: #ffffff;
    min-width: 100px;
}

.rich-text-content {
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    overflow: hidden;
}

.rich-text-content .form-control {
    border: none;
    border-radius: 0;
    resize: vertical;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-size: 14px;
    line-height: 1.5;
}

.rich-text-content .form-control:focus {
    box-shadow: none;
    border-color: transparent;
}

/* Content Preview Styles */
#contentPreview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background-color: #ffffff;
}

#previewContent {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
}

#previewContent img {
    border-radius: 0.375rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

#previewContent img:hover {
    transform: scale(1.02);
}

#previewContent blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1rem 0;
    background-color: #f8f9fa;
    border-radius: 0 0.375rem 0.375rem 0;
}

#previewContent ul, #previewContent ol {
    padding-left: 1.5rem;
}

#previewContent li {
    margin-bottom: 0.25rem;
}

/* Responsive toolbar */
@media (max-width: 768px) {
    .rich-text-toolbar {
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .toolbar-group {
        gap: 2px;
        padding-right: 4px;
    }
    
    .toolbar-group .btn {
        padding: 3px 6px;
        font-size: 11px;
    }
    
    .toolbar-group .form-select {
        font-size: 11px;
        padding: 3px 6px;
        min-width: 80px;
    }
}
</style>
@endsection


