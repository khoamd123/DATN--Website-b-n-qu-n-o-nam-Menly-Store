@extends('admin.layouts.app')

@section('title', 'Test CKEditor')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Test CKEditor</h3>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Nội dung với CKEditor</label>
                            <textarea class="form-control" id="content" name="content" rows="10">
                                <h2>Xin chào!</h2>
                                <p>Đây là <strong>CKEditor</strong> đang hoạt động.</p>
                                <ul>
                                    <li>Tính năng 1</li>
                                    <li>Tính năng 2</li>
                                </ul>
                            </textarea>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="getContent()">
                            Lấy nội dung
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let editor;
    
    ClassicEditor
        .create(document.querySelector('#content'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', '|',
                    'link', 'imageUpload', '|',
                    'undo', 'redo'
                ]
            },
            language: 'vi',
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'imageStyle:full',
                    'imageStyle:side'
                ]
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            }
        })
        .then(editorInstance => {
            editor = editorInstance;
            console.log('CKEditor initialized successfully');
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });
    
    function getContent() {
        if (editor) {
            const content = editor.getData();
            console.log('Content:', content);
            alert('Nội dung đã được lấy! Kiểm tra console để xem.');
        }
    }
</script>
@endsection
