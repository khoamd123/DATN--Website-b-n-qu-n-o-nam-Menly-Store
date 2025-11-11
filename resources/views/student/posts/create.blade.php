@extends('layouts.student')

@section('title', 'Tạo bài viết')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="fas fa-plus me-2"></i>Tạo bài viết</h4>
                <a href="{{ route('student.posts') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại</a>
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

            <form method="POST" action="{{ route('student.posts.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="10">{{ old('content') }}</textarea>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header"><strong>Cài đặt</strong></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                                        <option value="">Chọn CLB</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>{{ $club->name }}</option>
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
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Đăng bài</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var contentTextarea = document.querySelector('textarea[name="content"]');
        var form = document.querySelector('form[action="{{ route('student.posts.store') }}"]');
        var editorInstance = null;
        if (contentTextarea) {
            ClassicEditor.create(contentTextarea, {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'blockQuote', 'insertTable', '|',
                        'undo', 'redo'
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
                        alert('Vui lòng nhập nội dung bài viết.');
                    }
                }
            });
        }
    });
    </script>
@endpush
