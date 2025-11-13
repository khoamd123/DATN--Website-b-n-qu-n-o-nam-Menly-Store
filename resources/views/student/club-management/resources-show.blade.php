@extends('layouts.student')

@section('title', 'Chi tiết tài nguyên')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-folder-open text-teal me-2"></i> {{ $resource->title }}
                    </h4>
                    <p class="text-muted mb-0">{{ $club->name ?? 'CLB' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    @php
                        $position = $user->getPositionInClub($clubId);
                        $hasEditPermission = ($resource->user_id == $user->id) 
                            || in_array($position, ['leader', 'vice_president', 'officer'])
                            || $user->hasPermission('quan_ly_clb', $clubId)
                            || $user->hasPermission('dang_thong_bao', $clubId);
                    @endphp
                    @if($hasEditPermission)
                        <a href="{{ route('student.club-management.resources.edit', ['club' => $clubId, 'resource' => $resource->id]) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i> Chỉnh sửa
                        </a>
                        <form action="{{ route('student.club-management.resources.destroy', ['club' => $clubId, 'resource' => $resource->id]) }}" 
                              method="POST" 
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này?');"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Xóa
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <!-- Resource Information -->
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin tài nguyên</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="150"><strong>Tiêu đề:</strong></td>
                                    <td>{{ $resource->title }}</td>
                                </tr>
                                @if($resource->description)
                                <tr>
                                    <td><strong>Mô tả:</strong></td>
                                    <td>{!! nl2br(e($resource->description)) !!}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Người tạo:</strong></td>
                                    <td>{{ $resource->user->name ?? 'Hệ thống' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $resource->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                @if($resource->external_link)
                                <tr>
                                    <td><strong>Link ngoài:</strong></td>
                                    <td>
                                        <a href="{{ $resource->external_link }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-external-link-alt me-1"></i> Mở link
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Image & Video Album -->
                    @if($resource->images && $resource->images->count() > 0)
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-images me-2"></i>Album hình ảnh & video ({{ $resource->images->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($resource->images as $image)
                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <div class="card border" style="cursor: pointer;" 
                                                 data-bs-toggle="modal" data-bs-target="#imageModal{{ $image->id }}">
                                                <div class="card-body p-2">
                                                    @if(str_contains($image->image_type, 'video'))
                                                        <video class="img-fluid rounded" style="height: 200px; width: 100%; object-fit: cover;" muted>
                                                            <source src="{{ $image->image_url }}" type="{{ $image->image_type }}">
                                                        </video>
                                                        <div class="text-center mt-2">
                                                            <i class="fas fa-play-circle fa-2x"></i>
                                                        </div>
                                                    @else
                                                        <img src="{{ $image->thumbnail_url }}" 
                                                             alt="{{ $image->image_name }}" 
                                                             class="img-fluid rounded" 
                                                             style="height: 200px; width: 100%; object-fit: cover;"
                                                             onerror="this.src='{{ $image->image_url }}'; this.onerror=null;">
                                                    @endif
                                                    <div class="mt-2 text-center">
                                                        <small class="text-muted d-block">{{ $image->image_name }}</small>
                                                        <small class="text-muted">{{ $image->formatted_size }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal -->
                                        <div class="modal fade" id="imageModal{{ $image->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ $image->image_name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        @if(str_contains($image->image_type, 'video'))
                                                            <video controls class="img-fluid rounded" style="max-height: 70vh;">
                                                                <source src="{{ asset('storage/' . $image->image_path) }}" type="{{ $image->image_type }}">
                                                            </video>
                                                        @else
                                                            <img src="{{ $image->image_url }}" 
                                                                 alt="{{ $image->image_name }}" 
                                                                 class="img-fluid rounded"
                                                                 onerror="this.src='{{ asset('images/placeholder.jpg') }}'; this.onerror=null;">
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <small class="text-muted">
                                                            Kích thước: {{ $image->formatted_size }} | Loại: {{ $image->image_type }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- File Album -->
                    @if($resource->files && $resource->files->count() > 0)
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file me-2"></i>Album file ({{ $resource->files->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($resource->files as $file)
                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <div class="card border">
                                                <div class="card-body p-3 text-center">
                                                    <i class="{{ $file->file_icon }}" style="font-size: 3rem; margin-bottom: 10px;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">{{ $file->file_name }}</small>
                                                        <small class="text-muted">{{ $file->formatted_size }}</small>
                                                    </div>
                                                    <div class="mt-3">
                                                        <a href="{{ $file->file_url }}" class="btn btn-sm btn-success" download>
                                                            <i class="fas fa-download me-1"></i> Tải xuống
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <!-- Stats -->
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Lượt xem:</strong>
                                <span class="float-end">{{ number_format($resource->view_count ?? 0) }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Số file:</strong>
                                <span class="float-end">{{ $resource->files->count() ?? 0 }}</span>
                            </div>
                            <div class="mb-0">
                                <strong>Số ảnh/video:</strong>
                                <span class="float-end">{{ $resource->images->count() ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

