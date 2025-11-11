@extends('layouts.student')

@section('title', 'Chỉnh sửa bài viết')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa bài viết</h4>
                <a href="{{ route('student.posts.show', $post->id) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại</a>
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

            <form method="POST" action="{{ route('student.posts.update', $post->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title', $post->title) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" rows="10" required>{{ old('content', $post->content) }}</textarea>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header"><strong>Cài đặt</strong></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ (string)old('club_id', $post->club_id) === (string)$club->id ? 'selected' : '' }}>{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select" name="status" required>
                                        <option value="published" {{ old('status', $post->status)=='published'?'selected':'' }}>Công khai</option>
                                        <option value="members_only" {{ old('status', $post->status)=='members_only'?'selected':'' }}>Chỉ thành viên CLB</option>
                                        <option value="hidden" {{ old('status', $post->status)=='hidden'?'selected':'' }}>Ẩn</option>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Ảnh đại diện</label>
                                    <input type="file" class="form-control mb-2" name="image" accept="image/*">
                                    @php
                                        $preview = null;
                                        if (!empty($post->image)) {
                                            if (\Illuminate\Support\Str::startsWith($post->image, ['uploads/','/uploads/'])) {
                                                $preview = asset(ltrim($post->image,'/'));
                                            } else {
                                                $preview = asset('storage/' . ltrim($post->image,'/'));
                                            }
                                        }
                                    @endphp
                                    @if($preview)
                                        <img src="{{ $preview }}" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" id="remove_image" name="remove_image">
                                            <label class="form-check-label" for="remove_image">
                                                Xóa ảnh hiện tại
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Lưu thay đổi</button>
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
            }).catch(function (error) {
                console.error(error);
            });
        }
    });
    </script>
@endpush
