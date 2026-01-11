<?php
/**
 * Script tự động để thêm các cột mới vào bảng events
 * Tự động đọc thông tin database từ .env
 * Truy cập: http://your-domain/add-event-fields-auto.php
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Thêm các trường mới vào bảng events</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>
    body { padding: 20px; background: #f5f5f5; }
    .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
</style>";
echo "</head><body>";
echo "<div class='container'>";
echo "<h1>🔧 Thêm các trường mới vào bảng events</h1>";

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
    
    echo "<div class='alert alert-info'>";
    echo "<strong>Thông tin kết nối:</strong><br>";
    echo "Host: <code>$dbHost</code><br>";
    echo "Database: <code>$dbName</code><br>";
    echo "User: <code>$dbUser</code><br>";
    echo "</div>";
    
    // Thử kết nối với database
    try {
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div class='alert alert-success'>✅ Kết nối database thành công!</div>";
    } catch (PDOException $e) {
        // Nếu không kết nối được, thử với các database name phổ biến
        $commonDbs = ['datn_uniclubs', 'uniclubs', 'laravel', 'homestead'];
        $connected = false;
        
        foreach ($commonDbs as $db) {
            try {
                $pdo = new PDO("mysql:host={$dbHost};dbname={$db};charset=utf8mb4", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dbName = $db;
                echo "<div class='alert alert-warning'>⚠️ Kết nối với database: <strong>{$db}</strong></div>";
                $connected = true;
                break;
            } catch (PDOException $e2) {
                continue;
            }
        }
        
        if (!$connected) {
            throw new Exception("Không thể kết nối database. Lỗi: " . $e->getMessage());
        }
    }
    
    // Danh sách các cột cần thêm
    $fields = [
        'registration_deadline' => "ALTER TABLE events ADD COLUMN registration_deadline DATETIME NULL AFTER end_time",
        'main_organizer' => "ALTER TABLE events ADD COLUMN main_organizer VARCHAR(255) NULL AFTER registration_deadline",
        'organizing_team' => "ALTER TABLE events ADD COLUMN organizing_team TEXT NULL AFTER main_organizer",
        'co_organizers' => "ALTER TABLE events ADD COLUMN co_organizers TEXT NULL AFTER organizing_team",
        'contact_info' => "ALTER TABLE events ADD COLUMN contact_info TEXT NULL AFTER co_organizers",
        'proposal_file' => "ALTER TABLE events ADD COLUMN proposal_file VARCHAR(500) NULL AFTER contact_info",
        'poster_file' => "ALTER TABLE events ADD COLUMN poster_file VARCHAR(500) NULL AFTER proposal_file",
        'permit_file' => "ALTER TABLE events ADD COLUMN permit_file VARCHAR(500) NULL AFTER poster_file",
        'guests' => "ALTER TABLE events ADD COLUMN guests TEXT NULL AFTER permit_file",
    ];
    
    $addedFields = [];
    $skippedFields = [];
    $errors = [];
    
    echo "<div class='alert alert-info'><strong>Bước 1:</strong> Kiểm tra và thêm các cột mới...</div>";
    
    foreach ($fields as $fieldName => $sql) {
        try {
            // Kiểm tra cột đã tồn tại chưa
            $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE '$fieldName'");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($columns) > 0) {
                $skippedFields[] = $fieldName;
                echo "<div class='alert alert-warning'>⚠️ Cột <strong>$fieldName</strong> đã tồn tại, bỏ qua.</div>";
            } else {
                // Thêm cột
                $pdo->exec($sql);
                $addedFields[] = $fieldName;
                echo "<div class='alert alert-success'>✅ Đã thêm cột <strong>$fieldName</strong></div>";
            }
        } catch (PDOException $e) {
            // Nếu lỗi là do cột đã tồn tại
            if (strpos($e->getMessage(), 'Duplicate column name') !== false || 
                strpos($e->getMessage(), 'already exists') !== false) {
                $skippedFields[] = $fieldName;
                echo "<div class='alert alert-warning'>⚠️ Cột <strong>$fieldName</strong> đã tồn tại, bỏ qua.</div>";
            } else {
                $errors[] = ['field' => $fieldName, 'error' => $e->getMessage()];
                echo "<div class='alert alert-danger'>❌ Lỗi khi thêm cột <strong>$fieldName</strong>: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
    
    echo "<hr>";
    echo "<div class='alert alert-info'><strong>Bước 2:</strong> Tóm tắt kết quả...</div>";
    
    if (!empty($addedFields)) {
        echo "<div class='alert alert-success'><strong>✅ Đã thêm thành công " . count($addedFields) . " cột:</strong><br>";
        foreach ($addedFields as $field) {
            echo "• <code>$field</code><br>";
        }
        echo "</div>";
    }
    
    if (!empty($skippedFields)) {
        echo "<div class='alert alert-warning'><strong>⚠️ Đã bỏ qua " . count($skippedFields) . " cột (đã tồn tại):</strong><br>";
        foreach ($skippedFields as $field) {
            echo "• <code>$field</code><br>";
        }
        echo "</div>";
    }
    
    if (!empty($errors)) {
        echo "<div class='alert alert-danger'><strong>❌ Có " . count($errors) . " lỗi:</strong><br>";
        foreach ($errors as $error) {
            echo "• <code>{$error['field']}</code>: {$error['error']}<br>";
        }
        echo "</div>";
    }
    
    // Kiểm tra lại tất cả các cột
    echo "<hr>";
    echo "<div class='alert alert-info'><strong>Bước 3:</strong> Kiểm tra lại cấu trúc bảng events...</div>";
    $stmt = $pdo->query("SHOW COLUMNS FROM events");
    $allColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($allColumns, 'Field');
    
    $requiredFields = array_keys($fields);
    $missingFields = array_diff($requiredFields, $columnNames);
    
    if (empty($missingFields)) {
        echo "<div class='alert alert-success'><strong>✅ Tất cả các cột đã được thêm thành công!</strong></div>";
        echo "<div class='alert alert-success'><strong>🎉 Hoàn tất! Bạn có thể xóa file này sau khi hoàn tất.</strong></div>";
    } else {
        echo "<div class='alert alert-danger'><strong>❌ Còn thiếu các cột sau:</strong><br>";
        foreach ($missingFields as $field) {
            echo "• <code>$field</code><br>";
        }
        echo "</div>";
    }
    
    echo "<div class='alert alert-info'><strong>Danh sách tất cả các cột trong bảng events:</strong><br>";
    echo "<ul>";
    foreach ($allColumns as $col) {
        $isNew = in_array($col['Field'], $requiredFields) ? ' <strong style="color: #28a745;">[MỚI]</strong>' : '';
        echo "<li><code>{$col['Field']}</code> ({$col['Type']}){$isNew}</li>";
    }
    echo "</ul></div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'><strong>❌ Lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='alert alert-info'><strong>💡 Hướng dẫn:</strong><br>";
    echo "1. Kiểm tra file <code>.env</code> trong thư mục gốc của project<br>";
    echo "2. Đảm bảo các thông tin database đúng:<br>";
    echo "   - <code>DB_HOST</code><br>";
    echo "   - <code>DB_DATABASE</code><br>";
    echo "   - <code>DB_USERNAME</code><br>";
    echo "   - <code>DB_PASSWORD</code><br>";
    echo "3. Hoặc chạy migration: <code>php artisan migrate</code></div>";
}

echo "</div></body></html>";





























