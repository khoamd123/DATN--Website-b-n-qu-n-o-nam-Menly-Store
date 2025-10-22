@extends('admin.layouts.app')

@section('title', 'Thêm Bài viết - CLB Admin')

@section('content')
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">Tạo Bài viết Mới</h1>
        <p class="text-muted mb-0">Viết và chia sẻ nội dung với cộng đồng</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts') }}">Bài viết</a></li>
            <li class="breadcrumb-item active">Tạo mới</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-edit"></i> Nội dung bài viết</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" id="postForm" novalidate>
                    @csrf
                    
                    <!-- Tiêu đề -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">
                            Tiêu đề bài viết 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
                               placeholder="Nhập tiêu đề hấp dẫn..."
                               minlength="10"
                               maxlength="255"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                 
                    </div>

                    <!-- Nội dung -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-bold">
                             Nội dung bài viết 
                            <span class="text-danger">*</span>
                        </label>
                        
                        <!-- Rich Text Editor Toolbar -->
                        <div class="rich-text-toolbar mb-2">
                            <div class="toolbar-group">
                                <select class="form-select form-select-sm" id="formatSelect">
                                    <option value="p">Paragraph</option>
                                    <option value="h1">Heading 1</option>
                                    <option value="h2">Heading 2</option>
                                    <option value="h3">Heading 3</option>
                                    <option value="h4">Heading 4</option>
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
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="alignLeftBtn" title="Align Left">
                                    <i class="fas fa-align-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="alignCenterBtn" title="Align Center">
                                    <i class="fas fa-align-center"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="alignRightBtn" title="Align Right">
                                    <i class="fas fa-align-right"></i>
                                </button>
                            </div>
                            
                            <div class="toolbar-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="quoteBtn" title="Quote">
                                    <i class="fas fa-quote-right"></i>
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
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="outdentBtn" title="Outdent">
                                    <i class="fas fa-outdent"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="indentBtn" title="Indent">
                                    <i class="fas fa-indent"></i>
                                </button>
                            </div>
                            
                            <div class="toolbar-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="linkBtn" title="Link">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="imageBtn" title="Insert Image">
                                    <i class="fas fa-image"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="tableBtn" title="Table">
                                    <i class="fas fa-table"></i>
                                </button>
                            </div>
                            
                            <div class="toolbar-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="previewBtn" title="Preview Content">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="moreBtn" title="More Options">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Content Area -->
                        <div class="rich-text-content">
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="12" 
                                      placeholder="Viết nội dung bài viết của bạn ở đây..."
                                      minlength="50"
                                      maxlength="50000"
                                      required>{{ old('content') }}</textarea>
                        </div>
                        
                        <!-- Content Preview -->
                        <div id="contentPreview" class="mt-3" style="display: none;">
                            <h6 class="mb-2">Preview:</h6>
                            <div id="previewContent" class="border rounded p-3 bg-light" style="min-height: 100px;"></div>
                        </div>
                        
                        <!-- Help text -->
                        <div class="form-text">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Sử dụng nút <i class="fas fa-image"></i> để chèn ảnh trực tiếp vào nội dung.
                            </small>
                        </div>
                        
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                   
                    </div>

            </div>
        </div>
    </div>
                
                <div class="col-md-4">
                    <!-- Câu lạc bộ -->
                    <div class="mb-3">
                        <label for="club_id" class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                        <select class="form-select @error('club_id') is-invalid @enderror" 
                                id="club_id" 
                                name="club_id" 
                                required>
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

                    <!-- Loại bài viết -->
                    <div class="mb-3">
                        <label for="type" class="form-label">Loại bài viết <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">Chọn loại</option>
                            <option value="post" {{ old('type') == 'post' ? 'selected' : '' }}>Bài viết</option>
                            <option value="announcement" {{ old('type') == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Trạng thái -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="">Chọn trạng thái</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                            <option value="hidden" {{ old('status') == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="previewPost()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu bài viết
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Rich Text Editor Functions
(function() {
    'use strict';
    
    // Initialize when DOM is ready
    function init() {
        console.log('Initializing rich text editor...');
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
    
    // Additional fallback - try after a delay
    setTimeout(function() {
        if (typeof initializeToolbar === 'function') {
            console.log('Delayed initialization...');
            initializeToolbar();
        }
    }, 500);
})();

function initializeToolbar() {
    console.log('Initializing toolbar...');
    const contentTextarea = document.getElementById('content');
    if (!contentTextarea) {
        console.error('Content textarea not found');
        return;
    }
    
    // Test if we can find buttons
    const testBoldBtn = document.getElementById('boldBtn');
    console.log('Bold button found:', testBoldBtn);
    
    if (!testBoldBtn) {
        console.error('Bold button not found - toolbar may not be loaded');
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
                    case 'h4':
                        formattedText = '#### ' + selectedText;
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
    
    // Alignment Buttons
    const alignLeftBtn = document.getElementById('alignLeftBtn');
    if (alignLeftBtn) {
        alignLeftBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            if (selectedText) {
                insertText('<div style="text-align: left;">' + selectedText + '</div>');
            }
            toggleButtonActive(this);
        });
    }
    
    const alignCenterBtn = document.getElementById('alignCenterBtn');
    if (alignCenterBtn) {
        alignCenterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            if (selectedText) {
                insertText('<div style="text-align: center;">' + selectedText + '</div>');
            }
            toggleButtonActive(this);
        });
    }
    
    const alignRightBtn = document.getElementById('alignRightBtn');
    if (alignRightBtn) {
        alignRightBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            if (selectedText) {
                insertText('<div style="text-align: right;">' + selectedText + '</div>');
            }
            toggleButtonActive(this);
        });
    }
    
    // Quote Button
    const quoteBtn = document.getElementById('quoteBtn');
    if (quoteBtn) {
        quoteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            if (selectedText) {
                insertText('> ' + selectedText);
            } else {
                insertText('> ');
            }
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
    
    // Indent/Outdent Buttons
    const outdentBtn = document.getElementById('outdentBtn');
    if (outdentBtn) {
        outdentBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            if (selectedText) {
                const lines = selectedText.split('\n');
                const outdentedLines = lines.map(line => line.replace(/^    /, '')).join('\n');
                insertText(outdentedLines);
            }
            toggleButtonActive(this);
        });
    }
    
    const indentBtn = document.getElementById('indentBtn');
    if (indentBtn) {
        indentBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedText = getSelectedText();
            if (selectedText) {
                const lines = selectedText.split('\n');
                const indentedLines = lines.map(line => '    ' + line).join('\n');
                insertText(indentedLines);
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
    
    // Table Button
    const tableBtn = document.getElementById('tableBtn');
    if (tableBtn) {
        tableBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const tableMarkdown = `| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Cell 1   | Cell 2   | Cell 3   |
| Cell 4   | Cell 5   | Cell 6   |`;
            insertText(tableMarkdown);
            toggleButtonActive(this);
        });
    }
    
    
    // Preview Button
    const previewBtn = document.getElementById('previewBtn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            togglePreview();
            toggleButtonActive(this);
        });
    }
    
    // More Button (placeholder for future features)
    const moreBtn = document.getElementById('moreBtn');
    if (moreBtn) {
        moreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('More options coming soon!');
            toggleButtonActive(this);
        });
    }
}

