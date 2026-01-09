@extends('layouts.student')

@section('title', 'Thanh toán thất bại')
@section('page_title', 'Thanh toán thất bại')

@section('content')
<div class="content-card text-center">
    <div class="mb-4">
        <i class="fas fa-times-circle text-danger" style="font-size: 80px;"></i>
    </div>
    
    <h3 class="text-danger mb-3">Thanh toán thất bại!</h3>
    
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
        </div>
    @else
        <p class="text-muted">Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại sau.</p>
    @endif
    
    <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
        <a href="{{ route('payments.history') }}" class="btn btn-primary">
            <i class="fas fa-history me-2"></i>Xem lịch sử thanh toán
        </a>
        <a href="{{ route('payments.create') }}" class="btn btn-success">
            <i class="fas fa-redo me-2"></i>Thử lại
        </a>
        <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Về trang chủ
        </a>
    </div>
</div>
@endsection

