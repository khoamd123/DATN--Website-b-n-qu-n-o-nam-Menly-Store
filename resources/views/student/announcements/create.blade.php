@extends('layouts.student')

@section('title', 'Tạo thông báo')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="fas fa-bullhorn me-2 text-warning"></i>Tạo thông báo</h4>
                <a href="{{ route('student.club-management.posts', ['club' => request('club_id', $user->clubs->first()->id ?? 1)]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại</a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('student.announcements.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="announcement">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required placeholder="Nhập tiêu đề thông báo...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="10" placeholder="Nhập nội dung thông báo...">{{ old('content') }}</textarea>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark"><strong><i class="fas fa-cog me-1"></i>Cài đặt</strong></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                                        <option value="">Chọn CLB</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ (old('club_id', request('club_id')) == $club->id) ? 'selected' : '' }}>{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select" name="status" required>
                                        <option value="published" {{ old('status','published')=='published'?'selected':'' }}>Công khai</option>
                                        <option value="members_only" {{ old('status')=='members_only'?'selected':'' }}>Chỉ thành viên CLB</option>
                                        <option value="hidden" {{ old('status')=='hidden'?'selected':'' }}>Ẩn</option>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Ảnh đại diện</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <small class="text-muted">Chọn ảnh đại diện cho thông báo (tùy chọn)</small>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i> Đăng thông báo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.ckeditor-upload-adapter', ['uploadUrl' => route('student.posts.upload-image'), 'csrfToken' => csrf_token()])
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    // Custom Upload Adapter cho CKEditor 5
    class MyUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return this.loader.file
                .then(file => new Promise((resolve, reject) => {
                    this._initRequest();
                    this._initListeners(resolve, reject, file);
                    this._sendRequest(file);
                }));
        }

        abort() {
            if (this.xhr) {
                this.xhr.abort();
            }
        }

        _initRequest() {
            const xhr = this.xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("student.posts.upload-image") }}', true);
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            xhr.responseType = 'json';
        }

        _initListeners(resolve, reject, file) {
            const xhr = this.xhr;
            const loader = this.loader;
            const genericErrorText = 'Không thể upload file: ' + file.name + '.';

            xhr.addEventListener('error', () => reject(genericErrorText));
            xhr.addEventListener('abort', () => reject());
            xhr.addEventListener('load', () => {
                const response = xhr.response;

                if (!response || response.error) {
                    return reject(response && response.error ? response.error.message : genericErrorText);
                }

                resolve({
                    default: response.url
                });
            });

            if (xhr.upload) {
                xhr.upload.addEventListener('progress', evt => {
                    if (evt.lengthComputable) {
                        loader.uploadTotal = evt.total;
                        loader.uploaded = evt.loaded;
                    }
                });
            }
        }

        _sendRequest(file) {
            const data = new FormData();
            data.append('image', file);
            this.xhr.send(data);
        }
    }

    function SimpleUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new MyUploadAdapter(loader);
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        var contentTextarea = document.querySelector('textarea[name="content"]');
        var form = document.querySelector('form[action="{{ route('student.announcements.store') }}"]');
        var editorInstance = null;
        if (contentTextarea) {
            ClassicEditor.create(contentTextarea, {
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
                }
            }).then(function (editor) {
                editorInstance = editor;
            }).catch(function (error) {
                console.error(error);
            });
        }
        if (form) {
            form.addEventListener('submit', function (e) {
                if (editorInstance) {
                    var textContent = editorInstance.getData().replace(/<[^>]*>/g, '').trim();
                    if (!textContent) {
                        e.preventDefault();
                        alert('Vui lòng nhập nội dung thông báo.');
                    }
                }
            });
        }
    });
</script>
@endpush
