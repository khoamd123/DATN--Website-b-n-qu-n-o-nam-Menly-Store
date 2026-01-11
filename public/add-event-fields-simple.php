<?php
/**
 * Script đơn giản để thêm các cột mới vào bảng events
 * Truy cập: http://your-domain/add-event-fields-simple.php
 */

// Kết nối database trực tiếp
$host = 'localhost';
$dbname = 'your_database_name'; // Thay đổi tên database của bạn
$username = 'root'; // Thay đổi username của bạn
$password = ''; // Thay đổi password của bạn

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Thêm các trường mới vào bảng events</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
            .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
            .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 4px; margin: 10px 0; }
            .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 4px; margin: 10px 0; }
            .info { color: #004085; background: #cce5ff; padding: 15px; border-radius: 4px; margin: 10px 0; }
            .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 4px; margin: 10px 0; }
            .field-item { padding: 10px; margin: 5px 0; background: #f8f9fa; border-left: 4px solid #007bff; }
            code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
    <div class='container'>
        <h1>🔧 Thêm các trường mới vào bảng events</h1>";
    
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
    
    echo "<div class='info'><strong>Bước 1:</strong> Kiểm tra và thêm các cột mới...</div>";
    
    foreach ($fields as $fieldName => $sql) {
        try {
            // Kiểm tra cột đã tồn tại chưa
            $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE '$fieldName'");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($columns) > 0) {
                $skippedFields[] = $fieldName;
                echo "<div class='warning'>⚠️ Cột <strong>$fieldName</strong> đã tồn tại, bỏ qua.</div>";
            } else {
                // Thêm cột
                $pdo->exec($sql);
                $addedFields[] = $fieldName;
                echo "<div class='success'>✅ Đã thêm cột <strong>$fieldName</strong></div>";
            }
        } catch (PDOException $e) {
            // Nếu lỗi là do cột đã tồn tại (có thể do cách kiểm tra không chính xác)
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                $skippedFields[] = $fieldName;
                echo "<div class='warning'>⚠️ Cột <strong>$fieldName</strong> đã tồn tại, bỏ qua.</div>";
            } else {
                $errors[] = ['field' => $fieldName, 'error' => $e->getMessage()];
                echo "<div class='error'>❌ Lỗi khi thêm cột <strong>$fieldName</strong>: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
    
    echo "<hr>";
    echo "<div class='info'><strong>Bước 2:</strong> Tóm tắt kết quả...</div>";
    
    if (!empty($addedFields)) {
        echo "<div class='success'><strong>✅ Đã thêm thành công " . count($addedFields) . " cột:</strong><br>";
        foreach ($addedFields as $field) {
            echo "<div class='field-item'>• $field</div>";
        }
        echo "</div>";
    }
    
    if (!empty($skippedFields)) {
        echo "<div class='warning'><strong>⚠️ Đã bỏ qua " . count($skippedFields) . " cột (đã tồn tại):</strong><br>";
        foreach ($skippedFields as $field) {
            echo "<div class='field-item'>• $field</div>";
        }
        echo "</div>";
    }
    
    if (!empty($errors)) {
        echo "<div class='error'><strong>❌ Có " . count($errors) . " lỗi:</strong><br>";
        foreach ($errors as $error) {
            echo "<div class='field-item'>• {$error['field']}: {$error['error']}</div>";
        }
        echo "</div>";
    }
    
    // Kiểm tra lại tất cả các cột
    echo "<hr>";
    echo "<div class='info'><strong>Bước 3:</strong> Kiểm tra lại cấu trúc bảng events...</div>";
    $stmt = $pdo->query("SHOW COLUMNS FROM events");
    $allColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($allColumns, 'Field');
    
    $requiredFields = array_keys($fields);
    $missingFields = array_diff($requiredFields, $columnNames);
    
    if (empty($missingFields)) {
        echo "<div class='success'><strong>✅ Tất cả các cột đã được thêm thành công!</strong></div>";
        echo "<div class='success'><strong>🎉 Hoàn tất! Bạn có thể xóa file này sau khi hoàn tất.</strong></div>";
    } else {
        echo "<div class='error'><strong>❌ Còn thiếu các cột sau:</strong><br>";
        foreach ($missingFields as $field) {
            echo "<div class='field-item'>• $field</div>";
        }
        echo "</div>";
    }
    
    echo "<div class='info'><strong>Danh sách tất cả các cột trong bảng events:</strong><br>";
    echo "<ul>";
    foreach ($allColumns as $col) {
        $isNew = in_array($col['Field'], $requiredFields) ? ' <strong style=\"color: #28a745;\">[MỚI]</strong>' : '';
        echo "<li><code>{$col['Field']}</code> ({$col['Type']}){$isNew}</li>";
    }
    echo "</ul></div>";
    
} catch (PDOException $e) {
    echo "<div class='error'><strong>❌ Lỗi kết nối database:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'><strong>💡 Hướng dẫn:</strong><br>";
    echo "1. Mở file <code>add-event-fields-simple.php</code> trong editor<br>";
    echo "2. Thay đổi các thông tin kết nối database ở đầu file:<br>";
    echo "   - <code>\$dbname</code>: Tên database của bạn<br>";
    echo "   - <code>\$username</code>: Username database<br>";
    echo "   - <code>\$password</code>: Password database<br>";
    echo "3. Lưu file và refresh trang này.</div>";
}

echo "</div></body></html>";





























