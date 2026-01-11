<?php
// Kiểm tra các trường trong database
echo "<!DOCTYPE html>";
echo "<html><head><title>Check Database Fields</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🔍 Kiểm tra các trường trong database</h1>";

try {
    // Kết nối database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=datn_uniclubs', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='alert alert-info'>";
    echo "<h4>1. Cấu trúc bảng events</h4>";
    
    // Kiểm tra cấu trúc bảng
    $stmt = $pdo->query("SHOW COLUMNS FROM events");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        $highlight = '';
        if (in_array($column['Field'], ['cancellation_reason', 'cancelled_at', 'status', 'description', 'title'])) {
            $highlight = 'class="table-warning"';
        }
        echo "<tr {$highlight}>";
        echo "<td><strong>{$column['Field']}</strong></td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Kiểm tra sự kiện bị hủy
    echo "<div class='alert alert-info'>";
    echo "<h4>2. Sự kiện bị hủy trong database</h4>";
    
    $stmt = $pdo->query("SELECT id, title, status, cancellation_reason, cancelled_at, description, updated_at FROM events WHERE status = 'cancelled' ORDER BY id DESC LIMIT 5");
    $cancelledEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cancelledEvents)) {
        echo "<p>❌ Không có sự kiện nào bị hủy</p>";
    } else {
        echo "<p>✅ Tìm thấy " . count($cancelledEvents) . " sự kiện bị hủy:</p>";
        
        foreach ($cancelledEvents as $event) {
            echo "<div class='card mb-3'>";
            echo "<div class='card-header'>";
            echo "<h5>Sự kiện ID: {$event['id']} - {$event['title']}</h5>";
            echo "</div>";
            echo "<div class='card-body'>";
            echo "<table class='table table-sm'>";
            echo "<tr><th>Trường</th><th>Giá trị</th></tr>";
            echo "<tr><td><strong>status</strong></td><td><span class='badge bg-danger'>{$event['status']}</span></td></tr>";
            echo "<tr><td><strong>cancellation_reason</strong></td><td>" . ($event['cancellation_reason'] ?? 'NULL') . "</td></tr>";
            echo "<tr><td><strong>cancelled_at</strong></td><td>" . ($event['cancelled_at'] ?? 'NULL') . "</td></tr>";
            echo "<tr><td><strong>description</strong></td><td>" . substr($event['description'] ?? 'NULL', 0, 100) . "...</td></tr>";
            echo "<tr><td><strong>updated_at</strong></td><td>{$event['updated_at']}</td></tr>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        }
    }
    echo "</div>";
    
    // Kiểm tra tất cả sự kiện
    echo "<div class='alert alert-info'>";
    echo "<h4>3. Tất cả sự kiện (10 sự kiện gần nhất)</h4>";
    
    $stmt = $pdo->query("SELECT id, title, status, cancellation_reason, cancelled_at, updated_at FROM events ORDER BY id DESC LIMIT 10");
    $allEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table class='table table-sm'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Cancellation Reason</th><th>Cancelled At</th><th>Updated At</th></tr>";
    foreach ($allEvents as $event) {
        $rowClass = $event['status'] === 'cancelled' ? 'table-danger' : '';
        echo "<tr class='{$rowClass}'>";
        echo "<td>{$event['id']}</td>";
        echo "<td>" . substr($event['title'], 0, 30) . "...</td>";
        echo "<td><span class='badge bg-" . ($event['status'] === 'cancelled' ? 'danger' : 'primary') . "'>{$event['status']}</span></td>";
        echo "<td>" . ($event['cancellation_reason'] ?? 'NULL') . "</td>";
        echo "<td>" . ($event['cancelled_at'] ?? 'NULL') . "</td>";
        echo "<td>{$event['updated_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Kiểm tra trường có tồn tại không
    echo "<div class='alert alert-warning'>";
    echo "<h4>4. Kiểm tra trường cancellation_reason</h4>";
    
    $hasCancellationReason = false;
    $hasCancelledAt = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'cancellation_reason') {
            $hasCancellationReason = true;
            echo "<p>✅ Trường <strong>cancellation_reason</strong> tồn tại:</p>";
            echo "<ul>";
            echo "<li>Type: {$column['Type']}</li>";
            echo "<li>Null: {$column['Null']}</li>";
            echo "<li>Default: {$column['Default']}</li>";
            echo "</ul>";
        }
        if ($column['Field'] === 'cancelled_at') {
            $hasCancelledAt = true;
            echo "<p>✅ Trường <strong>cancelled_at</strong> tồn tại:</p>";
            echo "<ul>";
            echo "<li>Type: {$column['Type']}</li>";
            echo "<li>Null: {$column['Null']}</li>";
            echo "<li>Default: {$column['Default']}</li>";
            echo "</ul>";
        }
    }
    
    if (!$hasCancellationReason) {
        echo "<p>❌ Trường <strong>cancellation_reason</strong> chưa tồn tại!</p>";
    }
    if (!$hasCancelledAt) {
        echo "<p>❌ Trường <strong>cancelled_at</strong> chưa tồn tại!</p>";
    }
    echo "</div>";
    
    // Kết luận
    echo "<div class='alert alert-success'>";
    echo "<h4>🎯 Kết luận</h4>";
    echo "<p><strong>Nội dung lý do hủy được lưu ở trường:</strong></p>";
    echo "<ul>";
    echo "<li><strong>cancellation_reason</strong> - Lưu lý do hủy sự kiện (TEXT)</li>";
    echo "<li><strong>cancelled_at</strong> - Lưu thời gian hủy sự kiện (TIMESTAMP)</li>";
    echo "<li><strong>status</strong> - Trạng thái sự kiện (cancelled)</li>";
    echo "</ul>";
    echo "<p>Nếu các trường này chưa tồn tại, cần chạy migration hoặc thêm trường thủ công.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>










































