<?php
/**
 * Script để thêm cột visibility vào bảng events
 * Chạy script này bằng cách truy cập: http://your-domain/add-visibility-column.php
 */

// Kết nối database (điều chỉnh theo cấu hình của bạn)
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Kiểm tra xem cột đã tồn tại chưa
    $columns = DB::select("SHOW COLUMNS FROM events");
    $columnNames = array_column($columns, 'Field');
    
    if (in_array('visibility', $columnNames)) {
        echo "<h2 style='color: green;'>✓ Cột 'visibility' đã tồn tại trong bảng events!</h2>";
    } else {
        // Thêm cột visibility
        DB::statement("ALTER TABLE events ADD COLUMN visibility ENUM('public', 'internal') DEFAULT 'public' AFTER status");
        
        // Cập nhật tất cả các sự kiện hiện có thành 'public' nếu visibility là NULL
        DB::statement("UPDATE events SET visibility = 'public' WHERE visibility IS NULL");
        
        echo "<h2 style='color: green;'>✓ Đã thêm cột 'visibility' vào bảng events thành công!</h2>";
        echo "<p>Tất cả các sự kiện hiện có đã được cập nhật thành 'public'.</p>";
    }
    
    // Hiển thị thông tin cột
    $columns = DB::select("SHOW COLUMNS FROM events WHERE Field = 'visibility'");
    if (!empty($columns)) {
        echo "<h3>Thông tin cột visibility:</h3>";
        echo "<pre>";
        print_r($columns[0]);
        echo "</pre>";
    }
    
} catch (\Exception $e) {
    echo "<h2 style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

