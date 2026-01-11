<?php
/**
 * Script kiểm tra và sửa lỗi toàn diện cho event data
 * - Kiểm tra các cột trong database
 * - Thêm cột nếu thiếu
 * - Kiểm tra dữ liệu mẫu
 * - Kiểm tra Model và Controller
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
    <title>Kiểm tra và Sửa lỗi Event Data</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; border-left: 4px solid #2196F3; padding-left: 15px; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3; border-radius: 5px; }
        .success-box { background: #e8f5e9; padding: 15px; margin: 20px 0; border-left: 4px solid #4CAF50; border-radius: 5px; }
        .error-box { background: #ffebee; padding: 15px; margin: 20px 0; border-left: 4px solid #f44336; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #667eea; color: white; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        .btn { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #45a049; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Kiểm tra và Sửa lỗi Event Data</h1>

<?php
try {
    // ========== PHẦN 1: KIỂM TRA VÀ THÊM CÁC CỘT ==========
    echo "<h2>1. Kiểm tra và thêm các cột vào database</h2>";
    
    $columns = DB::select("SHOW COLUMNS FROM events");
    $existingColumns = array_column($columns, 'Field');
    
    $requiredColumns = [
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
    
    foreach ($requiredColumns as $columnName => $columnType) {
        if (in_array($columnName, $existingColumns)) {
            echo "<tr>";
            echo "<td><code>{$columnName}</code></td>";
            echo "<td><span class='success'>✓ Đã tồn tại</span></td>";
            echo "<td>OK</td>";
            echo "</tr>";
            $skipped[] = $columnName;
        } else {
            try {
                $sql = "ALTER TABLE events ADD COLUMN {$columnName} {$columnType}";
                
                // Thêm AFTER nếu có thể
                if ($columnName === 'registration_deadline' && in_array('end_time', $existingColumns)) {
                    $sql .= " AFTER end_time";
                } elseif (in_array('end_time', $existingColumns)) {
                    // Tìm cột trước đó
                    $prevColumn = null;
                    foreach (array_keys($requiredColumns) as $key) {
                        if ($key === $columnName) break;
                        if (in_array($key, $existingColumns)) {
                            $prevColumn = $key;
                        }
                    }
                    if ($prevColumn) {
                        $sql .= " AFTER {$prevColumn}";
                    }
                }
                
                DB::statement($sql);
                echo "<tr>";
                echo "<td><code>{$columnName}</code></td>";
                echo "<td><span class='success'>✓ Đã thêm</span></td>";
                echo "<td>Thành công</td>";
                echo "</tr>";
                $added[] = $columnName;
                $existingColumns[] = $columnName;
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
    
    // Kiểm tra lại
    $columnsAfter = DB::select("SHOW COLUMNS FROM events");
    $columnNamesAfter = array_column($columnsAfter, 'Field');
    $allColumnsExist = true;
    foreach (array_keys($requiredColumns) as $col) {
        if (!in_array($col, $columnNamesAfter)) {
            $allColumnsExist = false;
            break;
        }
    }
    
    if ($allColumnsExist) {
        echo "<div class='success-box'>";
        echo "<p class='success'>✅ Tất cả các cột đã tồn tại trong database!</p>";
        echo "</div>";
    } else {
        echo "<div class='error-box'>";
        echo "<p class='error'>❌ Vẫn còn một số cột chưa được thêm. Vui lòng kiểm tra lại.</p>";
        echo "</div>";
    }
    
    // ========== PHẦN 2: KIỂM TRA MODEL ==========
    echo "<h2>2. Kiểm tra Model Event</h2>";
    
    $model = new Event();
    $fillable = $model->getFillable();
    $casts = $model->getCasts();
    
    echo "<div class='info'>";
    echo "<h3>Fillable fields:</h3>";
    $missingInFillable = [];
    foreach (array_keys($requiredColumns) as $col) {
        if (in_array($col, $fillable)) {
            echo "<p class='success'>✓ <code>{$col}</code> có trong \$fillable</p>";
        } else {
            echo "<p class='error'>✗ <code>{$col}</code> THIẾU trong \$fillable</p>";
            $missingInFillable[] = $col;
        }
    }
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>Casts:</h3>";
    $requiredCasts = [
        'registration_deadline' => 'datetime',
        'contact_info' => 'array',
        'guests' => 'array',
    ];
    foreach ($requiredCasts as $col => $castType) {
        if (isset($casts[$col]) && $casts[$col] === $castType) {
            echo "<p class='success'>✓ <code>{$col}</code> có cast đúng: {$castType}</p>";
        } else {
            echo "<p class='warning'>⚠ <code>{$col}</code> chưa có cast hoặc cast sai</p>";
        }
    }
    echo "</div>";
    
    // ========== PHẦN 3: KIỂM TRA DỮ LIỆU MẪU ==========
    echo "<h2>3. Kiểm tra dữ liệu mẫu</h2>";
    
    $events = Event::orderBy('id', 'desc')->limit(3)->get();
    
    if ($events->isEmpty()) {
        echo "<div class='warning'>";
        echo "<p>Không có sự kiện nào trong database.</p>";
        echo "</div>";
    } else {
        echo "<p><strong>Tìm thấy " . $events->count() . " sự kiện gần nhất:</strong></p>";
        
        foreach ($events as $event) {
            echo "<div class='info'>";
            echo "<h3>📅 Sự kiện ID: {$event->id} - " . htmlspecialchars($event->title) . "</h3>";
            
            echo "<table>";
            echo "<tr><th>Thông tin</th><th>Giá trị</th><th>Trạng thái</th></tr>";
            
            // Kiểm tra từng field
            $fields = [
                'registration_deadline' => 'Hạn chót đăng ký',
                'main_organizer' => 'Người phụ trách chính',
                'organizing_team' => 'Ban tổ chức',
                'co_organizers' => 'Đơn vị phối hợp',
                'contact_info' => 'Liên hệ',
                'proposal_file' => 'Kế hoạch chi tiết',
                'poster_file' => 'Poster',
                'permit_file' => 'Giấy phép',
                'guests' => 'Khách mời',
            ];
            
            foreach ($fields as $field => $label) {
                $value = $event->$field ?? null;
                $hasValue = $value !== null && $value !== '';
                
                echo "<tr>";
                echo "<td><strong>{$label}</strong><br><code>{$field}</code></td>";
                echo "<td>";
                
                if ($hasValue) {
                    if (in_array($field, ['contact_info', 'guests'])) {
                        $decoded = is_array($value) ? $value : json_decode($value, true);
                        if ($decoded) {
                            echo "<pre style='margin:0; font-size:11px;'>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                        } else {
                            echo htmlspecialchars(substr($value, 0, 50)) . "...";
                        }
                    } else {
                        echo htmlspecialchars(substr($value, 0, 100)) . (strlen($value) > 100 ? '...' : '');
                    }
                } else {
                    echo "<span class='warning'>NULL hoặc rỗng</span>";
                }
                
                echo "</td>";
                echo "<td>" . ($hasValue ? "<span class='success'>✓ Có dữ liệu</span>" : "<span class='warning'>Chưa có</span>") . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        }
    }
    
    // ========== PHẦN 4: TỔNG KẾT VÀ HƯỚNG DẪN ==========
    echo "<h2>4. Tổng kết và Hướng dẫn</h2>";
    
    echo "<div class='success-box'>";
    echo "<h3>✅ Các bước tiếp theo:</h3>";
    echo "<ol>";
    echo "<li><strong>Nếu các cột chưa tồn tại:</strong> Script đã tự động thêm. Nếu vẫn còn lỗi, vui lòng chạy lại script này.</li>";
    echo "<li><strong>Nếu Model thiếu field trong \$fillable:</strong> Cần thêm vào file <code>app/Models/Event.php</code></li>";
    echo "<li><strong>Tạo hoặc chỉnh sửa sự kiện:</strong> Điền đầy đủ thông tin và lưu</li>";
    echo "<li><strong>Kiểm tra lại:</strong> Xem trang chi tiết sự kiện để xác nhận dữ liệu đã hiển thị</li>";
    echo "</ol>";
    echo "</div>";
    
    if (!empty($missingInFillable)) {
        echo "<div class='error-box'>";
        echo "<h3>❌ Cần sửa Model Event:</h3>";
        echo "<p>Các field sau cần được thêm vào <code>\$fillable</code> trong file <code>app/Models/Event.php</code>:</p>";
        echo "<pre>";
        foreach ($missingInFillable as $col) {
            echo "'{$col}',\n";
        }
        echo "</pre>";
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





























