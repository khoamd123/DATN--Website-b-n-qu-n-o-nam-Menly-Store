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
                                <th style="width: 50px;">STT</th>
                                <th style="width: 140px;">Ảnh đại diện</th>
                                <th>Tiêu đề</th>
                                <th>CLB</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $index => $post)
                                @php
                                    // Tính STT với pagination
                                    $stt = ($posts->currentPage() - 1) * $posts->perPage() + $index + 1;
                                    
                                    // Lấy ảnh đại diện
                                    $imageUrl = null;
                                    if (!empty($post->image)) {
                                        $imageField = $post->image;
                                        if (\Illuminate\Support\Str::startsWith($imageField, ['http://', 'https://'])) {
                                            $imageUrl = $imageField;
                                        } elseif (\Illuminate\Support\Str::startsWith($imageField, ['/storage/', 'storage/'])) {
                                            $imageUrl = asset(ltrim($imageField, '/'));
                                        } elseif (\Illuminate\Support\Str::startsWith($imageField, ['uploads/', '/uploads/'])) {
                                            $imageUrl = asset(ltrim($imageField, '/'));
                                        } else {
                                            $imageUrl = asset('storage/' . ltrim($imageField, '/'));
                                        }
                                    } elseif (isset($post->attachments) && $post->attachments->count() > 0) {
                                        $firstImageAttachment = $post->attachments->firstWhere('file_type', 'image') ?? $post->attachments->first();
                                        if ($firstImageAttachment && isset($firstImageAttachment->file_url)) {
                                            $fileUrl = $firstImageAttachment->file_url;
                                            if (\Illuminate\Support\Str::startsWith($fileUrl, ['http://', 'https://'])) {
                                                $imageUrl = $fileUrl;
                                            } else {
                                                $imageUrl = asset(ltrim($fileUrl, '/'));
                                            }
                                        }
                                    } elseif (!empty($post->content)) {
                                        if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $post->content, $m)) {
                                            $imgSrc = $m[1] ?? null;
                                            if ($imgSrc) {
                                                if (\Illuminate\Support\Str::startsWith($imgSrc, ['http://', 'https://'])) {
                                                    $imageUrl = $imgSrc;
                                                } else {
                                                    $imageUrl = asset(ltrim($imgSrc, '/'));
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <strong class="text-muted">{{ $stt }}</strong>
                                    </td>
                                    <td>
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" 
                                                 alt="{{ $post->title }}" 
                                                 class="img-thumbnail" 
                                                 style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                                                 onclick="window.location.href='{{ route('student.posts.show', $post->id) }}'"
                                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22120%22 height=%22120%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-family=%22Arial%22 font-size=%2214%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-light rounded" 
                                                 style="width: 120px; height: 120px;">
                                                <i class="far fa-image text-muted" style="font-size: 32px;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.posts.show', $post->id) }}" class="text-decoration-none text-dark fw-bold">
                                            {{ \Illuminate\Support\Str::limit($post->title, 60) }}
                                        </a>
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
                                        <a href="{{ route('student.posts.edit', $post->id) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('student.posts.delete', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa bài viết này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
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


