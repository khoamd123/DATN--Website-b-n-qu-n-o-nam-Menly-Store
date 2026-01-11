<?php
/**
 * Script tạo bảng club_payment_qrs trực tiếp bằng SQL
 * CHỈ CHẠY MỘT LẦN, SAU ĐÓ XÓA FILE NÀY
 * 
 * Truy cập: http://localhost/DATN--Website-b-n-qu-n-o-nam-Menly-Store/public/create-tables-club-payment-qr.php
 */

// Load Laravel để lấy DB config
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tạo bảng club_payment_qrs</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Tạo bảng club_payment_qrs</h1>

<?php
try {
    $db = \Illuminate\Support\Facades\DB::connection();
    
    echo "<div class='info'>Đang kết nối database...</div>";
    
    // SQL để tạo bảng club_payment_qrs
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
    
    echo "<div class='info'>Bước 1: Tạo bảng club_payment_qrs...</div>";
    $db->statement($createTableSql);
    echo "<div class='success'>✅ Bảng club_payment_qrs đã được tạo thành công!</div>";
    
    // Thêm foreign keys (nếu chưa có)
    try {
        $db->statement("ALTER TABLE `club_payment_qrs` ADD CONSTRAINT `club_payment_qrs_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE");
        echo "<div class='success'>✅ Foreign key club_id đã được thêm!</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate foreign key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "<div class='info'>ℹ️ Foreign key club_id đã tồn tại, bỏ qua.</div>";
        } else {
            echo "<div class='error'>⚠️ Lỗi khi thêm foreign key club_id: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    try {
        $db->statement("ALTER TABLE `club_payment_qrs` ADD CONSTRAINT `club_payment_qrs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE");
        echo "<div class='success'>✅ Foreign key created_by đã được thêm!</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate foreign key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "<div class='info'>ℹ️ Foreign key created_by đã tồn tại, bỏ qua.</div>";
        } else {
            echo "<div class='error'>⚠️ Lỗi khi thêm foreign key created_by: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    // Kiểm tra và thêm các cột vào fund_transactions
    echo "<div class='info'>Bước 2: Thêm các trường vào bảng fund_transactions...</div>";
    
    $columns = [
        'payment_method' => "varchar(255) DEFAULT NULL COMMENT 'Phương thức thanh toán' AFTER `category`",
        'transaction_code' => "varchar(255) DEFAULT NULL COMMENT 'Mã giao dịch/Số bill' AFTER `payment_method`",
        'payer_name' => "varchar(255) DEFAULT NULL COMMENT 'Tên người nộp' AFTER `transaction_code`",
        'payer_phone' => "varchar(20) DEFAULT NULL COMMENT 'Số điện thoại người nộp' AFTER `payer_name`"
    ];
    
    foreach ($columns as $columnName => $columnDef) {
        try {
            // Kiểm tra xem cột đã tồn tại chưa
            $exists = $db->select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'fund_transactions' 
                AND COLUMN_NAME = ?", [$columnName]);
            
            if ($exists[0]->count > 0) {
                echo "<div class='info'>ℹ️ Cột `{$columnName}` đã tồn tại, bỏ qua.</div>";
            } else {
                $db->statement("ALTER TABLE `fund_transactions` ADD COLUMN `{$columnName}` {$columnDef}");
                echo "<div class='success'>✅ Đã thêm cột `{$columnName}` vào bảng fund_transactions!</div>";
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                echo "<div class='info'>ℹ️ Cột `{$columnName}` đã tồn tại, bỏ qua.</div>";
            } else {
                echo "<div class='error'>⚠️ Lỗi khi thêm cột `{$columnName}`: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
    
    echo "<div class='success' style='margin-top: 20px; padding: 15px; font-size: 16px;'><strong>✅ HOÀN TẤT! Tất cả các bảng đã được tạo thành công!</strong></div>";
    echo "<p><a href='/student/club-management/1/payment-qr' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Thử lại trang quản lý QR code</a></p>";
    echo "<p style='color: red;'><strong>⚠️ LƯU Ý:</strong> Vui lòng xóa file <code>create-tables-club-payment-qr.php</code> sau khi chạy xong để bảo mật!</p>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>❌ LỖI:</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    
    echo "<div class='info' style='margin-top: 20px;'>";
    echo "<h3>Giải pháp thay thế:</h3>";
    echo "<p><strong>Cách 1:</strong> Chạy SQL trong phpMyAdmin</p>";
    echo "<ol>";
    echo "<li>Mở phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Chọn database (thường là <code>uniclubs</code> hoặc tên database của bạn)</li>";
    echo "<li>Click tab 'SQL'</li>";
    echo "<li>Copy và paste nội dung file <code>create_club_payment_qrs_table_simple.sql</code></li>";
    echo "<li>Click 'Go' để chạy</li>";
    echo "</ol>";
    echo "</div>";
}
?>
</body>
</html>

