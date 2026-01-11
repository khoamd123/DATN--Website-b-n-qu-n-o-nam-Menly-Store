<?php
/**
 * Script test để kiểm tra xem dữ liệu có được lưu không
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
    <title>Test Event Save</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #333; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3; border-radius: 5px; }
        .success-box { background: #e8f5e9; padding: 15px; margin: 20px 0; border-left: 4px solid #4CAF50; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #667eea; color: white; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 11px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Test Event Save - Kiểm tra dữ liệu</h1>

<?php
try {
    // Kiểm tra các cột
    $columns = DB::select("SHOW COLUMNS FROM events");
    $columnNames = array_column($columns, 'Field');
    
    $requiredColumns = [
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
    
    echo "<div class='info'>";
    echo "<h3>1. Kiểm tra các cột trong database</h3>";
    $missingColumns = [];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columnNames)) {
            echo "<p class='success'>✓ <code>{$col}</code> tồn tại</p>";
        } else {
            echo "<p class='error'>✗ <code>{$col}</code> KHÔNG tồn tại</p>";
            $missingColumns[] = $col;
        }
    }
    echo "</div>";
    
    if (!empty($missingColumns)) {
        echo "<div class='error-box'>";
        echo "<h3>❌ Các cột còn thiếu: " . implode(', ', $missingColumns) . "</h3>";
        echo "<p>Vui lòng chạy script <code>force-add-columns-and-check.php</code> để thêm các cột này.</p>";
        echo "</div>";
        exit;
    }
    
    // Kiểm tra dữ liệu từ event mới nhất
    echo "<div class='info'>";
    echo "<h3>2. Kiểm tra dữ liệu từ sự kiện mới nhất</h3>";
    
    $latestEvent = Event::orderBy('id', 'desc')->first();
    
    if (!$latestEvent) {
        echo "<p>Không có sự kiện nào trong database.</p>";
        echo "</div>";
    } else {
        echo "<p><strong>Sự kiện mới nhất:</strong> ID {$latestEvent->id} - " . htmlspecialchars($latestEvent->title) . "</p>";
        echo "</div>";
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Giá trị từ Model</th><th>Giá trị từ DB (thô)</th></tr>";
        
        foreach ($requiredColumns as $field) {
            $modelValue = $latestEvent->$field ?? null;
            $rawValue = DB::table('events')->where('id', $latestEvent->id)->value($field);
            
            echo "<tr>";
            echo "<td><code>{$field}</code></td>";
            echo "<td>";
            if ($modelValue !== null && $modelValue !== '') {
                if (is_array($modelValue)) {
                    echo "<pre>" . json_encode($modelValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                } else {
                    echo htmlspecialchars(substr($modelValue, 0, 200));
                }
            } else {
                echo "<span class='error'>NULL hoặc rỗng</span>";
            }
            echo "</td>";
            echo "<td>";
            if ($rawValue !== null && $rawValue !== '') {
                if (in_array($field, ['contact_info', 'guests'])) {
                    $decoded = json_decode($rawValue, true);
                    if ($decoded) {
                        echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                    } else {
                        echo htmlspecialchars(substr($rawValue, 0, 200));
                    }
                } else {
                    echo htmlspecialchars(substr($rawValue, 0, 200));
                }
            } else {
                echo "<span class='error'>NULL hoặc rỗng</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Kiểm tra log
        echo "<div class='info'>";
        echo "<h3>3. Kiểm tra log file</h3>";
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            if (strpos($logContent, 'EventsStore') !== false || strpos($logContent, 'EventsUpdate') !== false) {
                echo "<p class='success'>✓ Có log từ EventsStore/EventsUpdate</p>";
                echo "<p>Kiểm tra file: <code>storage/logs/laravel.log</code></p>";
            } else {
                echo "<p class='warning'>⚠ Không tìm thấy log từ EventsStore/EventsUpdate</p>";
            }
        } else {
            echo "<p class='warning'>⚠ File log không tồn tại</p>";
        }
        echo "</div>";
    }
    
    echo "<div class='success-box'>";
    echo "<h3>✅ Hướng dẫn</h3>";
    echo "<ol>";
    echo "<li>Nếu các cột đã tồn tại nhưng dữ liệu vẫn NULL, hãy tạo/sửa lại sự kiện</li>";
    echo "<li>Kiểm tra log file: <code>storage/logs/laravel.log</code> để xem dữ liệu có được lưu không</li>";
    echo "<li>Nếu log cho thấy dữ liệu được lưu nhưng view không hiển thị, có thể là vấn đề với view</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<div class='error-box'>";
    echo "<h3>❌ Lỗi</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

</div>
</body>
</html>





























