<?php
// Test lưu trực tiếp vào database
echo "<!DOCTYPE html>";
echo "<html><head><title>Test Direct Save</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🧪 Test Direct Save - Test lưu trực tiếp</h1>";

try {
    // Kết nối database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=datn_uniclubs', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='alert alert-info'>";
    echo "<h4>1. Kiểm tra cấu trúc bảng</h4>";
    
    // Kiểm tra cấu trúc bảng
    $stmt = $pdo->query("SHOW COLUMNS FROM events");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasCancellationReason = false;
    $hasCancelledAt = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'cancellation_reason') {
            $hasCancellationReason = true;
        }
        if ($column['Field'] === 'cancelled_at') {
            $hasCancelledAt = true;
        }
    }
    
    echo "<p>cancellation_reason: " . ($hasCancellationReason ? "✅ Có" : "❌ Chưa có") . "</p>";
    echo "<p>cancelled_at: " . ($hasCancelledAt ? "✅ Có" : "❌ Chưa có") . "</p>";
    echo "</div>";
    
    // Thêm trường nếu chưa có
    if (!$hasCancellationReason) {
        $pdo->exec("ALTER TABLE events ADD COLUMN cancellation_reason TEXT NULL AFTER status");
        echo "<div class='alert alert-success'>✅ Đã thêm trường cancellation_reason</div>";
        $hasCancellationReason = true;
    }
    
    if (!$hasCancelledAt) {
        $pdo->exec("ALTER TABLE events ADD COLUMN cancelled_at TIMESTAMP NULL AFTER cancellation_reason");
        echo "<div class='alert alert-success'>✅ Đã thêm trường cancelled_at</div>";
        $hasCancelledAt = true;
    }
    
    // Tìm sự kiện để test
    $stmt = $pdo->query("SELECT id, title, status FROM events WHERE status IN ('pending', 'approved', 'ongoing') LIMIT 1");
    $testEvent = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$testEvent) {
        echo "<div class='alert alert-warning'>Không tìm thấy sự kiện để test. Tạo sự kiện test...</div>";
        
        // Tạo sự kiện test
        $stmt = $pdo->prepare("INSERT INTO events (title, description, status, start_time, end_time, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            'Test Event for Direct Save',
            'This is a test event for direct save testing',
            'pending',
            date('Y-m-d H:i:s', strtotime('+1 day')),
            date('Y-m-d H:i:s', strtotime('+2 days'))
        ]);
        
        $testEventId = $pdo->lastInsertId();
        echo "<div class='alert alert-success'>✅ Đã tạo sự kiện test ID: {$testEventId}</div>";
        
        $stmt = $pdo->prepare("SELECT id, title, status FROM events WHERE id = ?");
        $stmt->execute([$testEventId]);
        $testEvent = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo "<div class='alert alert-info'>";
    echo "<h4>2. Sự kiện test</h4>";
    echo "<p><strong>ID:</strong> {$testEvent['id']}</p>";
    echo "<p><strong>Title:</strong> {$testEvent['title']}</p>";
    echo "<p><strong>Status:</strong> {$testEvent['status']}</p>";
    echo "</div>";
    
    // Test lưu trực tiếp
    echo "<div class='alert alert-warning'>";
    echo "<h4>3. Test lưu trực tiếp vào database</h4>";
    
    $cancellationReason = "Test lưu trực tiếp - " . date('Y-m-d H:i:s');
    $cancelledAt = date('Y-m-d H:i:s');
    
    // Lưu trực tiếp
    $stmt = $pdo->prepare("UPDATE events SET status = 'cancelled', cancellation_reason = ?, cancelled_at = ? WHERE id = ?");
    $result = $stmt->execute([$cancellationReason, $cancelledAt, $testEvent['id']]);
    
    if ($result) {
        echo "<p>✅ Đã lưu trực tiếp thành công</p>";
        
        // Kiểm tra kết quả
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$testEvent['id']]);
        $updatedEvent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Kết quả sau khi lưu:</strong></p>";
        echo "<p>Status: {$updatedEvent['status']}</p>";
        echo "<p>Cancellation Reason: {$updatedEvent['cancellation_reason']}</p>";
        echo "<p>Cancelled At: {$updatedEvent['cancelled_at']}</p>";
        
        if ($updatedEvent['status'] === 'cancelled' && $updatedEvent['cancellation_reason'] === $cancellationReason) {
            echo "<p>✅ Lý do hủy đã được lưu chính xác!</p>";
        } else {
            echo "<p>❌ Lý do hủy không được lưu chính xác!</p>";
        }
    } else {
        echo "<p>❌ Không thể lưu trực tiếp</p>";
    }
    echo "</div>";
    
    // Test hiển thị
    echo "<div class='alert alert-info'>";
    echo "<h4>4. Test hiển thị</h4>";
    
    if ($updatedEvent['status'] === 'cancelled') {
        $reason = $updatedEvent['cancellation_reason'];
        
        // Khung lý do hủy
        echo "<div style='background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%); border-radius: 15px; padding: 0; border: 1px solid #feb2b2; box-shadow: 0 4px 20px rgba(239, 68, 68, 0.1); margin: 1rem 0;'>";
        
        // Header
        echo "<div style='background: linear-gradient(135deg, #f56565, #e53e3e); color: white; padding: 1.25rem 1.5rem; display: flex; align-items: center;'>";
        echo "<div style='width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; font-size: 1.2rem;'>";
        echo "<i class='fas fa-exclamation-triangle'></i>";
        echo "</div>";
        echo "<h5 style='margin: 0; font-size: 1.25rem; font-weight: 600;'>Lý do hủy sự kiện</h5>";
        echo "</div>";
        
        // Content
        echo "<div style='padding: 1.5rem;'>";
        echo "<div style='font-size: 1.1rem; line-height: 1.7; color: #2d3748; margin-bottom: 1rem; text-align: justify; position: relative; padding-left: 1rem; border-left: 4px solid #f56565;'>";
        echo $reason;
        echo "</div>";
        
        // Footer
        echo "<div style='display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid #feb2b2; background: rgba(245, 101, 101, 0.05); margin: 0 -1.5rem -1.5rem -1.5rem; padding: 1rem 1.5rem;'>";
        echo "<small class='text-muted'>";
        echo "<i class='fas fa-clock me-1'></i>";
        echo "Hủy lúc: {$updatedEvent['cancelled_at']}";
        echo "</small>";
        echo "<small class='text-muted'>";
        echo "<i class='fas fa-user me-1'></i>";
        echo "Bởi: Admin";
        echo "</small>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        echo "<p><strong>Nếu bạn thấy khung màu đỏ ở trên, thì tính năng đã hoạt động!</strong></p>";
    }
    echo "</div>";
    
    echo "<div class='alert alert-success'>";
    echo "<h4>🎉 Kết luận</h4>";
    echo "<p>Nếu lý do hủy được lưu vào database và khung màu đỏ hiển thị, thì tính năng đã hoạt động.</p>";
    echo "<p>Nếu vẫn không hiển thị trong trang thật, có thể do:</p>";
    echo "<ul>";
    echo "<li>Laravel cache chưa được clear</li>";
    echo "<li>View không load đúng dữ liệu</li>";
    echo "<li>Có lỗi trong controller hoặc view</li>";
    echo "</ul>";
    echo "<p><a href='../admin/events' class='btn btn-primary'>Xem danh sách sự kiện</a></p>";
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










































