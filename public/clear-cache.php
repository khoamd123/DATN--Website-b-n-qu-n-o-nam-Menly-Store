<?php
// Clear Laravel cache
echo "<!DOCTYPE html>";
echo "<html><head><title>Clear Cache</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🧹 Clear Laravel Cache</h1>";

try {
    // Clear view cache
    $viewCachePath = '../storage/framework/views';
    if (is_dir($viewCachePath)) {
        $files = glob($viewCachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "<div class='alert alert-success'>✅ Đã clear view cache</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ Không tìm thấy view cache directory</div>";
    }
    
    // Clear config cache
    $configCachePath = '../bootstrap/cache/config.php';
    if (file_exists($configCachePath)) {
        unlink($configCachePath);
        echo "<div class='alert alert-success'>✅ Đã clear config cache</div>";
    } else {
        echo "<div class='alert alert-info'>ℹ️ Không có config cache</div>";
    }
    
    // Clear route cache
    $routeCachePath = '../bootstrap/cache/routes.php';
    if (file_exists($routeCachePath)) {
        unlink($routeCachePath);
        echo "<div class='alert alert-success'>✅ Đã clear route cache</div>";
    } else {
        echo "<div class='alert alert-info'>ℹ️ Không có route cache</div>";
    }
    
    echo "<div class='alert alert-success'>";
    echo "<h4>🎉 Hoàn tất!</h4>";
    echo "<p>Cache đã được clear. Bây giờ hãy thử truy cập lại trang sự kiện.</p>";
    echo "<p><a href='../admin/events/20' class='btn btn-primary'>Xem sự kiện ID 20</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>










































