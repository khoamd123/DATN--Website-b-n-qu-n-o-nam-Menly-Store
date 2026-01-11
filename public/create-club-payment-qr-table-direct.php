<?php
/**
 * Script tạo bảng club_payment_qrs trực tiếp bằng PDO
 * KHÔNG CẦN Laravel, chỉ cần kết nối database trực tiếp
 * 
 * Truy cập: http://localhost/DATN--Website-b-n-qu-n-o-nam-Menly-Store/public/create-club-payment-qr-table-direct.php
 * 
 * SAU KHI CHẠY XONG, XÓA FILE NÀY ĐỂ BẢO MẬT!
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tạo bảng club_payment_qrs</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
        .success { color: green; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔧 Tạo bảng club_payment_qrs</h1>

<?php
// Cấu hình database - Điều chỉnh theo database của bạn
$host = '127.0.0.1';
$port = '3306';
$database = 'uniclubs'; // Thay đổi theo tên database của bạn
$username = 'root';
$password = ''; // Thay đổi nếu có password

try {
    // Kết nối database
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>✅ Đã kết nối database thành công!</div>";
    
    // 1. Tạo bảng club_payment_qrs
    echo "<div class='info'><strong>Bước 1:</strong> Tạo bảng club_payment_qrs...</div>";
    
    $createTableSql = "
    CREATE TABLE IF NOT EXISTS `club_payment_qrs` (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `club_id` bigint(20) UNSIGNED NOT NULL,
      `payment_method` varchar(255) NOT NULL DEFAULT 'VietQR',
      `account_number` varchar(255) NOT NULL,
      `bank_code` varchar(50) DEFAULT NULL,
      `account_name` varchar(255) DEFAULT NULL,
      `qr_code_data` text DEFAULT NULL,
      `qr_code_image` varchar(255) DEFAULT NULL,
      `is_primary` tinyint(1) NOT NULL DEFAULT 0,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `description` text DEFAULT NULL,
      `created_by` bigint(20) UNSIGNED NOT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      `deleted_at` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `club_payment_qrs_club_id_foreign` (`club_id`),
      KEY `club_payment_qrs_created_by_foreign` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTableSql);
    echo "<div class='success'>✅ Bảng club_payment_qrs đã được tạo thành công!</div>";
    
    // 2. Thêm foreign keys
    echo "<div class='info'><strong>Bước 2:</strong> Thêm foreign keys...</div>";
    
    try {
        $pdo->exec("ALTER TABLE `club_payment_qrs` ADD CONSTRAINT `club_payment_qrs_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE");
        echo "<div class='success'>✅ Foreign key club_id đã được thêm!</div>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate foreign key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "<div class='info'>ℹ️ Foreign key club_id đã tồn tại, bỏ qua.</div>";
        } else {
            echo "<div class='warning'>⚠️ Không thể thêm foreign key club_id (có thể đã tồn tại hoặc bảng clubs chưa có): " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    try {
        $pdo->exec("ALTER TABLE `club_payment_qrs` ADD CONSTRAINT `club_payment_qrs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE");
        echo "<div class='success'>✅ Foreign key created_by đã được thêm!</div>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate foreign key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "<div class='info'>ℹ️ Foreign key created_by đã tồn tại, bỏ qua.</div>";
        } else {
            echo "<div class='warning'>⚠️ Không thể thêm foreign key created_by (có thể đã tồn tại hoặc bảng users chưa có): " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    // 3. Thêm các trường vào fund_transactions
    echo "<div class='info'><strong>Bước 3:</strong> Thêm các trường vào bảng fund_transactions...</div>";
    
    $columns = [
        'payment_method' => "varchar(255) DEFAULT NULL COMMENT 'Phương thức thanh toán'",
        'transaction_code' => "varchar(255) DEFAULT NULL COMMENT 'Mã giao dịch/Số bill'",
        'payer_name' => "varchar(255) DEFAULT NULL COMMENT 'Tên người nộp'",
        'payer_phone' => "varchar(20) DEFAULT NULL COMMENT 'Số điện thoại người nộp'"
    ];
    
    // Lấy danh sách cột hiện có
    $existingColumns = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM `fund_transactions`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
    }
    
    $addedCount = 0;
    foreach ($columns as $columnName => $columnDef) {
        if (in_array($columnName, $existingColumns)) {
            echo "<div class='info'>ℹ️ Cột <code>{$columnName}</code> đã tồn tại, bỏ qua.</div>";
        } else {
            try {
                // Xác định vị trí AFTER
                $afterColumn = 'category';
                if ($columnName === 'transaction_code') $afterColumn = 'payment_method';
                elseif ($columnName === 'payer_name') $afterColumn = 'transaction_code';
                elseif ($columnName === 'payer_phone') $afterColumn = 'payer_name';
                
                // Kiểm tra xem cột AFTER có tồn tại không
                if (!in_array($afterColumn, $existingColumns)) {
                    $afterColumn = ''; // Không dùng AFTER nếu cột không tồn tại
                }
                
                $sql = "ALTER TABLE `fund_transactions` ADD COLUMN `{$columnName}` {$columnDef}";
                if ($afterColumn) {
                    $sql .= " AFTER `{$afterColumn}`";
                }
                
                $pdo->exec($sql);
                echo "<div class='success'>✅ Đã thêm cột <code>{$columnName}</code> vào bảng fund_transactions!</div>";
                $addedCount++;
                $existingColumns[] = $columnName; // Thêm vào danh sách để các cột sau biết
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "<div class='info'>ℹ️ Cột <code>{$columnName}</code> đã tồn tại, bỏ qua.</div>";
                } else {
                    echo "<div class='error'>❌ Lỗi khi thêm cột <code>{$columnName}</code>: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
        }
    }
    
    echo "<div class='success' style='margin-top: 20px; padding: 20px; font-size: 18px; text-align: center;'>";
    echo "<strong>✅ HOÀN TẤT! Tất cả các bảng và cột đã được tạo thành công!</strong><br>";
    echo "<small>Đã tạo bảng club_payment_qrs và thêm {$addedCount} cột mới vào fund_transactions</small>";
    echo "</div>";
    
    echo "<div style='margin-top: 20px; text-align: center;'>";
    echo "<a href='/student/club-management/1/payment-qr' style='display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Thử lại trang quản lý QR code</a>";
    echo "</div>";
    
    echo "<div class='warning' style='margin-top: 20px;'>";
    echo "<strong>⚠️ LƯU Ý BẢO MẬT:</strong><br>";
    echo "Vui lòng <strong>XÓA FILE NÀY</strong> (<code>create-club-payment-qr-table-direct.php</code>) sau khi chạy xong để bảo mật!";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<strong>❌ LỖI KẾT NỐI DATABASE:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
    
    echo "<div class='info' style='margin-top: 20px;'>";
    echo "<h3>Hướng dẫn:</h3>";
    echo "<p>Nếu bạn gặp lỗi kết nối, vui lòng:</p>";
    echo "<ol>";
    echo "<li>Mở file <code>public/create-club-payment-qr-table-direct.php</code></li>";
    echo "<li>Tìm dòng cấu hình database (dòng 20-24)</li>";
    echo "<li>Điều chỉnh thông tin: <code>\$database</code>, <code>\$username</code>, <code>\$password</code> theo database của bạn</li>";
    echo "<li>Refresh lại trang này</li>";
    echo "</ol>";
    echo "<p><strong>Hoặc chạy SQL trực tiếp trong phpMyAdmin:</strong></p>";
    echo "<ol>";
    echo "<li>Mở <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
    echo "<li>Chọn database của bạn</li>";
    echo "<li>Vào tab 'SQL'</li>";
    echo "<li>Copy nội dung file <code>create_club_payment_qrs_table_simple.sql</code> và paste vào</li>";
    echo "<li>Click 'Go' để chạy</li>";
    echo "</ol>";
    echo "</div>";
}
?>
</body>
</html>




