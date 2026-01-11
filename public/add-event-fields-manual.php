<?php
/**
 * Script SQL để thêm các cột - Phiên bản tương thích với MySQL cũ
 * Nếu MySQL không hỗ trợ IF NOT EXISTS, dùng script này
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Thêm các trường mới vào bảng events</title>";
echo "<meta charset='UTF-8'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container mt-4'>";
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
    
    // Thử kết nối với các database phổ biến
    $pdo = null;
    $commonDbs = [$dbName, 'datn_uniclubs', 'uniclubs', 'laravel', 'homestead'];
    
    foreach ($commonDbs as $db) {
        try {
            $pdo = new PDO("mysql:host={$dbHost};dbname={$db};charset=utf8mb4", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbName = $db;
            echo "<div class='alert alert-success'>✅ Kết nối database thành công: <strong>{$db}</strong></div>";
            break;
        } catch (PDOException $e) {
            continue;
        }
    }
    
    if (!$pdo) {
        throw new Exception("Không thể kết nối database. Vui lòng kiểm tra thông tin trong file .env");
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
    
    echo "<div class='alert alert-info'><strong>Đang kiểm tra và thêm các cột...</strong></div>";
    
    foreach ($fields as $fieldName => $sql) {
        try {
            // Kiểm tra cột đã tồn tại chưa
            $stmt = $pdo->query("SHOW COLUMNS FROM events WHERE Field = '$fieldName'");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($columns) > 0) {
                $skippedFields[] = $fieldName;
                echo "<div class='alert alert-warning'>⚠️ Cột <code>$fieldName</code> đã tồn tại, bỏ qua.</div>";
            } else {
                // Thêm cột
                $pdo->exec($sql);
                $addedFields[] = $fieldName;
                echo "<div class='alert alert-success'>✅ Đã thêm cột <code>$fieldName</code></div>";
            }
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            // Nếu lỗi là do cột đã tồn tại
            if (strpos($errorMsg, 'Duplicate column name') !== false || 
                strpos($errorMsg, 'already exists') !== false ||
                strpos($errorMsg, 'Duplicate') !== false) {
                $skippedFields[] = $fieldName;
                echo "<div class='alert alert-warning'>⚠️ Cột <code>$fieldName</code> đã tồn tại, bỏ qua.</div>";
            } else {
                $errors[] = ['field' => $fieldName, 'error' => $errorMsg];
                echo "<div class='alert alert-danger'>❌ Lỗi khi thêm cột <code>$fieldName</code>: " . htmlspecialchars($errorMsg) . "</div>";
            }
        }
    }
    
    echo "<hr>";
    
    // Tóm tắt
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
            echo "• <code>{$error['field']}</code>: " . htmlspecialchars($error['error']) . "<br>";
        }
        echo "</div>";
    }
    
    // Kiểm tra lại
    $stmt = $pdo->query("SHOW COLUMNS FROM events");
    $allColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($allColumns, 'Field');
    $requiredFields = array_keys($fields);
    $missingFields = array_diff($requiredFields, $columnNames);
    
    if (empty($missingFields)) {
        echo "<div class='alert alert-success'><strong>🎉 Hoàn tất! Tất cả các cột đã được thêm thành công!</strong></div>";
        echo "<div class='alert alert-info'>Bạn có thể xóa file này sau khi hoàn tất.</div>";
    } else {
        echo "<div class='alert alert-danger'><strong>❌ Còn thiếu các cột sau:</strong><br>";
        foreach ($missingFields as $field) {
            echo "• <code>$field</code><br>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'><strong>❌ Lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";





























