<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Tự Động Thay Đổi Vai Trò - UniClubs</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .demo-card {
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
        .permission-item {
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .permission-checked {
            background: #d4edda;
            border-color: #c3e6cb;
        }
        .role-badge {
            font-size: 14px;
            padding: 6px 12px;
        }
        .test-result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .test-success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .test-info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card demo-card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-magic"></i> Demo: Tự Động Thay Đổi Vai Trò</h4>
                        <p class="mb-0">Hệ thống sẽ tự động thay đổi vai trò dựa trên số quyền được cấp</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-user"></i> Chọn User</h5>
                                <select id="userSelect" class="form-select mb-3">
                                    @foreach($users as $user)
                                        @php
                                            $position = $user->getPositionInClub(1);
                                            $permissions = $user->getClubPermissions(1);
                                        @endphp
                                        <option value="{{ $user->id }}" data-position="{{ $position }}" data-permissions="{{ implode(',', $permissions) }}">
                                            {{ $user->name }} ({{ $position }})
                                        </option>
                                    @endforeach
                                </select>
                                
                                <div id="currentInfo" class="alert alert-info">
                                    <strong>Vai trò hiện tại:</strong> <span id="currentRole"></span><br>
                                    <strong>Quyền hiện tại:</strong> <span id="currentPermissions"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5><i class="fas fa-key"></i> Chọn Quyền</h5>
                                @foreach($permissions as $permission)
                                    <div class="permission-item" data-permission-id="{{ $permission->id }}">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   value="{{ $permission->id }}" 
                                                   id="perm_{{ $permission->id }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                <strong>{{ $permission->name }}</strong><br>
                                                <small class="text-muted">{{ $permission->description }}</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <button class="btn btn-success mt-3" onclick="updatePermissions()">
                                    <i class="fas fa-save"></i> Cập Nhật Quyền
                                </button>
                            </div>
                        </div>
                        
                        <div id="testResult" class="test-result" style="display: none;">
                            <h6><i class="fas fa-info-circle"></i> Kết Quả</h6>
                            <div id="resultContent"></div>
                        </div>
                        
                        <div class="mt-4">
                            <h5><i class="fas fa-question-circle"></i> Quy Tắc Tự Động Thay Đổi Vai Trò</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary">Member</h6>
                                            <p class="card-text">Chỉ có quyền <code>xem_bao_cao</code></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title text-warning">Officer (Cán sự)</h6>
                                            <p class="card-text">Có quyền khác ngoài <code>xem_bao_cao</code> (1-4 quyền)</p>
                                            <small class="text-danger"><strong>Giới hạn: Tối đa 3 Officer/CLB</strong></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h6 class="card-title text-success">Leader (Chủ CLB)</h6>
                                            <p class="card-text">Có đủ 5 quyền</p>
                                            <small class="text-danger"><strong>Giới hạn: Chỉ 1 Leader/CLB</strong></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <h6><i class="fas fa-info-circle"></i> Lưu ý:</h6>
                                <ul class="mb-0">
                                    <li><strong>Hủy quyền:</strong> Nếu hủy tất cả quyền khác ngoài <code>xem_bao_cao</code> → tự động thành <strong>Member</strong></li>
                                    <li><strong>Giới hạn Leader:</strong> Nếu có Leader mới → Leader cũ sẽ tự động thành <strong>Officer</strong></li>
                                    <li><strong>Giới hạn Officer:</strong> Nếu đã có 3 Officer → user mới sẽ thành <strong>Member</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentUserId = null;
        let currentClubId = 1;

        document.addEventListener('DOMContentLoaded', function() {
            const userSelect = document.getElementById('userSelect');
            userSelect.addEventListener('change', loadUserInfo);
            loadUserInfo(); // Load thông tin user đầu tiên
        });

        function loadUserInfo() {
            const userSelect = document.getElementById('userSelect');
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            
            currentUserId = selectedOption.value;
            const currentPosition = selectedOption.getAttribute('data-position');
            const currentPermissions = selectedOption.getAttribute('data-permissions');
            
            document.getElementById('currentRole').textContent = currentPosition;
            document.getElementById('currentPermissions').textContent = currentPermissions;
            
            // Reset checkboxes
            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
            
            // Check permissions hiện tại
            if (currentPermissions) {
                const permissions = currentPermissions.split(',');
                permissions.forEach(perm => {
                    const permissionMap = {
                        @foreach($permissions as $permission)
                        '{{ $permission->name }}': {{ $permission->id }},
                        @endforeach
                    };
                    const permissionId = permissionMap[perm];
                    if (permissionId) {
                        const checkbox = document.querySelector(`input[value="${permissionId}"]`);
                        if (checkbox) checkbox.checked = true;
                    }
                });
            }
        }

        function updatePermissions() {
            const selectedPermissions = Array.from(document.querySelectorAll('.permission-checkbox:checked'))
                .map(cb => cb.value);
            
            fetch('{{ url("/admin/permissions/update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    user_id: currentUserId,
                    club_id: currentClubId,
                    permissions: selectedPermissions
                })
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('testResult');
                const contentDiv = document.getElementById('resultContent');
                
                if (data.success) {
                    resultDiv.className = 'test-result test-success';
                    contentDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>✅ Thành công!</strong> ${data.message}
                        </div>
                        <p><strong>Hệ thống đã tự động cập nhật vai trò dựa trên quyền mới.</strong></p>
                        <p>Vui lòng làm mới trang để xem thay đổi.</p>
                    `;
                } else {
                    resultDiv.className = 'test-result test-info';
                    contentDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>❌ Lỗi:</strong> ${data.message}
                        </div>
                    `;
                }
                
                resultDiv.style.display = 'block';
            })
            .catch(error => {
                const resultDiv = document.getElementById('testResult');
                const contentDiv = document.getElementById('resultContent');
                
                resultDiv.className = 'test-result test-info';
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>❌ Lỗi:</strong> ${error.message}
                    </div>
                `;
                resultDiv.style.display = 'block';
            });
        }
    </script>
</body>
</html>
