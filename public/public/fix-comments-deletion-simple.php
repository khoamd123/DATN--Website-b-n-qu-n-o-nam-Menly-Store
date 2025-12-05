<?php
// Script đơn giản để thêm cột deletion_reason
// Chạy file này trực tiếp trên browser

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "<!DOCTYPE html>";
echo "<html><head><title>Fix Comments Deletion Reason</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🔧 Thêm cột deletion_reason vào bảng comments</h1>";

try {
    // Thêm cột vào post_comments
    echo "<div class='alert alert-info'>";
    echo "<h4>1. Kiểm tra bảng post_comments...</h4>";
    
    if (!Schema::hasColumn('post_comments', 'deletion_reason')) {
        DB::statement('ALTER TABLE post_comments ADD COLUMN deletion_reason TEXT NULL AFTER status');
        echo "<p>✅ Đã thêm cột deletion_reason vào post_comments</p>";
    } else {
        echo "<p>✅ Cột deletion_reason đã tồn tại trong post_comments</p>";
    }
    
    if (!Schema::hasColumn('post_comments', 'deleted_at')) {
        DB::statement('ALTER TABLE post_comments ADD COLUMN deleted_at TIMESTAMP NULL AFTER deletion_reason');
        echo "<p>✅ Đã thêm cột deleted_at vào post_comments</p>";
    } else {
        echo "<p>✅ Cột deleted_at đã tồn tại trong post_comments</p>";
    }
    echo "</div>";
    
    // Thêm cột vào event_comments
    echo "<div class='alert alert-info'>";
    echo "<h4>2. Kiểm tra bảng event_comments...</h4>";
    
    if (!Schema::hasColumn('event_comments', 'deletion_reason')) {
        DB::statement('ALTER TABLE event_comments ADD COLUMN deletion_reason TEXT NULL AFTER status');
        echo "<p>✅ Đã thêm cột deletion_reason vào event_comments</p>";
    } else {
        echo "<p>✅ Cột deletion_reason đã tồn tại trong event_comments</p>";
    }
    
    if (!Schema::hasColumn('event_comments', 'deleted_at')) {
        DB::statement('ALTER TABLE event_comments ADD COLUMN deleted_at TIMESTAMP NULL AFTER deletion_reason');
        echo "<p>✅ Đã thêm cột deleted_at vào event_comments</p>";
    } else {
        echo "<p>✅ Cột deleted_at đã tồn tại trong event_comments</p>";
    }
    echo "</div>";
    
    echo "<div class='alert alert-success mt-4'>";
    echo "<h4>✅ Hoàn thành!</h4>";
    echo "<p>Tất cả các cột đã được thêm vào database thành công.</p>";
    echo "<p><a href='javascript:window.close()' class='btn btn-primary'>Đóng</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</body></html>";
?>

