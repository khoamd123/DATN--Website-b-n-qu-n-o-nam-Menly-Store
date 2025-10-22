@extends('admin.layouts.app')

@section('title', 'Test Menu')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Test Menu Links</h3>
                </div>
                <div class="card-body">
                    <h4>Kiểm tra các link:</h4>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <a href="{{ route('admin.club-resources.index') }}" class="btn btn-primary">
                                <i class="fas fa-folder-open"></i> Tài nguyên CLB
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('admin.club-resources.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Tạo tài nguyên mới
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('admin.posts') }}" class="btn btn-info">
                                <i class="fas fa-newspaper"></i> Bài viết
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('admin.posts.create') }}" class="btn btn-warning">
                                <i class="fas fa-plus"></i> Tạo bài viết mới
                            </a>
                        </li>
                    </ul>
                    
                    <hr>
                    
                    <h4>Kiểm tra routes:</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Club Resources Routes:</h5>
                            <ul>
                                <li>Index: <code>{{ route('admin.club-resources.index') }}</code></li>
                                <li>Create: <code>{{ route('admin.club-resources.create') }}</code></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Posts Routes:</h5>
                            <ul>
                                <li>Index: <code>{{ route('admin.posts') }}</code></li>
                                <li>Create: <code>{{ route('admin.posts.create') }}</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
