@extends('admin.layouts.app')

@section('title', 'Tin nhắn - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-envelope me-2"></i>Tin nhắn</h1>
            <p class="text-muted mb-0">Quản lý và xem tất cả tin nhắn hệ thống</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách tin nhắn</h5>
                </div>
                <div class="card-body">
                    @if($messages && $messages->count() > 0)
                        @foreach($messages as $message)
                        <div class="message-item mb-3 p-3 border rounded" style="cursor: pointer;">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="fas fa-envelope fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">{{ $message->title ?? 'Tin nhắn' }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ isset($message->created_at) ? $message->created_at->format('d/m/Y H:i') : 'N/A' }}
                                        </small>
                                    </div>
                                    <p class="text-muted mb-2">{{ $message->content ?? $message->message ?? 'Nội dung tin nhắn' }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            @if(isset($message->sender))
                                                <i class="fas fa-user me-1"></i>Gửi bởi: {{ $message->sender->name ?? 'Hệ thống' }}
                                            @else
                                                <i class="fas fa-user me-1"></i>Hệ thống
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có tin nhắn nào</h5>
                            <p class="text-muted">Tin nhắn mới sẽ xuất hiện ở đây khi có cập nhật.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .message-item {
        transition: all 0.2s ease;
    }
    
    .message-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush
@endsection


