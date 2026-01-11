<?php
// Test script để kiểm tra sự kiện ID 20
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Cấu hình database từ .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
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

try {
    echo "Kiểm tra sự kiện ID 20...\n";
    
    // Tìm sự kiện ID 20
    $event = Capsule::table('events')->where('id', 20)->first();
    
    if (!$event) {
        echo "❌ Không tìm thấy sự kiện ID 20\n";
        
        // Liệt kê tất cả sự kiện
        $allEvents = Capsule::table('events')->select('id', 'title', 'status')->orderBy('id', 'desc')->limit(10)->get();
        echo "10 sự kiện gần nhất:\n";
        foreach ($allEvents as $e) {
            echo "- ID: {$e->id}, Title: {$e->title}, Status: {$e->status}\n";
        }
    } else {
        echo "✅ Tìm thấy sự kiện ID 20:\n";
        echo "- Title: {$event->title}\n";
        echo "- Status: {$event->status}\n";
        echo "- Created: {$event->created_at}\n";
        echo "- Updated: {$event->updated_at}\n";
        
        // Kiểm tra các trường mới
        if (isset($event->cancellation_reason)) {
            echo "- Cancellation Reason: " . ($event->cancellation_reason ?? 'NULL') . "\n";
        } else {
            echo "- Cancellation Reason: Field not exists\n";
        }
        
        if (isset($event->cancelled_at)) {
            echo "- Cancelled At: " . ($event->cancelled_at ?? 'NULL') . "\n";
        } else {
            echo "- Cancelled At: Field not exists\n";
        }
        
        // Nếu sự kiện bị hủy nhưng chưa có lý do, thêm lý do mặc định
        if ($event->status === 'cancelled' && empty($event->cancellation_reason)) {
            echo "\n🔄 Cập nhật lý do hủy mặc định...\n";
            
            $updateData = ['updated_at' => now()];
            
            // Kiểm tra và thêm các trường mới nếu có
            if (isset($event->cancellation_reason)) {
                $updateData['cancellation_reason'] = 'Sự kiện đã bị hủy bởi quản trị viên';
            }
            if (isset($event->cancelled_at)) {
                $updateData['cancelled_at'] = now();
            }
            
            $result = Capsule::table('events')
                ->where('id', 20)
                ->update($updateData);
            
            if ($result) {
                echo "✅ Đã cập nhật lý do hủy mặc định\n";
            } else {
                echo "❌ Không thể cập nhật lý do hủy\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}










































