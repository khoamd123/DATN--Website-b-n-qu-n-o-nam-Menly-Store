<?php
// Web script để sửa lỗi trường cancellation
require_once '../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Cấu hình database từ .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'database' => $_ENV['DB_DATABASE'] ?? 'datn_uniclubs',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "<!DOCTYPE html>";
echo "<html><head><title>Fix Cancellation Fields</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🔧 Sửa lỗi trường cancellation</h1>";

try {
    echo "<div class='alert alert-info'>";
    echo "<h4>1. Kiểm tra cấu trúc bảng events...</h4>";
    
    $columns = Capsule::select("SHOW COLUMNS FROM events");
    $hasCancellationReason = false;
    $hasCancelledAt = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'cancellation_reason') {
            $hasCancellationReason = true;
        }
        if ($column->Field === 'cancelled_at') {
            $hasCancelledAt = true;
        }
    }
    
    echo "<p>cancellation_reason: " . ($hasCancellationReason ? "✅ Có" : "❌ Chưa có") . "</p>";
    echo "<p>cancelled_at: " . ($hasCancelledAt ? "✅ Có" : "❌ Chưa có") . "</p>";
    echo "</div>";
    
    // Thêm trường cancellation_reason nếu chưa có
    if (!$hasCancellationReason) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>2. Thêm trường cancellation_reason...</h4>";
        Capsule::statement("ALTER TABLE events ADD COLUMN cancellation_reason TEXT NULL AFTER status");
        echo "<p>✅ Đã thêm trường cancellation_reason</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h4>2. Trường cancellation_reason đã tồn tại</h4>";
        echo "</div>";
    }
    
    // Thêm trường cancelled_at nếu chưa có
    if (!$hasCancelledAt) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>3. Thêm trường cancelled_at...</h4>";
        Capsule::statement("ALTER TABLE events ADD COLUMN cancelled_at TIMESTAMP NULL AFTER cancellation_reason");
        echo "<p>✅ Đã thêm trường cancelled_at</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h4>3. Trường cancelled_at đã tồn tại</h4>";
        echo "</div>";
    }
    
    // Cập nhật các sự kiện đã bị hủy
    echo "<div class='alert alert-info'>";
    echo "<h4>4. Cập nhật các sự kiện đã bị hủy...</h4>";
    $cancelledEvents = Capsule::table('events')
        ->where('status', 'cancelled')
        ->whereNull('cancellation_reason')
        ->get();
    
    echo "<p>Tìm thấy " . $cancelledEvents->count() . " sự kiện bị hủy chưa có lý do</p>";
    
    foreach ($cancelledEvents as $event) {
        Capsule::table('events')
            ->where('id', $event->id)
            ->update([
                'cancellation_reason' => 'Sự kiện đã bị hủy bởi quản trị viên',
                'cancelled_at' => $event->updated_at
            ]);
        echo "<p>✅ Cập nhật sự kiện ID {$event->id}</p>";
    }
    echo "</div>";
    
    // Kiểm tra sự kiện ID 20 cụ thể
    echo "<div class='alert alert-success'>";
    echo "<h4>5. Kiểm tra sự kiện ID 20...</h4>";
    $event20 = Capsule::table('events')->where('id', 20)->first();
    if ($event20) {
        echo "<p><strong>Title:</strong> {$event20->title}</p>";
        echo "<p><strong>Status:</strong> {$event20->status}</p>";
        echo "<p><strong>Cancellation Reason:</strong> " . ($event20->cancellation_reason ?? 'NULL') . "</p>";
        echo "<p><strong>Cancelled At:</strong> " . ($event20->cancelled_at ?? 'NULL') . "</p>";
    } else {
        echo "<p>❌ Không tìm thấy sự kiện ID 20</p>";
    }
    echo "</div>";
    
    echo "<div class='alert alert-success'>";
    echo "<h4>🎉 Hoàn tất!</h4>";
    echo "<p>Bây giờ các sự kiện bị hủy sẽ hiển thị lý do hủy.</p>";
    echo "<p><a href='../admin/events/20' class='btn btn-primary'>Xem sự kiện ID 20</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</body></html>";
?>










































