<?php
// Script để thêm cột media_type vào bảng event_images

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Kiểm tra xem cột đã tồn tại chưa
    $columns = DB::select("SHOW COLUMNS FROM event_images");
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('media_type', $columnNames)) {
        // Thêm cột media_type
        DB::statement("ALTER TABLE event_images ADD COLUMN media_type VARCHAR(20) NULL DEFAULT 'image' AFTER image_path");
        echo "Đã thêm cột media_type\n";
    } else {
        echo "Cột media_type đã tồn tại\n";
    }
    
    echo "\nHoàn thành! Cột đã được thêm vào bảng event_images.\n";
    
} catch (\Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}









































