<?php
/**
 * Script test để kiểm tra tạo thông báo
 * Truy cập: http://127.0.0.1:8000/test-notification.php
 */

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    // Kiểm tra bảng notifications
    echo "<h2>Kiểm tra bảng notifications</h2>";
    
    $columns = DB::select("SHOW COLUMNS FROM notifications");
    echo "<h3>Các cột trong bảng:</h3><ul>";
    foreach ($columns as $column) {
        echo "<li>{$column->Field} ({$column->Type})</li>";
    }
    echo "</ul>";
    
    // Kiểm tra thông báo hiện có
    echo "<h3>Thông báo hiện có:</h3>";
    $notifications = DB::table('notifications')->orderBy('created_at', 'desc')->limit(10)->get();
    echo "<p>Số lượng: " . $notifications->count() . "</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Type</th><th>Title</th><th>Read At</th><th>Created At</th></tr>";
    foreach ($notifications as $notif) {
        echo "<tr>";
        echo "<td>{$notif->id}</td>";
        echo "<td>" . (isset($notif->type) ? $notif->type : 'N/A') . "</td>";
        echo "<td>" . substr(isset($notif->title) ? $notif->title : 'N/A', 0, 50) . "</td>";
        echo "<td>" . (isset($notif->read_at) ? $notif->read_at : 'Chưa đọc') . "</td>";
        echo "<td>{$notif->created_at}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Thử tạo thông báo test
    echo "<h3>Thử tạo thông báo test:</h3>";
    try {
        $testNotification = DB::table('notifications')->insert([
            'sender_id' => 1, // Giả sử user ID 1 tồn tại
            'type' => 'event_created',
            'title' => 'Test thông báo',
            'message' => 'Đây là thông báo test để kiểm tra hệ thống.',
            'related_id' => null,
            'related_type' => null,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($testNotification) {
            echo "<p style='color: green;'>✓ Tạo thông báo test thành công!</p>";
        } else {
            echo "<p style='color: red;'>✗ Không thể tạo thông báo test</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Lỗi khi tạo thông báo test: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}















