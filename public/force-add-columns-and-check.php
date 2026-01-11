<?php
/**
 * Script ép buộc thêm cột và kiểm tra dữ liệu
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Event;

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ép buộc thêm cột và kiểm tra</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3; border-radius: 5px; }
        .success-box { background: #e8f5e9; padding: 15px; margin: 20px 0; border-left: 4px solid #4CAF50; border-radius: 5px; }
        .error-box { background: #ffebee; padding: 15px; margin: 20px 0; border-left: 4px solid #f44336; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #667eea; color: white; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Ép buộc thêm cột và kiểm tra dữ liệu</h1>

<?php
try {
    // ========== THÊM CÁC CỘT ==========
    echo "<h2>1. Thêm các cột vào database</h2>";
    
    $columns = DB::select("SHOW COLUMNS FROM events");
    $existingColumns = array_column($columns, 'Field');
    
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
    foreach ($columnsToAdd as $colName => $colType) {
        if (!in_array($colName, $existingColumns)) {
            try {
                // Thử thêm với IF NOT EXISTS
                DB::statement("ALTER TABLE events ADD COLUMN IF NOT EXISTS {$colName} {$colType}");
                $added[] = $colName;
                echo "<p class='success'>✓ Đã thêm: <code>{$colName}</code></p>";
            } catch (\Exception $e1) {
                // Nếu không hỗ trợ IF NOT EXISTS, thử cách khác
                try {
                    DB::statement("ALTER TABLE events ADD COLUMN {$colName} {$colType}");
                    $added[] = $colName;
                    echo "<p class='success'>✓ Đã thêm: <code>{$colName}</code></p>";
                } catch (\Exception $e2) {
                    echo "<p class='error'>✗ Lỗi khi thêm <code>{$colName}</code>: " . htmlspecialchars($e2->getMessage()) . "</p>";
                }
            }
        } else {
            echo "<p class='success'>✓ <code>{$colName}</code> đã tồn tại</p>";
        }
    }
    
    // Kiểm tra lại
    $columnsAfter = DB::select("SHOW COLUMNS FROM events");
    $columnNamesAfter = array_column($columnsAfter, 'Field');
    
    echo "<div class='info'>";
    echo "<h3>Tổng số cột sau khi thêm: " . count($columnNamesAfter) . "</h3>";
    echo "</div>";
    
    // ========== KIỂM TRA DỮ LIỆU THỰC TẾ ==========
    echo "<h2>2. Kiểm tra dữ liệu từ database trực tiếp</h2>";
    
    $latestEvent = DB::table('events')->orderBy('id', 'desc')->first();
    
    if (!$latestEvent) {
        echo "<div class='error-box'>";
        echo "<p>Không có sự kiện nào trong database.</p>";
        echo "</div>";
    } else {
        echo "<div class='info'>";
        echo "<p><strong>Sự kiện mới nhất:</strong> ID {$latestEvent->id} - " . htmlspecialchars($latestEvent->title ?? 'N/A') . "</p>";
        echo "</div>";
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Giá trị từ DB (thô)</th><th>Giá trị từ Model</th></tr>";
        
        $fields = [
            'registration_deadline',
            'main_organizer',
            'organizing_team',
            'co_organizers',
            'contact_info',
            'proposal_file',
            'poster_file',
            'permit_file',
            'guests',
        ];
        
        $eventModel = Event::find($latestEvent->id);
        
        foreach ($fields as $field) {
            $rawValue = $latestEvent->$field ?? null;
            $modelValue = $eventModel ? ($eventModel->$field ?? null) : null;
            
            echo "<tr>";
            echo "<td><code>{$field}</code></td>";
            echo "<td>";
            if ($rawValue !== null) {
                if (in_array($field, ['contact_info', 'guests'])) {
                    $decoded = json_decode($rawValue, true);
                    if ($decoded) {
                        echo "<pre style='margin:0; font-size:11px;'>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                    } else {
                        echo htmlspecialchars(substr($rawValue, 0, 100));
                    }
                } else {
                    echo htmlspecialchars(substr($rawValue, 0, 100));
                }
            } else {
                echo "<span class='error'>NULL</span>";
            }
            echo "</td>";
            echo "<td>";
            if ($modelValue !== null) {
                if (is_array($modelValue)) {
                    echo "<pre style='margin:0; font-size:11px;'>" . json_encode($modelValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                } else {
                    echo htmlspecialchars(substr($modelValue, 0, 100));
                }
            } else {
                echo "<span class='error'>NULL</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // ========== KIỂM TRA CONTROLLER LOGIC ==========
    echo "<h2>3. Kiểm tra Controller Logic</h2>";
    
    echo "<div class='info'>";
    echo "<p>Controller hiện tại chỉ lưu dữ liệu nếu cột tồn tại trong database.</p>";
    echo "<p>Nếu các cột đã được thêm ở trên, hãy tạo/sửa lại sự kiện để dữ liệu được lưu.</p>";
    echo "</div>";
    
    // ========== TỔNG KẾT ==========
    echo "<h2>4. Tổng kết</h2>";
    
    $allColumnsExist = true;
    foreach (array_keys($columnsToAdd) as $col) {
        if (!in_array($col, $columnNamesAfter)) {
            $allColumnsExist = false;
            break;
        }
    }
    
    if ($allColumnsExist) {
        echo "<div class='success-box'>";
        echo "<h3>✅ Tất cả các cột đã tồn tại!</h3>";
        echo "<p><strong>Bước tiếp theo:</strong></p>";
        echo "<ol>";
        echo "<li>Tạo hoặc chỉnh sửa một sự kiện</li>";
        echo "<li>Điền đầy đủ tất cả các thông tin</li>";
        echo "<li>Lưu sự kiện</li>";
        echo "<li>Kiểm tra lại trang chi tiết</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='error-box'>";
        echo "<h3>❌ Vẫn còn một số cột chưa được thêm</h3>";
        echo "<p>Vui lòng kiểm tra lại hoặc thêm thủ công trong phpMyAdmin.</p>";
        echo "</div>";
    }
    
} catch (\Exception $e) {
    echo "<div class='error-box'>";
    echo "<h3>❌ Lỗi</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>

</div>
</body>
</html>





























