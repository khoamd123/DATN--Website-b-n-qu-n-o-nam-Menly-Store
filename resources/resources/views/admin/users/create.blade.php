@extends('admin.layouts.app')

@section('title', 'Thêm người dùng mới - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Thêm người dùng mới</h1>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus"></i> Thông tin người dùng</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password" aria-label="Hiện/ẩn mật khẩu">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password_confirmation" aria-label="Hiện/ẩn mật khẩu">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="student_id" class="form-label">
                                Mã sinh viên 
                                <small class="text-muted">(tự động từ email)</small>
                            </label>
                            <input type="text" 
                                   class="form-control bg-light" 
                                   id="student_id" 
                                   name="student_id" 
                                   value="{{ old('student_id') }}"
                                   readonly>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Mã SV sẽ tự động lấy từ email (VD: khoamdph31863 → PH31863)
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" 
                                    id="role" 
                                    name="role" 
                                    required>
                                <option value="">Chọn vai trò</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Người dùng</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_admin" 
                                       name="is_admin" 
                                       value="1" 
                                       {{ old('is_admin') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_admin">
                                    Cấp quyền quản trị viên
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Thêm người dùng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4"></div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        function togglePasswordVisibility(button) {
            var targetSelector = button.getAttribute('data-target');
            var input = document.querySelector(targetSelector);
            if (!input) return;
            var isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            var icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('.toggle-password')) {
                togglePasswordVisibility(e.target.closest('.toggle-password'));
            }
        });

        // Extract student_id from email (similar to user registration)
        var emailInput = document.getElementById('email');
        var studentIdInput = document.getElementById('student_id');

        // Extract student code from email
        // Example: khoamdph31863@fpt.edu.vn -> PH31863
        function extractStudentIdFromEmail(email) {
            if (!email) return '';
            
            // Get username part before @
            var username = email.split('@')[0];
            if (!username) return '';
            
            // Find where numbers start
            var numberMatch = username.match(/\d/);
            if (!numberMatch) return '';
            
            var numberPosition = username.indexOf(numberMatch[0]);
            if (numberPosition <= 0) return '';
            
            // Go back 2-3 characters to include letters before numbers
            // For khoamdph31863, we want "ph31863" not just "31863"
            var startPosition = Math.max(0, numberPosition - 2);
            var studentCode = username.substring(startPosition);
            
            // If the result is too short, try to get more context
            if (studentCode.length < 4) {
                startPosition = Math.max(0, numberPosition - 3);
                studentCode = username.substring(startPosition);
            }
            
            // Return uppercase
            return studentCode.toUpperCase();
        }

        // Auto-add @fpt.edu.vn domain and extract student_id
        function handleEmailInput() {
            if (!emailInput) return;
            
            var value = emailInput.value;
            
            // If user hasn't typed @ yet, auto-add domain on blur
            if (value && value.indexOf('@') === -1) {
                emailInput.value = value + '@fpt.edu.vn';
            }
            
            // Extract student_id from email
            var studentId = extractStudentIdFromEmail(emailInput.value);
            if (studentIdInput && studentId) {
                studentIdInput.value = studentId;
            }
        }

        // Initialize
        if (emailInput) {
            // Set default domain
            emailInput.placeholder = 'vidu@fpt.edu.vn';
            
            // Update student_id as user types
            emailInput.addEventListener('input', handleEmailInput);
            
            // Auto-add domain when leaving the field
            emailInput.addEventListener('blur', handleEmailInput);
        }
    })();
</script>
@endpush
