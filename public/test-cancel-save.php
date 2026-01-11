<?php
// Test kiểm tra việc lưu lý do hủy vào database
echo "<!DOCTYPE html>";
echo "<html><head><title>Test Cancel Save</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🧪 Test Cancel Save - Kiểm tra lưu lý do hủy</h1>";

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
            echo "<p>❌ Lỗi: " . $e->getMessage() . "</p>";
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
            echo "<p>❌ Lỗi: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
    }
    
    // Kiểm tra sự kiện bị hủy
    echo "<div class='alert alert-info'>";
    echo "<h4>4. Kiểm tra sự kiện bị hủy trong database</h4>";
    
    $stmt = $pdo->query("SELECT id, title, status, cancellation_reason, cancelled_at, updated_at FROM events WHERE status = 'cancelled' ORDER BY id DESC LIMIT 10");
    $cancelledEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cancelledEvents)) {
        echo "<p>❌ Không có sự kiện nào bị hủy</p>";
    } else {
        echo "<p>✅ Tìm thấy " . count($cancelledEvents) . " sự kiện bị hủy:</p>";
        echo "<table class='table table-sm'>";
        echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Cancellation Reason</th><th>Cancelled At</th><th>Updated At</th></tr>";
        foreach ($cancelledEvents as $event) {
            echo "<tr>";
            echo "<td>{$event['id']}</td>";
            echo "<td>" . substr($event['title'], 0, 30) . "...</td>";
            echo "<td><span class='badge bg-danger'>{$event['status']}</span></td>";
            echo "<td>" . ($event['cancellation_reason'] ?? 'NULL') . "</td>";
            echo "<td>" . ($event['cancelled_at'] ?? 'NULL') . "</td>";
            echo "<td>{$event['updated_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Test tạo sự kiện hủy mới
    echo "<div class='alert alert-warning'>";
    echo "<h4>5. Test tạo sự kiện hủy mới</h4>";
    
    // Tìm sự kiện có thể hủy
    $stmt = $pdo->query("SELECT id, title, status FROM events WHERE status IN ('pending', 'approved', 'ongoing') LIMIT 1");
    $testEvent = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testEvent) {
        echo "<p><strong>Sự kiện test:</strong> ID {$testEvent['id']} - {$testEvent['title']} ({$testEvent['status']})</p>";
        
        // Hủy sự kiện test
        $testReason = "Test hủy sự kiện - " . date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE events SET status = 'cancelled', cancellation_reason = ?, cancelled_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$testReason, $testEvent['id']]);
        
        if ($result) {
            echo "<p>✅ Đã hủy sự kiện test thành công</p>";
            
            // Kiểm tra lại
            $stmt = $pdo->prepare("SELECT id, title, status, cancellation_reason, cancelled_at FROM events WHERE id = ?");
            $stmt->execute([$testEvent['id']]);
            $updatedEvent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Kết quả:</strong></p>";
            echo "<p>ID: {$updatedEvent['id']}</p>";
            echo "<p>Title: {$updatedEvent['title']}</p>";
            echo "<p>Status: {$updatedEvent['status']}</p>";
            echo "<p>Cancellation Reason: {$updatedEvent['cancellation_reason']}</p>";
            echo "<p>Cancelled At: {$updatedEvent['cancelled_at']}</p>";
            
            if ($updatedEvent['cancellation_reason'] === $testReason) {
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
    
    // Kiểm tra controller logic
    echo "<div class='alert alert-info'>";
    echo "<h4>6. Kiểm tra controller logic</h4>";
    
    // Simulate controller logic
    $eventId = 20; // Hoặc ID sự kiện test
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        echo "<p><strong>Sự kiện ID {$eventId}:</strong></p>";
        echo "<p>Status: {$event['status']}</p>";
        echo "<p>Cancellation Reason: " . ($event['cancellation_reason'] ?? 'NULL') . "</p>";
        echo "<p>Cancelled At: " . ($event['cancelled_at'] ?? 'NULL') . "</p>";
        
        if ($event['status'] === 'cancelled') {
            echo "<p>✅ Sự kiện đã bị hủy</p>";
            if (!empty($event['cancellation_reason'])) {
                echo "<p>✅ Có lý do hủy: {$event['cancellation_reason']}</p>";
            } else {
                echo "<p>❌ Không có lý do hủy</p>";
            }
        } else {
            echo "<p>ℹ️ Sự kiện chưa bị hủy</p>";
        }
    } else {
        echo "<p>❌ Không tìm thấy sự kiện ID {$eventId}</p>";
    }
    echo "</div>";
    
    echo "<div class='alert alert-success'>";
    echo "<h4>🎉 Kết luận</h4>";
    echo "<p>Nếu bạn thấy lý do hủy được lưu chính xác trong database, thì vấn đề có thể là:</p>";
    echo "<ul>";
    echo "<li>View không hiển thị đúng</li>";
    echo "<li>Controller không load dữ liệu đúng</li>";
    echo "<li>CSS không hiển thị</li>";
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










































