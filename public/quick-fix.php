<?php
// Script sửa lỗi nhanh
echo "<!DOCTYPE html>";
echo "<html><head><title>Quick Fix</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🔧 Quick Fix - Sửa lỗi cancellation</h1>";

try {
    // Kết nối database trực tiếp
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=datn_uniclubs', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='alert alert-info'>";
    echo "<h4>1. Kiểm tra cấu trúc bảng events...</h4>";
    
    // Kiểm tra các trường hiện có
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
    
    // Thêm trường cancellation_reason nếu chưa có
    if (!$hasCancellationReason) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>2. Thêm trường cancellation_reason...</h4>";
        $pdo->exec("ALTER TABLE events ADD COLUMN cancellation_reason TEXT NULL AFTER status");
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
        $pdo->exec("ALTER TABLE events ADD COLUMN cancelled_at TIMESTAMP NULL AFTER cancellation_reason");
        echo "<p>✅ Đã thêm trường cancelled_at</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h4>3. Trường cancelled_at đã tồn tại</h4>";
        echo "</div>";
    }
    
    // Cập nhật sự kiện ID 20 cụ thể
    echo "<div class='alert alert-info'>";
    echo "<h4>4. Cập nhật sự kiện ID 20...</h4>";
    
    // Kiểm tra sự kiện ID 20
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([20]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        echo "<p><strong>Trước khi cập nhật:</strong></p>";
        echo "<p>Title: {$event['title']}</p>";
        echo "<p>Status: {$event['status']}</p>";
        echo "<p>Cancellation Reason: " . ($event['cancellation_reason'] ?? 'NULL') . "</p>";
        echo "<p>Cancelled At: " . ($event['cancelled_at'] ?? 'NULL') . "</p>";
        
        // Cập nhật sự kiện ID 20
        $stmt = $pdo->prepare("UPDATE events SET cancellation_reason = ?, cancelled_at = ? WHERE id = ?");
        $stmt->execute([
            'Sự kiện đã bị hủy bởi quản trị viên',
            $event['updated_at'],
            20
        ]);
        
        echo "<p>✅ Đã cập nhật sự kiện ID 20</p>";
        
        // Kiểm tra lại
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([20]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Sau khi cập nhật:</strong></p>";
        echo "<p>Title: {$event['title']}</p>";
        echo "<p>Status: {$event['status']}</p>";
        echo "<p>Cancellation Reason: " . ($event['cancellation_reason'] ?? 'NULL') . "</p>";
        echo "<p>Cancelled At: " . ($event['cancelled_at'] ?? 'NULL') . "</p>";
        
    } else {
        echo "<p>❌ Không tìm thấy sự kiện ID 20</p>";
    }
    echo "</div>";
    
    // Cập nhật tất cả sự kiện bị hủy khác
    echo "<div class='alert alert-info'>";
    echo "<h4>5. Cập nhật tất cả sự kiện bị hủy khác...</h4>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE status = 'cancelled' AND cancellation_reason IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    
    echo "<p>Tìm thấy $count sự kiện bị hủy chưa có lý do</p>";
    
    if ($count > 0) {
        $stmt = $pdo->prepare("UPDATE events SET cancellation_reason = ?, cancelled_at = updated_at WHERE status = 'cancelled' AND cancellation_reason IS NULL");
        $stmt->execute(['Sự kiện đã bị hủy bởi quản trị viên']);
        echo "<p>✅ Đã cập nhật $count sự kiện bị hủy</p>";
    }
    echo "</div>";
    
    echo "<div class='alert alert-success'>";
    echo "<h4>🎉 Hoàn tất!</h4>";
    echo "<p>Bây giờ các sự kiện bị hủy sẽ hiển thị lý do hủy.</p>";
    echo "<p><a href='../admin/events/20' class='btn btn-primary btn-lg'>Xem sự kiện ID 20</a></p>";
    echo "<p><a href='../admin/events' class='btn btn-secondary'>Xem danh sách sự kiện</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Lỗi database:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>










