// Helper Functions
function getSelectedText() {
    const textarea = document.getElementById('content');
    if (!textarea) return '';
    
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    return textarea.value.substring(start, end);
}

function insertText(text) {
    const textarea = document.getElementById('content');
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
    const textarea = document.getElementById('content');
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

function togglePreview() {
    const contentPreview = document.getElementById('contentPreview');
    const previewContent = document.getElementById('previewContent');
    const contentValue = document.getElementById('content').value;
    
    if (contentPreview.style.display === 'none' || contentPreview.style.display === '') {
        // Show preview
        contentPreview.style.display = 'block';
        previewContent.innerHTML = renderMarkdown(contentValue);
    } else {
        // Hide preview
        contentPreview.style.display = 'none';
    }
}

function renderMarkdown(text) {
    if (!text) return '';
    
    // Convert markdown to HTML
    let html = text
        // Headers
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        // Bold
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Italic
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        // Images
        .replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="img-fluid mb-2" style="max-width: 100%; height: auto;">')
        // Links
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>')
        // Line breaks
        .replace(/\n/g, '<br>')
        // Blockquotes
        .replace(/^> (.*$)/gim, '<blockquote class="blockquote"><p class="mb-0">$1</p></blockquote>')
        // Lists
        .replace(/^\* (.*$)/gim, '<li>$1</li>')
        .replace(/^- (.*$)/gim, '<li>$1</li>')
        .replace(/^\d+\. (.*$)/gim, '<li>$1</li>');
    
    // Wrap list items in ul/ol tags
    html = html.replace(/(<li>.*<\/li>)/gs, function(match) {
        if (match.includes('1.') || match.includes('2.') || match.includes('3.')) {
            return '<ol>' + match + '</ol>';
        } else {
            return '<ul>' + match + '</ul>';
        }
    });
    
    return html;
}

function previewPost() {
    const title = document.getElementById('title').value;
    const contentValue = document.getElementById('content').value;
    const type = document.getElementById('type').value;
    
    if (title && contentValue) {
        const preview = document.getElementById('preview');
        if (preview) {
            const typeLabel = type === 'announcement' ? 'Thông báo' : 'Bài viết';
            
            preview.innerHTML = `
                <div class="mb-2">
                    <span class="badge bg-${type === 'announcement' ? 'danger' : 'primary'}">${typeLabel}</span>
                </div>
                <h5>${title}</h5>
                <div class="text-muted small mb-2">${new Date().toLocaleDateString('vi-VN')}</div>
                <div>${contentValue.substring(0, 200)}${contentValue.length > 200 ? '...' : ''}</div>
            `;
        }
    }
}

// Auto preview when typing - removed because preview element doesn't exist

// Form validation
document.getElementById('postForm').addEventListener('submit', function(e) {
    let isValid = true;
    const errors = [];
    
    // Validate title
    const title = document.getElementById('title');
    if (title.value.trim().length < 10) {
        isValid = false;
        errors.push('Tiêu đề phải có ít nhất 10 ký tự');
        title.classList.add('is-invalid');
    } else if (title.value.trim().length > 255) {
        isValid = false;
        errors.push('Tiêu đề không được vượt quá 255 ký tự');
        title.classList.add('is-invalid');
    } else {
        title.classList.remove('is-invalid');
    }
    
    // Validate content
    const contentElement = document.getElementById('content');
    if (contentElement.value.trim().length < 50) {
        isValid = false;
        errors.push('Nội dung phải có ít nhất 50 ký tự');
        contentElement.classList.add('is-invalid');
    } else if (contentElement.value.trim().length > 50000) {
        isValid = false;
        errors.push('Nội dung không được vượt quá 50,000 ký tự');
        contentElement.classList.add('is-invalid');
    } else {
        contentElement.classList.remove('is-invalid');
    }
    
    // Validate club
    const clubId = document.getElementById('club_id');
    if (!clubId.value) {
        isValid = false;
        errors.push('Vui lòng chọn câu lạc bộ');
        clubId.classList.add('is-invalid');
    } else {
        clubId.classList.remove('is-invalid');
    }
    
    // Validate type
    const type = document.getElementById('type');
    if (!type.value) {
        isValid = false;
        errors.push('Vui lòng chọn loại bài viết');
        type.classList.add('is-invalid');
    } else {
        type.classList.remove('is-invalid');
    }
    
    // Validate status
    const status = document.getElementById('status');
    if (!status.value) {
        isValid = false;
        errors.push('Vui lòng chọn trạng thái');
        status.classList.add('is-invalid');
    } else {
        status.classList.remove('is-invalid');
    }
    
    // Validate inline images in content (base64 images)
    const contentValue = document.getElementById('content').value;
    const base64Images = contentValue.match(/!\[.*?\]\(data:image\/[^)]+\)/g);
    if (base64Images && base64Images.length > 0) {
        // Check if total content size is reasonable (base64 images can be large)
        if (contentValue.length > 100000) { // 100KB limit for content with images
            isValid = false;
            errors.push('Nội dung có quá nhiều ảnh. Vui lòng giảm số lượng ảnh hoặc sử dụng ảnh nhỏ hơn.');
        }
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('Vui lòng kiểm tra lại:\n\n' + errors.join('\n'));
        return false;
    }
    
    return true;
});

// Real-time character count
document.getElementById('title').addEventListener('input', function() {
    const length = this.value.length;
    const formText = this.parentElement.querySelector('.form-text');
    formText.innerHTML = `Tiêu đề: ${length}/255 ký tự`;
    
    if (length < 10) {
        formText.classList.add('text-danger');
        formText.classList.remove('text-success');
    } else {
        formText.classList.remove('text-danger');
        formText.classList.add('text-success');
    }
});

document.getElementById('content').addEventListener('input', function() {
    const length = this.value.length;
    const formText = this.parentElement.querySelector('.form-text');
    formText.innerHTML = `Nội dung: ${length}/50,000 ký tự`;
    
    if (length < 50) {
        formText.classList.add('text-danger');
        formText.classList.remove('text-success');
    } else {
        formText.classList.remove('text-danger');
        formText.classList.add('text-success');
    }
});

</script>

<style>

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
