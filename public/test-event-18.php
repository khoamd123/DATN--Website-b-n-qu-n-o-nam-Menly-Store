<?php
// Test script để kiểm tra sự kiện ID 18
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
    echo "Kiểm tra sự kiện ID 18...\n";
    
    // Tìm sự kiện ID 18
    $event = Capsule::table('events')->where('id', 18)->first();
    
    if (!$event) {
        echo "❌ Không tìm thấy sự kiện ID 18\n";
        
        // Liệt kê tất cả sự kiện
        $allEvents = Capsule::table('events')->select('id', 'title', 'status')->get();
        echo "Danh sách tất cả sự kiện:\n";
        foreach ($allEvents as $e) {
            echo "- ID: {$e->id}, Title: {$e->title}, Status: {$e->status}\n";
        }
    } else {
        echo "✅ Tìm thấy sự kiện ID 18:\n";
        echo "- Title: {$event->title}\n";
        echo "- Status: {$event->status}\n";
        echo "- Created: {$event->created_at}\n";
        echo "- Updated: {$event->updated_at}\n";
        
        // Kiểm tra các trường mới
        if (property_exists($event, 'cancellation_reason')) {
            echo "- Cancellation Reason: " . ($event->cancellation_reason ?? 'NULL') . "\n";
        } else {
            echo "- Cancellation Reason: Field not exists\n";
        }
        
        if (property_exists($event, 'cancelled_at')) {
            echo "- Cancelled At: " . ($event->cancelled_at ?? 'NULL') . "\n";
        } else {
            echo "- Cancelled At: Field not exists\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}










































