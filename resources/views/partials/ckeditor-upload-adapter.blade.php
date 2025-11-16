{{-- 
    Helper để tạo Upload Adapter cho CKEditor 5
    Sử dụng: @include('partials.ckeditor-upload-adapter', ['uploadUrl' => route('...'), 'csrfToken' => csrf_token()])
--}}
<script>
    // Custom Upload Adapter cho CKEditor 5 - Dùng chung cho toàn bộ dự án
    (function() {
        if (!window.CKEditorUploadAdapterFactory) {
            window.CKEditorUploadAdapterFactory = function(uploadUrl, csrfToken) {
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
                        xhr.open('POST', uploadUrl, true);
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
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

                return function SimpleUploadAdapterPlugin(editor) {
                    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                        return new MyUploadAdapter(loader);
                    };
                };
            };
        }
    })();
</script>
