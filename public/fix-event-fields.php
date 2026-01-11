<?php
/**
 * Script đơn giản để thêm các cột vào bảng events
 * Chạy script này để đảm bảo tất cả các cột cần thiết đã được thêm
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thêm cột vào bảng events</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3; border-radius: 5px; }
        .success-box { background: #e8f5e9; padding: 15px; margin: 20px 0; border-left: 4px solid #4CAF50; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #667eea; color: white; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Thêm cột vào bảng events</h1>

<?php
try {
    // Lấy danh sách cột hiện tại
    $columns = DB::select("SHOW COLUMNS FROM events");
    $existingColumns = array_column($columns, 'Field');
    
    echo "<div class='info'>";
    echo "<p><strong>Tổng số cột hiện tại:</strong> " . count($existingColumns) . "</p>";
    echo "</div>";
    
    // Danh sách cột cần thêm
    $columnsToAdd = [
        'registration_deadline' => 'DATETIME NULL',
        'main_organizer' => 'VARCHAR(255) NULL',
        'organizing_team' => 'TEXT NULL',
        'co_organizers' => 'TEXT NULL',
        'contact_info' => 'TEXT NULL',
        'proposal_file' => 'VARCHAR(500) NULL',
        'poster_file' => 'VARCHAR(500) NULL',
        'permit_file' => 'VARCHAR(500) NULL',
        'guests' => 'TEXT NULL',
    ];
    
    $added = [];
    $skipped = [];
    $errors = [];
    
    echo "<table>";
    echo "<tr><th>Cột</th><th>Trạng thái</th><th>Kết quả</th></tr>";
    
    foreach ($columnsToAdd as $columnName => $columnType) {
        if (in_array($columnName, $existingColumns)) {
            echo "<tr>";
            echo "<td><code>{$columnName}</code></td>";
            echo "<td><span class='success'>✓ Đã tồn tại</span></td>";
            echo "<td>Bỏ qua</td>";
            echo "</tr>";
            $skipped[] = $columnName;
        } else {
            try {
                // Thử thêm với AFTER end_time trước
                $sql = "ALTER TABLE events ADD COLUMN {$columnName} {$columnType}";
                
                // Nếu không phải cột đầu tiên, thử thêm AFTER
                if ($columnName !== 'registration_deadline' && in_array('end_time', $existingColumns)) {
                    // Tìm cột trước đó trong danh sách
                    $prevColumn = null;
                    foreach (array_keys($columnsToAdd) as $key) {
                        if ($key === $columnName) break;
                        if (in_array($key, $existingColumns)) {
                            $prevColumn = $key;
                        }
                    }
                    if ($prevColumn) {
                        $sql .= " AFTER {$prevColumn}";
                    } elseif (in_array('end_time', $existingColumns)) {
                        $sql .= " AFTER end_time";
                    }
                } elseif ($columnName === 'registration_deadline' && in_array('end_time', $existingColumns)) {
                    $sql .= " AFTER end_time";
                }
                
                DB::statement($sql);
                echo "<tr>";
                echo "<td><code>{$columnName}</code></td>";
                echo "<td><span class='success'>✓ Đã thêm</span></td>";
                echo "<td>Thành công</td>";
                echo "</tr>";
                $added[] = $columnName;
                $existingColumns[] = $columnName; // Cập nhật danh sách
            } catch (\Exception $e) {
                // Thử lại không có AFTER
                try {
                    DB::statement("ALTER TABLE events ADD COLUMN {$columnName} {$columnType}");
                    echo "<tr>";
                    echo "<td><code>{$columnName}</code></td>";
                    echo "<td><span class='success'>✓ Đã thêm</span></td>";
                    echo "<td>Thành công (không có AFTER)</td>";
                    echo "</tr>";
                    $added[] = $columnName;
                    $existingColumns[] = $columnName;
                } catch (\Exception $e2) {
                    echo "<tr>";
                    echo "<td><code>{$columnName}</code></td>";
                    echo "<td><span class='error'>✗ Lỗi</span></td>";
                    echo "<td>" . htmlspecialchars($e2->getMessage()) . "</td>";
                    echo "</tr>";
                    $errors[] = ['column' => $columnName, 'error' => $e2->getMessage()];
                }
            }
        }
    }
    
    echo "</table>";
    
    // Tổng kết
    echo "<div class='success-box'>";
    echo "<h3>✅ Tổng kết</h3>";
    echo "<ul>";
    echo "<li><strong>Đã thêm:</strong> " . count($added) . " cột";
    if (!empty($added)) {
        echo " (" . implode(', ', $added) . ")";
    }
    echo "</li>";
    echo "<li><strong>Đã tồn tại:</strong> " . count($skipped) . " cột</li>";
    echo "<li><strong>Lỗi:</strong> " . count($errors) . " cột</li>";
    echo "</ul>";
    
    if (!empty($errors)) {
        echo "<div style='background: #ffebee; padding: 15px; margin-top: 15px; border-left: 4px solid #f44336; border-radius: 5px;'>";
        echo "<h4>❌ Lỗi:</h4>";
        foreach ($errors as $error) {
            echo "<p><code>{$error['column']}</code>: " . htmlspecialchars($error['error']) . "</p>";
        }
        echo "</div>";
    }
    
    // Kiểm tra lại
    $columnsAfter = DB::select("SHOW COLUMNS FROM events");
    $columnNamesAfter = array_column($columnsAfter, 'Field');
    
    $allExists = true;
    $missing = [];
    foreach (array_keys($columnsToAdd) as $col) {
        if (!in_array($col, $columnNamesAfter)) {
            $allExists = false;
            $missing[] = $col;
        }
    }
    
    if ($allExists) {
        echo "<p class='success' style='font-size: 18px; margin-top: 20px;'>✅ <strong>Tất cả các cột đã sẵn sàng!</strong></p>";
        echo "<p>Bây giờ bạn có thể:</p>";
        echo "<ol>";
        echo "<li>Tạo hoặc chỉnh sửa sự kiện</li>";
        echo "<li>Điền đầy đủ thông tin: Thông tin tổ chức, Tài liệu và File, Các khách mời</li>";
        echo "<li>Lưu sự kiện</li>";
        echo "<li>Dữ liệu sẽ được hiển thị ở trang chi tiết sự kiện</li>";
        echo "</ol>";
    } else {
        echo "<p class='error' style='font-size: 18px; margin-top: 20px;'>❌ Vẫn còn thiếu: " . implode(', ', $missing) . "</p>";
    }
    
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; margin: 20px 0; border-left: 4px solid #f44336; border-radius: 5px;'>";
    echo "<h3>❌ Lỗi</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

</div>
</body>
</html>





























