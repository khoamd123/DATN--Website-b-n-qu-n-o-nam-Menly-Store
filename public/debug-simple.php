<?php
// Debug đơn giản để kiểm tra vấn đề
echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Simple</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🔧 Debug Simple - Kiểm tra vấn đề</h1>";

try {
    // Kết nối database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=datn_uniclubs', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='alert alert-info'>";
    echo "<h4>1. Kiểm tra cấu trúc bảng events</h4>";
    
    // Kiểm tra cấu trúc bảng
    $stmt = $pdo->query("SHOW COLUMNS FROM events");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasCancellationReason = false;
    $hasCancelledAt = false;
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        if ($column['Field'] === 'cancellation_reason') {
            $hasCancellationReason = true;
        }
        if ($column['Field'] === 'cancelled_at') {
            $hasCancelledAt = true;
        }
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>cancellation_reason:</strong> " . ($hasCancellationReason ? "✅ Có" : "❌ Chưa có") . "</p>";
    echo "<p><strong>cancelled_at:</strong> " . ($hasCancelledAt ? "✅ Có" : "❌ Chưa có") . "</p>";
    echo "</div>";
    
    // Thêm trường nếu chưa có
    if (!$hasCancellationReason) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>2. Thêm trường cancellation_reason</h4>";
        try {
            $pdo->exec("ALTER TABLE events ADD COLUMN cancellation_reason TEXT NULL AFTER status");
            echo "<p>✅ Đã thêm trường cancellation_reason</p>";
        } catch (Exception $e) {
            echo "<p>❌ Lỗi thêm trường: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
    }
    
    if (!$hasCancelledAt) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>3. Thêm trường cancelled_at</h4>";
        try {
            $pdo->exec("ALTER TABLE events ADD COLUMN cancelled_at TIMESTAMP NULL AFTER cancellation_reason");
            echo "<p>✅ Đã thêm trường cancelled_at</p>";
        } catch (Exception $e) {
            echo "<p>❌ Lỗi thêm trường: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
    }
    
    // Kiểm tra sự kiện ID 20
    echo "<div class='alert alert-info'>";
    echo "<h4>4. Kiểm tra sự kiện ID 20</h4>";
    
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([20]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        echo "<p><strong>Title:</strong> {$event['title']}</p>";
        echo "<p><strong>Status:</strong> {$event['status']}</p>";
        echo "<p><strong>Cancellation Reason:</strong> " . ($event['cancellation_reason'] ?? 'NULL') . "</p>";
        echo "<p><strong>Cancelled At:</strong> " . ($event['cancelled_at'] ?? 'NULL') . "</p>";
        
        // Cập nhật sự kiện nếu cần
        if ($event['status'] === 'cancelled' && empty($event['cancellation_reason'])) {
            echo "<p><strong>Đang cập nhật sự kiện...</strong></p>";
            $stmt = $pdo->prepare("UPDATE events SET cancellation_reason = ?, cancelled_at = ? WHERE id = ?");
            $stmt->execute([
                'Sự kiện đã bị hủy bởi quản trị viên',
                $event['updated_at'],
                20
            ]);
            echo "<p>✅ Đã cập nhật sự kiện ID 20</p>";
        }
    } else {
        echo "<p>❌ Không tìm thấy sự kiện ID 20</p>";
    }
    echo "</div>";
    
    // Hiển thị giao diện test
    if ($event && $event['status'] === 'cancelled') {
        echo "<div class='alert alert-success'>";
        echo "<h4>5. Test hiển thị giao diện</h4>";
        
        $reason = $event['cancellation_reason'] ?? 'Sự kiện đã bị hủy bởi quản trị viên';
        
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
        echo "Hủy lúc: " . ($event['cancelled_at'] ?? $event['updated_at']);
        echo "</small>";
        echo "<small class='text-muted'>";
        echo "<i class='fas fa-user me-1'></i>";
        echo "Bởi: Admin";
        echo "</small>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        echo "<p><strong>Nếu bạn thấy khung màu đỏ ở trên, thì CSS và HTML đã hoạt động!</strong></p>";
        echo "</div>";
    }
    
    echo "<div class='alert alert-info'>";
    echo "<h4>6. Bước tiếp theo</h4>";
    echo "<p>Nếu khung màu đỏ hiển thị ở trên, hãy truy cập:</p>";
    echo "<p><a href='../admin/events/20' class='btn btn-primary'>Xem trang thật</a></p>";
    echo "<p>Nếu vẫn không hiển thị, có thể do:</p>";
    echo "<ul>";
    echo "<li>Laravel cache chưa được clear</li>";
    echo "<li>View cache chưa được refresh</li>";
    echo "<li>Có lỗi trong controller hoặc view</li>";
    echo "</ul>";
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










































