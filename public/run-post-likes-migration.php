<?php
/**
 * Script to create post_likes table
 * Access via: http://127.0.0.1:8000/run-post-likes-migration.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "<h2>Đang tạo bảng post_likes...</h2>";
    echo "<pre>";
    
    // Run the specific migration
    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_01_13_000001_create_post_likes_table.php',
        '--force' => true
    ]);
    
    echo Artisan::output();
    echo "\n✅ Hoàn tất! Bảng post_likes đã được tạo thành công.";
    echo "</pre>";
    
    echo "<p><a href='/student/posts'>Quay lại trang bài viết</a></p>";
    
} catch (\Exception $e) {
    echo "<pre style='color: red;'>";
    echo "❌ Lỗi: " . $e->getMessage();
    echo "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
    
    echo "<h3>Hoặc bạn có thể chạy SQL trực tiếp trong phpMyAdmin:</h3>";
    echo "<pre>";
    echo file_get_contents(__DIR__ . '/../create_post_likes_table.sql');
    echo "</pre>";
}


