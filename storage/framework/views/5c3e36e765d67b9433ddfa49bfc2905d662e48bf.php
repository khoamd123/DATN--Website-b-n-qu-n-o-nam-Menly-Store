<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Phân Quyền CLB - UniClubs</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .user-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .club-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
        }
        .test-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-leader { background: #dc3545; color: white; }
        .btn-officer { background: #fd7e14; color: white; }
        .btn-member { background: #28a745; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn:hover { opacity: 0.8; }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .result.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .result.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-admin { background: #dc3545; color: white; }
        .badge-leader { background: #fd7e14; color: white; }
        .badge-officer { background: #ffc107; color: black; }
        .badge-member { background: #28a745; color: white; }
        .badge-none { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Test Hệ Thống Phân Quyền CLB</h1>
            <p>Kiểm tra quyền truy cập các chức năng CLB</p>
        </div>

        <div class="user-info">
            <h3>👤 Thông tin User</h3>
            <p><strong>Tên:</strong> <?php echo e($user->name); ?></p>
            <p><strong>Email:</strong> <?php echo e($user->email); ?></p>
            <p><strong>Vai trò:</strong> 
                <?php if($user->is_admin): ?>
                    <span class="role-badge badge-admin">Admin</span>
                <?php else: ?>
                    <span class="role-badge badge-none">User</span>
                <?php endif; ?>
            </p>
        </div>

        <?php $__currentLoopData = $clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="club-card">
                <h3>🏛️ <?php echo e($club->name); ?></h3>
                <p><strong>Mô tả:</strong> <?php echo e($club->description); ?></p>
                
                <?php
                    $userRole = $clubRoles[$club->id] ?? null;
                ?>
                
                <p><strong>Vai trò của bạn:</strong>
                    <?php if($userRole): ?>
                        <?php if($userRole == 'leader'): ?>
                            <span class="role-badge badge-leader">Trưởng CLB</span>
                        <?php elseif($userRole == 'officer'): ?>
                            <span class="role-badge badge-officer">Cán sự</span>
                        <?php elseif($userRole == 'member'): ?>
                            <span class="role-badge badge-member">Thành viên</span>
                        <?php else: ?>
                            <span class="role-badge badge-none"><?php echo e($userRole); ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="role-badge badge-none">Không phải thành viên</span>
                    <?php endif; ?>
                </p>

                <p><strong>Trưởng CLB:</strong> <?php echo e($club->leader ? $club->leader->name : 'Chưa có'); ?></p>
                <p><strong>Số thành viên:</strong> <?php echo e($club->activeMembers->count()); ?></p>

                <div class="test-buttons">
                    <button class="btn btn-leader" onclick="testPermission('leader', <?php echo e($club->id); ?>)">
                        🔑 Test Quyền Leader
                    </button>
                    <button class="btn btn-officer" onclick="testPermission('officer', <?php echo e($club->id); ?>)">
                        👔 Test Quyền Officer
                    </button>
                    <button class="btn btn-member" onclick="testPermission('member', <?php echo e($club->id); ?>)">
                        👤 Test Quyền Member
                    </button>
                    <button class="btn btn-info" onclick="getClubInfo(<?php echo e($club->id); ?>)">
                        ℹ️ Thông tin CLB
                    </button>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div id="testResult" class="result">
            <h4>📋 Kết quả test:</h4>
            <div id="resultContent"></div>
        </div>
    </div>

    <script>
        function showResult(success, message, role = null, permissions = null) {
            const resultDiv = document.getElementById('testResult');
            const contentDiv = document.getElementById('resultContent');
            
            resultDiv.className = 'result ' + (success ? 'success' : 'error');
            resultDiv.style.display = 'block';
            
            let roleInfo = '';
            if (role) {
                roleInfo = `<br><small><strong>Vai trò hiện tại:</strong> ${role}</small>`;
            }
            
            let permissionsInfo = '';
            if (permissions && permissions.length > 0) {
                permissionsInfo = `<br><small><strong>Quyền có sẵn:</strong> ${permissions.join(', ')}</small>`;
            }
            
            contentDiv.innerHTML = `
                ${success ? '✅' : '❌'} ${message}${roleInfo}${permissionsInfo}
                <br><small>Thời gian: ${new Date().toLocaleTimeString()}</small>
            `;
        }

        function testPermission(permissionType, clubId) {
            fetch(`<?php echo e(url('/club/test')); ?>/${permissionType}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ club_id: clubId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                showResult(data.success, data.message, data.role, data.permissions);
            })
            .catch(error => {
                showResult(false, 'Lỗi: ' + error.message);
            });
        }

        function getClubInfo(clubId) {
            fetch(`<?php echo e(url('/club/info')); ?>/${clubId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showResult(true, `CLB: ${data.club.name}<br>Số thành viên: ${data.members_count}<br>Trưởng CLB: ${data.leader_name}`);
                } else {
                    showResult(false, data.message);
                }
            })
            .catch(error => {
                showResult(false, 'Lỗi: ' + error.message);
            });
        }
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\DATN_Uniclubs\resources\views/club/test-simple.blade.php ENDPATH**/ ?>