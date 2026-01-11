<?php
// Sửa lỗi lưu lý do hủy vào database
echo "<!DOCTYPE html>";
echo "<html><head><title>Fix Cancel Save</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🔧 Sửa lỗi lưu lý do hủy vào database</h1>";

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
        echo "<div class='alert alert-warning'>";
        echo "<h4>2. Thêm trường cancellation_reason</h4>";
        try {
            $pdo->exec("ALTER TABLE events ADD COLUMN cancellation_reason TEXT NULL AFTER status");
            echo "<p>✅ Đã thêm trường cancellation_reason</p>";
            $hasCancellationReason = true;
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
            $hasCancelledAt = true;
        } catch (Exception $e) {
            echo "<p>❌ Lỗi thêm trường: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
    }
    
    // Kiểm tra sự kiện bị hủy hiện tại
    echo "<div class='alert alert-info'>";
    echo "<h4>4. Kiểm tra sự kiện bị hủy hiện tại</h4>";
    
    $stmt = $pdo->query("SELECT id, title, status, cancellation_reason, cancelled_at, updated_at FROM events WHERE status = 'cancelled' ORDER BY id DESC LIMIT 5");
    $cancelledEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cancelledEvents)) {
        echo "<p>❌ Không có sự kiện nào bị hủy</p>";
    } else {
        echo "<p>✅ Tìm thấy " . count($cancelledEvents) . " sự kiện bị hủy:</p>";
        
        foreach ($cancelledEvents as $event) {
            echo "<div class='card mb-2'>";
            echo "<div class='card-body'>";
            echo "<h6>ID: {$event['id']} - {$event['title']}</h6>";
            echo "<p><strong>Status:</strong> {$event['status']}</p>";
            echo "<p><strong>Cancellation Reason:</strong> " . ($event['cancellation_reason'] ?? 'NULL') . "</p>";
            echo "<p><strong>Cancelled At:</strong> " . ($event['cancelled_at'] ?? 'NULL') . "</p>";
            echo "<p><strong>Updated At:</strong> {$event['updated_at']}</p>";
            echo "</div>";
            echo "</div>";
        }
    }
    echo "</div>";
    
    // Cập nhật sự kiện bị hủy chưa có lý do
    if (!empty($cancelledEvents)) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>5. Cập nhật sự kiện bị hủy chưa có lý do</h4>";
        
        $updatedCount = 0;
        foreach ($cancelledEvents as $event) {
            if (empty($event['cancellation_reason'])) {
                $reason = "Sự kiện đã bị hủy bởi quản trị viên";
                $cancelledAt = $event['cancelled_at'] ?? $event['updated_at'];
                
                $stmt = $pdo->prepare("UPDATE events SET cancellation_reason = ?, cancelled_at = ? WHERE id = ?");
                $result = $stmt->execute([$reason, $cancelledAt, $event['id']]);
                
                if ($result) {
                    echo "<p>✅ Đã cập nhật sự kiện ID {$event['id']}</p>";
                    $updatedCount++;
                } else {
                    echo "<p>❌ Không thể cập nhật sự kiện ID {$event['id']}</p>";
                }
            }
        }
        
        if ($updatedCount > 0) {
            echo "<p><strong>Tổng cộng đã cập nhật {$updatedCount} sự kiện</strong></p>";
        } else {
            echo "<p>ℹ️ Tất cả sự kiện đã có lý do hủy</p>";
        }
        echo "</div>";
    }
    
    // Test tạo sự kiện hủy mới
    echo "<div class='alert alert-info'>";
    echo "<h4>6. Test tạo sự kiện hủy mới</h4>";
    
    // Tìm sự kiện để test
    $stmt = $pdo->query("SELECT id, title, status FROM events WHERE status IN ('pending', 'approved', 'ongoing') LIMIT 1");
    $testEvent = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testEvent) {
        echo "<p><strong>Sự kiện test:</strong> ID {$testEvent['id']} - {$testEvent['title']} ({$testEvent['status']})</p>";
        
        // Hủy sự kiện test
        $testReason = "Test hủy sự kiện - " . date('Y-m-d H:i:s');
        $testCancelledAt = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("UPDATE events SET status = 'cancelled', cancellation_reason = ?, cancelled_at = ? WHERE id = ?");
        $result = $stmt->execute([$testReason, $testCancelledAt, $testEvent['id']]);
        
        if ($result) {
            echo "<p>✅ Đã hủy sự kiện test thành công</p>";
            
            // Kiểm tra kết quả
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$testEvent['id']]);
            $updatedEvent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Kết quả sau khi hủy:</strong></p>";
            echo "<p>Status: {$updatedEvent['status']}</p>";
            echo "<p>Cancellation Reason: {$updatedEvent['cancellation_reason']}</p>";
            echo "<p>Cancelled At: {$updatedEvent['cancelled_at']}</p>";
            
            if ($updatedEvent['status'] === 'cancelled' && $updatedEvent['cancellation_reason'] === $testReason) {
                echo "<p>✅ Lý do hủy đã được lưu chính xác!</p>";
            } else {
                echo "<p>❌ Lý do hủy không được lưu chính xác!</p>";
            }
        } else {
            echo "<p>❌ Không thể hủy sự kiện test</p>";
        }
    } else {
        echo "<p>❌ Không tìm thấy sự kiện nào để test</p>";
    }
    echo "</div>";
    
    // Kiểm tra lại tất cả sự kiện bị hủy
    echo "<div class='alert alert-success'>";
    echo "<h4>7. Kiểm tra lại tất cả sự kiện bị hủy</h4>";
    
    $stmt = $pdo->query("SELECT id, title, status, cancellation_reason, cancelled_at FROM events WHERE status = 'cancelled' ORDER BY id DESC");
    $allCancelledEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Tổng cộng có " . count($allCancelledEvents) . " sự kiện bị hủy:</p>";
    
    foreach ($allCancelledEvents as $event) {
        $hasReason = !empty($event['cancellation_reason']);
        $hasCancelledAt = !empty($event['cancelled_at']);
        
        echo "<div class='card mb-2'>";
        echo "<div class='card-body'>";
        echo "<h6>ID: {$event['id']} - {$event['title']}</h6>";
        echo "<p><strong>Status:</strong> <span class='badge bg-danger'>{$event['status']}</span></p>";
        echo "<p><strong>Cancellation Reason:</strong> " . ($hasReason ? "✅ {$event['cancellation_reason']}" : "❌ NULL") . "</p>";
        echo "<p><strong>Cancelled At:</strong> " . ($hasCancelledAt ? "✅ {$event['cancelled_at']}" : "❌ NULL") . "</p>";
        echo "</div>";
        echo "</div>";
    }
    echo "</div>";
    
    echo "<div class='alert alert-success'>";
    echo "<h4>🎉 Hoàn tất!</h4>";
    echo "<p>Nếu bạn thấy lý do hủy được lưu trong database, thì vấn đề đã được giải quyết.</p>";
    echo "<p>Bây giờ hãy thử hủy sự kiện mới và kiểm tra xem lý do có được lưu không.</p>";
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










































