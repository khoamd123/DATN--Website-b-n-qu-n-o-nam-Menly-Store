<?php
/**
 * Script để thêm cột type, related_id, related_type vào bảng notifications
 * Chạy từ trình duyệt: http://your-domain/fix-notifications-table.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Thêm cột vào bảng notifications</h2>";
echo "<pre>";

try {
    echo "Đang kiểm tra và thêm cột vào bảng notifications...\n\n";
    
    // Kiểm tra và thêm cột type
    if (!Schema::hasColumn('notifications', 'type')) {
        echo "Thêm cột 'type'...\n";
        try {
            DB::statement("ALTER TABLE notifications ADD COLUMN type VARCHAR(50) NULL AFTER sender_id");
            echo "✓ Đã thêm cột 'type'\n";
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "✓ Cột 'type' đã tồn tại\n";
            } else {
                throw $e;
            }
        }
    } else {
        echo "✓ Cột 'type' đã tồn tại\n";
    }
    
    // Kiểm tra và thêm cột related_id
    if (!Schema::hasColumn('notifications', 'related_id')) {
        echo "Thêm cột 'related_id'...\n";
        try {
            DB::statement("ALTER TABLE notifications ADD COLUMN related_id BIGINT UNSIGNED NULL AFTER type");
            echo "✓ Đã thêm cột 'related_id'\n";
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "✓ Cột 'related_id' đã tồn tại\n";
            } else {
                throw $e;
            }
        }
    } else {
        echo "✓ Cột 'related_id' đã tồn tại\n";
    }
    
    // Kiểm tra và thêm cột related_type
    if (!Schema::hasColumn('notifications', 'related_type')) {
        echo "Thêm cột 'related_type'...\n";
        try {
            DB::statement("ALTER TABLE notifications ADD COLUMN related_type VARCHAR(50) NULL AFTER related_id");
            echo "✓ Đã thêm cột 'related_type'\n";
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "✓ Cột 'related_type' đã tồn tại\n";
            } else {
                throw $e;
            }
        }
    } else {
        echo "✓ Cột 'related_type' đã tồn tại\n";
    }
    
    echo "\n✓ Hoàn thành! Tất cả các cột đã được thêm vào bảng notifications.\n";
    
} catch (\Exception $e) {
    echo "✗ Lỗi: " . $e->getMessage() . "\n";
    echo "Chi tiết: " . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<p><a href='javascript:history.back()'>Quay lại</a></p>";

