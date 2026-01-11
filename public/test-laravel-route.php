<?php
// Test Laravel route trực tiếp
echo "<!DOCTYPE html>";
echo "<html><head><title>Test Laravel Route</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🧪 Test Laravel Route</h1>";

try {
    // Simulate Laravel request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/admin/events/20';
    $_SERVER['HTTP_HOST'] = '127.0.0.1:8000';
    
    // Start output buffering
    ob_start();
    
    // Include Laravel bootstrap
    require_once '../bootstrap/app.php';
    
    // Get the application
    $app = require_once '../bootstrap/app.php';
    
    // Make request
    $request = \Illuminate\Http\Request::create('/admin/events/20', 'GET');
    
    // Set session data
    session(['logged_in' => true, 'is_admin' => true]);
    
    // Handle request
    $response = $app->handle($request);
    
    // Get response content
    $content = $response->getContent();
    
    // Check if content contains cancellation info
    $hasCancellationInfo = strpos($content, 'Lý do hủy sự kiện') !== false;
    $hasCancellationReason = strpos($content, 'cancellation_reason') !== false;
    $hasCancellationText = strpos($content, 'Sự kiện đã bị hủy bởi quản trị viên') !== false;
    
    echo "<div class='alert alert-info'>";
    echo "<h4>📊 Kết quả kiểm tra:</h4>";
    echo "<p>Có 'Lý do hủy sự kiện': " . ($hasCancellationInfo ? '✅ YES' : '❌ NO') . "</p>";
    echo "<p>Có 'cancellation_reason': " . ($hasCancellationReason ? '✅ YES' : '❌ NO') . "</p>";
    echo "<p>Có lý do mặc định: " . ($hasCancellationText ? '✅ YES' : '❌ NO') . "</p>";
    echo "</div>";
    
    if ($hasCancellationInfo) {
        echo "<div class='alert alert-success'>";
        echo "<h4>✅ Thành công!</h4>";
        echo "<p>Tính năng hiển thị lý do hủy đã hoạt động.</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<h4>❌ Thất bại!</h4>";
        echo "<p>Tính năng hiển thị lý do hủy chưa hoạt động.</p>";
        echo "</div>";
    }
    
    // Show partial content for debugging
    echo "<div class='alert alert-warning'>";
    echo "<h4>🔍 Partial Content (first 1000 chars):</h4>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 1000)) . "...</pre>";
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










































