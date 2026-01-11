<?php
// Script để thêm cột deletion_reason và deleted_at vào bảng comments
echo "<!DOCTYPE html>";
echo "<html><head><title>Add Deletion Reason Columns</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🔧 Thêm cột deletion_reason vào bảng comments</h1>";

try {
    // Đọc thông tin database từ .env
    $envFile = __DIR__ . '/../.env';
    $dbHost = '127.0.0.1';
    $dbName = 'uniclubs';
    $dbUser = 'root';
    $dbPass = '';
    
    if (file_exists($envFile)) {
        $env = parse_ini_file($envFile);
        $dbHost = $env['DB_HOST'] ?? '127.0.0.1';
        $dbName = $env['DB_DATABASE'] ?? 'uniclubs';
        $dbUser = $env['DB_USERNAME'] ?? 'root';
        $dbPass = $env['DB_PASSWORD'] ?? '';
    }
    
    // Thử kết nối với database name từ .env
    try {
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div class='alert alert-success'>Kết nối database thành công: <strong>{$dbName}</strong></div>";
    } catch (PDOException $e) {
        // Nếu không kết nối được, thử với datn_uniclubs
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=datn_uniclubs', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div class='alert alert-warning'>Kết nối với database: <strong>datn_uniclubs</strong></div>";
    }
    
    echo "<div class='alert alert-info'>";
    echo "<h4>1. Kiểm tra cấu trúc bảng post_comments...</h4>";
    
    // Kiểm tra các trường hiện có
    $stmt = $pdo->query("SHOW COLUMNS FROM post_comments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasDeletionReason = false;
    $hasDeletedAt = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'deletion_reason') {
            $hasDeletionReason = true;
        }
        if ($column['Field'] === 'deleted_at') {
            $hasDeletedAt = true;
        }
    }
    
    echo "<p>deletion_reason: " . ($hasDeletionReason ? "✅ Đã có" : "❌ Chưa có") . "</p>";
    echo "<p>deleted_at: " . ($hasDeletedAt ? "✅ Đã có" : "❌ Chưa có") . "</p>";
    echo "</div>";
    
    // Thêm trường deletion_reason vào post_comments nếu chưa có
    if (!$hasDeletionReason) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>2. Thêm trường deletion_reason vào post_comments...</h4>";
        $pdo->exec("ALTER TABLE post_comments ADD COLUMN deletion_reason TEXT NULL AFTER status");
        echo "<p>✅ Đã thêm trường deletion_reason vào post_comments</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h4>2. Trường deletion_reason đã tồn tại trong post_comments</h4>";
        echo "</div>";
    }
    
    // Thêm trường deleted_at vào post_comments nếu chưa có
    if (!$hasDeletedAt) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>3. Thêm trường deleted_at vào post_comments...</h4>";
        $pdo->exec("ALTER TABLE post_comments ADD COLUMN deleted_at TIMESTAMP NULL AFTER deletion_reason");
        echo "<p>✅ Đã thêm trường deleted_at vào post_comments</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h4>3. Trường deleted_at đã tồn tại trong post_comments</h4>";
        echo "</div>";
    }
    
    // Kiểm tra event_comments
    echo "<div class='alert alert-info mt-4'>";
    echo "<h4>4. Kiểm tra cấu trúc bảng event_comments...</h4>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM event_comments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasDeletionReasonEvent = false;
    $hasDeletedAtEvent = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'deletion_reason') {
            $hasDeletionReasonEvent = true;
        }
        if ($column['Field'] === 'deleted_at') {
            $hasDeletedAtEvent = true;
        }
    }
    
    echo "<p>deletion_reason: " . ($hasDeletionReasonEvent ? "✅ Đã có" : "❌ Chưa có") . "</p>";
    echo "<p>deleted_at: " . ($hasDeletedAtEvent ? "✅ Đã có" : "❌ Chưa có") . "</p>";
    echo "</div>";
    
    // Thêm trường deletion_reason vào event_comments nếu chưa có
    if (!$hasDeletionReasonEvent) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>5. Thêm trường deletion_reason vào event_comments...</h4>";
        $pdo->exec("ALTER TABLE event_comments ADD COLUMN deletion_reason TEXT NULL AFTER status");
        echo "<p>✅ Đã thêm trường deletion_reason vào event_comments</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h4>5. Trường deletion_reason đã tồn tại trong event_comments</h4>";
        echo "</div>";
    }
    
    // Thêm trường deleted_at vào event_comments nếu chưa có
    if (!$hasDeletedAtEvent) {
        echo "<div class='alert alert-warning'>";
        echo "<h4>6. Thêm trường deleted_at vào event_comments...</h4>";
        $pdo->exec("ALTER TABLE event_comments ADD COLUMN deleted_at TIMESTAMP NULL AFTER deletion_reason");
        echo "<p>✅ Đã thêm trường deleted_at vào event_comments</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h4>6. Trường deleted_at đã tồn tại trong event_comments</h4>";
        echo "</div>";
    }
    
    echo "<div class='alert alert-success mt-4'>";
    echo "<h4>✅ Hoàn thành!</h4>";
    echo "<p>Tất cả các cột đã được thêm vào database thành công.</p>";
    echo "<p><a href='javascript:window.close()' class='btn btn-primary'>Đóng</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</body></html>";
?>

