<?php
/**
 * Script để thêm cột type, related_id, related_type vào bảng notifications
 * Chạy script này nếu migration chưa được chạy
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    echo "Đang kiểm tra và thêm cột vào bảng notifications...\n\n";
    
    // Kiểm tra và thêm cột type
    if (!Schema::hasColumn('notifications', 'type')) {
        echo "Thêm cột 'type'...\n";
        DB::statement("ALTER TABLE notifications ADD COLUMN type VARCHAR(50) NULL AFTER sender_id");
        echo "✓ Đã thêm cột 'type'\n";
    } else {
        echo "✓ Cột 'type' đã tồn tại\n";
    }
    
    // Kiểm tra và thêm cột related_id
    if (!Schema::hasColumn('notifications', 'related_id')) {
        echo "Thêm cột 'related_id'...\n";
        DB::statement("ALTER TABLE notifications ADD COLUMN related_id BIGINT UNSIGNED NULL AFTER type");
        echo "✓ Đã thêm cột 'related_id'\n";
    } else {
        echo "✓ Cột 'related_id' đã tồn tại\n";
    }
    
    // Kiểm tra và thêm cột related_type
    if (!Schema::hasColumn('notifications', 'related_type')) {
        echo "Thêm cột 'related_type'...\n";
        DB::statement("ALTER TABLE notifications ADD COLUMN related_type VARCHAR(50) NULL AFTER related_id");
        echo "✓ Đã thêm cột 'related_type'\n";
    } else {
        echo "✓ Cột 'related_type' đã tồn tại\n";
    }
    
    echo "\n✓ Hoàn thành! Tất cả các cột đã được thêm vào bảng notifications.\n";
    
} catch (\Exception $e) {
    echo "✗ Lỗi: " . $e->getMessage() . "\n";
    echo "Chi tiết: " . $e->getTraceAsString() . "\n";
}

