<?php
/**
 * Script tự động thêm các cột vào bảng events nếu chưa tồn tại
 */

// Load Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Tự động thêm cột vào bảng events</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3; border-radius: 5px; }
        .success-box { background: #e8f5e9; padding: 15px; margin: 20px 0; border-left: 4px solid #4CAF50; border-radius: 5px; }
        .error-box { background: #ffebee; padding: 15px; margin: 20px 0; border-left: 4px solid #f44336; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ddd; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
<div class='container'>";

try {
    echo "<h1>🔧 Tự động thêm cột vào bảng events</h1>";
    
    // Lấy danh sách cột hiện tại
    $columns = DB::select("SHOW COLUMNS FROM events");
    $columnNames = array_column($columns, 'Field');
    
    // Danh sách cột cần thêm (sử dụng IF NOT EXISTS để tránh lỗi)
    $columnsToAdd = [
        'registration_deadline' => [
            'sql' => "ALTER TABLE events ADD COLUMN registration_deadline DATETIME NULL",
            'after' => 'end_time'
        ],
        'main_organizer' => [
            'sql' => "ALTER TABLE events ADD COLUMN main_organizer VARCHAR(255) NULL",
            'after' => 'registration_deadline'
        ],
        'organizing_team' => [
            'sql' => "ALTER TABLE events ADD COLUMN organizing_team TEXT NULL",
            'after' => 'main_organizer'
        ],
        'co_organizers' => [
            'sql' => "ALTER TABLE events ADD COLUMN co_organizers TEXT NULL",
            'after' => 'organizing_team'
        ],
        'contact_info' => [
            'sql' => "ALTER TABLE events ADD COLUMN contact_info TEXT NULL",
            'after' => 'co_organizers'
        ],
        'proposal_file' => [
            'sql' => "ALTER TABLE events ADD COLUMN proposal_file VARCHAR(500) NULL",
            'after' => 'contact_info'
        ],
        'poster_file' => [
            'sql' => "ALTER TABLE events ADD COLUMN poster_file VARCHAR(500) NULL",
            'after' => 'proposal_file'
        ],
        'permit_file' => [
            'sql' => "ALTER TABLE events ADD COLUMN permit_file VARCHAR(500) NULL",
            'after' => 'poster_file'
        ],
        'guests' => [
            'sql' => "ALTER TABLE events ADD COLUMN guests TEXT NULL",
            'after' => 'permit_file'
        ],
    ];
    
    echo "<div class='info'>";
    echo "<h3>📋 Kiểm tra các cột hiện có</h3>";
    echo "<p>Tổng số cột hiện tại: <strong>" . count($columnNames) . "</strong></p>";
    echo "</div>";
    
    $added = [];
    $skipped = [];
    $errors = [];
    
    echo "<table>";
    echo "<tr><th>Cột</th><th>Trạng thái</th><th>Hành động</th></tr>";
    
    foreach ($columnsToAdd as $columnName => $columnInfo) {
        $exists = in_array($columnName, $columnNames);
        
        echo "<tr>";
        echo "<td><code>{$columnName}</code></td>";
        
        if ($exists) {
            echo "<td><span class='success'>✓ Đã tồn tại</span></td>";
            echo "<td>Bỏ qua</td>";
            $skipped[] = $columnName;
        } else {
            try {
                // Xây dựng SQL với AFTER nếu cột trước đó tồn tại
                $sql = $columnInfo['sql'];
                $afterColumn = $columnInfo['after'];
                
                // Kiểm tra xem cột "after" có tồn tại không
                if (in_array($afterColumn, $columnNames)) {
                    $sql .= " AFTER {$afterColumn}";
                }
                
                DB::statement($sql);
                echo "<td><span class='success'>✓ Đã thêm thành công</span></td>";
                echo "<td>Đã thực thi SQL</td>";
                $added[] = $columnName;
                
                // Cập nhật danh sách cột sau khi thêm thành công
                $columnNames[] = $columnName;
            } catch (\Exception $e) {
                // Thử lại không có AFTER nếu lỗi
                try {
                    DB::statement($columnInfo['sql']);
                    echo "<td><span class='success'>✓ Đã thêm thành công (không có AFTER)</span></td>";
                    echo "<td>Đã thực thi SQL không có AFTER</td>";
                    $added[] = $columnName;
                    $columnNames[] = $columnName;
                } catch (\Exception $e2) {
                    echo "<td><span class='error'>✗ Lỗi</span></td>";
                    echo "<td>" . htmlspecialchars($e2->getMessage()) . "</td>";
                    $errors[] = ['column' => $columnName, 'error' => $e2->getMessage()];
                }
            }
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Tổng kết
    echo "<div class='success-box'>";
    echo "<h3>✅ Tổng kết</h3>";
    echo "<ul>";
    echo "<li><strong>Đã thêm:</strong> " . count($added) . " cột</li>";
    echo "<li><strong>Đã tồn tại:</strong> " . count($skipped) . " cột</li>";
    echo "<li><strong>Lỗi:</strong> " . count($errors) . " cột</li>";
    echo "</ul>";
    
    if (!empty($added)) {
        echo "<p class='success'>Các cột đã được thêm: " . implode(', ', $added) . "</p>";
    }
    
    if (!empty($errors)) {
        echo "<div class='error-box'>";
        echo "<h4>❌ Các lỗi xảy ra:</h4>";
        foreach ($errors as $error) {
            echo "<p><code>{$error['column']}</code>: " . htmlspecialchars($error['error']) . "</p>";
        }
        echo "</div>";
    }
    
    echo "</div>";
    
    // Kiểm tra lại
    echo "<div class='info'>";
    echo "<h3>🔍 Kiểm tra lại</h3>";
    $columnsAfter = DB::select("SHOW COLUMNS FROM events");
    $columnNamesAfter = array_column($columnsAfter, 'Field');
    
    $allExists = true;
    foreach (array_keys($columnsToAdd) as $col) {
        if (!in_array($col, $columnNamesAfter)) {
            $allExists = false;
            break;
        }
    }
    
    if ($allExists) {
        echo "<p class='success'>✅ Tất cả các cột cần thiết đã tồn tại trong database!</p>";
        echo "<p>Bây giờ bạn có thể tạo hoặc chỉnh sửa sự kiện và dữ liệu sẽ được lưu vào các cột này.</p>";
    } else {
        echo "<p class='error'>❌ Vẫn còn một số cột chưa được thêm. Vui lòng kiểm tra lại.</p>";
    }
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<div class='error-box'>";
    echo "<h3>❌ Lỗi</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "</div></body></html>";

