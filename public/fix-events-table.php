<?php
// Script để thêm cột cancellation_reason và cancelled_at vào bảng events

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Kiểm tra xem cột đã tồn tại chưa
    $columns = DB::select("SHOW COLUMNS FROM events");
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('cancellation_reason', $columnNames)) {
        // Thêm cột cancellation_reason
        DB::statement("ALTER TABLE events ADD COLUMN cancellation_reason TEXT NULL AFTER status");
        echo "Đã thêm cột cancellation_reason\n";
    } else {
        echo "Cột cancellation_reason đã tồn tại\n";
    }
    
    if (!in_array('cancelled_at', $columnNames)) {
        // Thêm cột cancelled_at
        DB::statement("ALTER TABLE events ADD COLUMN cancelled_at TIMESTAMP NULL AFTER cancellation_reason");
        echo "Đã thêm cột cancelled_at\n";
    } else {
        echo "Cột cancelled_at đã tồn tại\n";
    }
    
    echo "\nHoàn thành! Các cột đã được thêm vào bảng events.\n";
    
} catch (\Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
