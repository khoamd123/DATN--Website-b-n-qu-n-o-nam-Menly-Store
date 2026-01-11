<?php
/**
 * Script debug để kiểm tra tại sao các thông tin mới không hiển thị trong trang show event
 */

// Load Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Event;

echo "<h2>Debug: Kiểm tra tại sao thông tin không hiển thị</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { background-color: #e7f3ff; padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3; }
    .error-box { background-color: #ffebee; padding: 15px; margin: 20px 0; border-left: 4px solid #f44336; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

try {
    // Lấy event mới nhất
    $event = Event::orderBy('id', 'desc')->first();
    
    if (!$event) {
        echo "<div class='error-box'>";
        echo "<h3>Không tìm thấy sự kiện nào trong database!</h3>";
        echo "</div>";
        exit;
    }
    
    echo "<div class='info'>";
    echo "<h3>Thông tin sự kiện được kiểm tra</h3>";
    echo "<p><strong>ID:</strong> {$event->id}</p>";
    echo "<p><strong>Tiêu đề:</strong> {$event->title}</p>";
    echo "<p><strong>Trạng thái:</strong> {$event->status}</p>";
    echo "</div>";
    
    // Kiểm tra các cột trong database
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
    
    echo "<h3>1. Kiểm tra các cột trong database</h3>";
    echo "<table>";
    echo "<tr><th>Cột</th><th>Tồn tại trong DB</th><th>Giá trị trong Model</th><th>Trạng thái</th></tr>";
    
    foreach ($requiredColumns as $columnName) {
        $existsInDB = in_array($columnName, $columnNames);
        $value = $event->$columnName ?? null;
        $hasValue = $value !== null && $value !== '';
        
        echo "<tr>";
        echo "<td><strong>{$columnName}</strong></td>";
        echo "<td>" . ($existsInDB ? "<span class='success'>✓ Có</span>" : "<span class='error'>✗ Không</span>") . "</td>";
        echo "<td>";
        
        if ($hasValue) {
            if (in_array($columnName, ['contact_info', 'guests'])) {
                $decoded = is_array($value) ? $value : json_decode($value, true);
                if ($decoded) {
                    echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                } else {
                    echo htmlspecialchars(substr($value, 0, 100));
                }
            } else {
                echo htmlspecialchars(substr($value, 0, 100)) . (strlen($value) > 100 ? '...' : '');
            }
        } else {
            echo "<span class='warning'>NULL hoặc rỗng</span>";
        }
        
        echo "</td>";
        
        $status = '';
        if (!$existsInDB) {
            $status = "<span class='error'>Cột chưa tồn tại trong DB</span>";
        } elseif (!$hasValue) {
            $status = "<span class='warning'>Cột tồn tại nhưng chưa có dữ liệu</span>";
        } else {
            $status = "<span class='success'>OK</span>";
        }
        
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Kiểm tra Model fillable
    echo "<h3>2. Kiểm tra Model Event</h3>";
    $model = new Event();
    $fillable = $model->getFillable();
    
    echo "<div class='info'>";
    echo "<h4>Fillable fields:</h4>";
    echo "<pre>" . print_r($fillable, true) . "</pre>";
    
    $missingInFillable = [];
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $fillable)) {
            $missingInFillable[] = $col;
        }
    }
    
    if (empty($missingInFillable)) {
        echo "<p class='success'>✓ Tất cả các cột đã có trong \$fillable</p>";
    } else {
        echo "<p class='error'>✗ Các cột thiếu trong \$fillable: " . implode(', ', $missingInFillable) . "</p>";
    }
    echo "</div>";
    
    // Kiểm tra casts
    $casts = $model->getCasts();
    echo "<div class='info'>";
    echo "<h4>Casts:</h4>";
    echo "<pre>" . print_r($casts, true) . "</pre>";
    echo "</div>";
    
    // Kiểm tra dữ liệu thô từ database
    echo "<h3>3. Kiểm tra dữ liệu thô từ database</h3>";
    $rawData = DB::table('events')->where('id', $event->id)->first();
    
    echo "<table>";
    echo "<tr><th>Cột</th><th>Giá trị thô từ DB</th></tr>";
    
    foreach ($requiredColumns as $columnName) {
        $rawValue = $rawData->$columnName ?? null;
        echo "<tr>";
        echo "<td><strong>{$columnName}</strong></td>";
        echo "<td>";
        
        if ($rawValue !== null) {
            if (in_array($columnName, ['contact_info', 'guests'])) {
                $decoded = json_decode($rawValue, true);
                if ($decoded) {
                    echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                } else {
                    echo htmlspecialchars($rawValue);
                }
            } else {
                echo htmlspecialchars($rawValue);
            }
        } else {
            echo "<span class='warning'>NULL</span>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Tổng kết và giải pháp
    echo "<h3>4. Tổng kết và giải pháp</h3>";
    echo "<div class='info'>";
    
    $missingColumns = [];
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $columnNames)) {
            $missingColumns[] = $col;
        }
    }
    
    if (empty($missingColumns)) {
        echo "<p class='success'>✓ Tất cả các cột đã tồn tại trong database</p>";
        echo "<p><strong>Vấn đề có thể là:</strong></p>";
        echo "<ul>";
        echo "<li>Dữ liệu chưa được nhập khi tạo/sửa sự kiện</li>";
        echo "<li>Controller chưa lưu dữ liệu vào các cột này</li>";
        echo "<li>View có thể có lỗi trong việc hiển thị</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>✗ Còn thiếu các cột trong database: " . implode(', ', $missingColumns) . "</p>";
        echo "<p><strong>Giải pháp:</strong></p>";
        echo "<ol>";
        echo "<li>Chạy migration: <code>php artisan migrate</code></li>";
        echo "<li>Hoặc chạy script: <a href='add-event-fields-manual.php'>add-event-fields-manual.php</a></li>";
        echo "</ol>";
    }
    
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<div class='error-box'>";
    echo "<h3>Lỗi</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}





























