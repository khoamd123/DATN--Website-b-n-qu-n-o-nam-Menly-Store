@extends('layouts.student')

@section('title', 'Chỉnh sửa Hồ sơ - UniClubs')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-edit text-teal me-2"></i> Chỉnh sửa thông tin cá nhân
            </h4>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row align-items-center mb-4">
                    <div class="col-md-3 text-center">
                        <div class="profile-avatar-edit mb-3">
                            @if($user->avatar)
                                <img id="avatar-preview" src="{{ asset($user->avatar) }}" alt="Avatar">
                            @else
                                <span id="avatar-initials">{{ substr($user->name, 0, 1) }}</span>
                                <img id="avatar-preview" src="#" alt="Avatar" style="display: none;">
                            @endif
                        </div>
                        <input type="file" name="avatar" id="avatar" class="d-none" onchange="previewAvatar(event)">
                        <label for="avatar" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-camera me-2"></i> Đổi ảnh
                        </label>
                    </div>
                    <div class="col-md-9">
                        <h3 class="mb-1">{{ $user->name }}</h3>
                        <p class="text-muted mb-0">{{ $user->student_id ?? 'Chưa có mã sinh viên' }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('student.profile.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-2"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .profile-avatar-edit {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #14b8a6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: bold;
        margin: 0 auto;
        overflow: hidden;
    }
    .profile-avatar-edit img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush

@push('scripts')
<script>
    function previewAvatar(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('avatar-preview');
            const initials = document.getElementById('avatar-initials');
            output.src = reader.result;
            output.style.display = 'block';
            if (initials) {
                initials.style.display = 'none';
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endpush