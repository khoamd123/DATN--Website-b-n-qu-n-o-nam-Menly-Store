@extends('layouts.student')

@section('title', 'Bài viết của tôi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="fas fa-newspaper me-2"></i>Bài viết của tôi</h4>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('student.posts.manage') }}" class="d-flex">
                        <input type="text" class="form-control me-2" name="search" value="{{ request('search') }}" placeholder="Tìm theo tiêu đề...">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    <a href="{{ route('student.posts.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tạo bài viết</a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($posts->count() === 0)
                <div class="text-center py-5">
                    <i class="far fa-newspaper fa-2x text-muted mb-3"></i>
                    <p class="text-muted mb-2">Bạn chưa đăng bài viết nào.</p>
                    <a href="{{ route('student.posts.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tạo bài viết</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tiêu đề</th>
                                <th>CLB</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $post)
                                <tr>
                                    <td>
                                        <a href="{{ route('student.posts.show', $post->id) }}" class="text-decoration-none">{{ $post->title }}</a>
                                    </td>
                                    <td>{{ $post->club->name ?? '—' }}</td>
                                    <td>
                                        @php
                                            $colors = ['published'=>'success','members_only'=>'info','hidden'=>'secondary'];
                                            $labels = ['published'=>'Công khai','members_only'=>'Chỉ thành viên','hidden'=>'Ẩn'];
                                        @endphp
                                        <span class="badge bg-{{ $colors[$post->status] ?? 'secondary' }}">{{ $labels[$post->status] ?? $post->status }}</span>
                                    </td>
                                    <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('student.posts.edit', $post->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('student.posts.delete', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa bài viết này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


