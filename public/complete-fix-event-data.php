<?php
/**
 * Script hoàn chỉnh để sửa lỗi và đảm bảo dữ liệu event được lưu và hiển thị
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
    <title>Hoàn chỉnh - Sửa lỗi Event Data</title>
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
        .step { background: #fff3cd; padding: 15px; margin: 15px 0; border-left: 4px solid #ffc107; border-radius: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Hoàn chỉnh - Sửa lỗi Event Data</h1>

<?php
try {
    $allOk = true;
    $issues = [];
    
    // ========== BƯỚC 1: THÊM CÁC CỘT VÀO DATABASE ==========
    echo "<h2>Bước 1: Thêm các cột vào database</h2>";
    
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
    foreach ($requiredColumns as $columnName => $columnType) {
        if (!in_array($columnName, $existingColumns)) {
            try {
                DB::statement("ALTER TABLE events ADD COLUMN {$columnName} {$columnType}");
                $added[] = $columnName;
                echo "<p class='success'>✓ Đã thêm cột: <code>{$columnName}</code></p>";
            } catch (\Exception $e) {
                echo "<p class='error'>✗ Lỗi khi thêm cột <code>{$columnName}</code>: " . htmlspecialchars($e->getMessage()) . "</p>";
                $allOk = false;
                $issues[] = "Không thể thêm cột {$columnName}";
            }
        } else {
            echo "<p class='success'>✓ Cột <code>{$columnName}</code> đã tồn tại</p>";
        }
    }
    
    if (!empty($added)) {
        echo "<div class='success-box'>";
        echo "<p><strong>Đã thêm " . count($added) . " cột:</strong> " . implode(', ', $added) . "</p>";
        echo "</div>";
    }
    
    // Kiểm tra lại
    $columnsAfter = DB::select("SHOW COLUMNS FROM events");
    $columnNamesAfter = array_column($columnsAfter, 'Field');
    $allColumnsExist = true;
    foreach (array_keys($requiredColumns) as $col) {
        if (!in_array($col, $columnNamesAfter)) {
            $allColumnsExist = false;
            $allOk = false;
            $issues[] = "Cột {$col} vẫn chưa tồn tại";
        }
    }
    
    if ($allColumnsExist) {
        echo "<div class='success-box'>";
        echo "<p class='success'>✅ Tất cả các cột đã tồn tại trong database!</p>";
        echo "</div>";
    } else {
        echo "<div class='error-box'>";
        echo "<p class='error'>❌ Vẫn còn một số cột chưa được thêm.</p>";
        echo "</div>";
    }
    
    // ========== BƯỚC 2: KIỂM TRA MODEL ==========
    echo "<h2>Bước 2: Kiểm tra Model Event</h2>";
    
    $model = new Event();
    $fillable = $model->getFillable();
    $casts = $model->getCasts();
    
    $modelOk = true;
    foreach (array_keys($requiredColumns) as $col) {
        if (!in_array($col, $fillable)) {
            echo "<p class='error'>✗ <code>{$col}</code> THIẾU trong \$fillable</p>";
            $modelOk = false;
            $allOk = false;
            $issues[] = "Model thiếu {$col} trong \$fillable";
        } else {
            echo "<p class='success'>✓ <code>{$col}</code> có trong \$fillable</p>";
        }
    }
    
    // Kiểm tra casts
    if (!isset($casts['registration_deadline']) || $casts['registration_deadline'] !== 'datetime') {
        echo "<p class='warning'>⚠ <code>registration_deadline</code> cần cast là 'datetime'</p>";
    }
    if (!isset($casts['contact_info']) || $casts['contact_info'] !== 'array') {
        echo "<p class='warning'>⚠ <code>contact_info</code> cần cast là 'array'</p>";
    }
    if (!isset($casts['guests']) || $casts['guests'] !== 'array') {
        echo "<p class='warning'>⚠ <code>guests</code> cần cast là 'array'</p>";
    }
    
    if ($modelOk) {
        echo "<div class='success-box'>";
        echo "<p class='success'>✅ Model Event đã được cấu hình đúng!</p>";
        echo "</div>";
    }
    
    // ========== BƯỚC 3: KIỂM TRA DỮ LIỆU ==========
    echo "<h2>Bước 3: Kiểm tra dữ liệu mẫu</h2>";
    
    $events = Event::orderBy('id', 'desc')->limit(1)->get();
    
    if ($events->isEmpty()) {
        echo "<div class='info'>";
        echo "<p>Không có sự kiện nào trong database. Hãy tạo một sự kiện mới để kiểm tra.</p>";
        echo "</div>";
    } else {
        $event = $events->first();
        echo "<div class='info'>";
        echo "<p><strong>Sự kiện mới nhất:</strong> ID {$event->id} - " . htmlspecialchars($event->title) . "</p>";
        echo "</div>";
        
        echo "<table>";
        echo "<tr><th>Thông tin</th><th>Giá trị</th><th>Trạng thái</th></tr>";
        
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
            echo "<td><strong>{$label}</strong></td>";
            echo "<td>";
            
            if ($hasValue) {
                if (in_array($field, ['contact_info', 'guests'])) {
                    $decoded = is_array($value) ? $value : json_decode($value, true);
                    if ($decoded) {
                        echo "<pre style='margin:0; font-size:11px; max-height:100px; overflow:auto;'>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                    } else {
                        echo htmlspecialchars(substr($value, 0, 50));
                    }
                } else {
                    echo htmlspecialchars(substr($value, 0, 100));
                }
            } else {
                echo "<span class='warning'>NULL hoặc rỗng</span>";
            }
            
            echo "</td>";
            echo "<td>" . ($hasValue ? "<span class='success'>✓ Có</span>" : "<span class='warning'>Chưa có</span>") . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // ========== TỔNG KẾT ==========
    echo "<h2>Tổng kết</h2>";
    
    if ($allOk && $allColumnsExist && $modelOk) {
        echo "<div class='success-box'>";
        echo "<h3>✅ Tất cả đã sẵn sàng!</h3>";
        echo "<p>Bây giờ bạn có thể:</p>";
        echo "<ol>";
        echo "<li>Tạo hoặc chỉnh sửa sự kiện</li>";
        echo "<li>Điền đầy đủ các thông tin: Thông tin tổ chức, Tài liệu và File, Các khách mời</li>";
        echo "<li>Lưu sự kiện</li>";
        echo "<li>Dữ liệu sẽ được hiển thị ở trang chi tiết sự kiện</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='error-box'>";
        echo "<h3>❌ Còn một số vấn đề cần xử lý:</h3>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li>{$issue}</li>";
        }
        echo "</ul>";
        echo "<p><strong>Giải pháp:</strong> Chạy lại script này hoặc kiểm tra từng phần một.</p>";
        echo "</div>";
    }
    
    echo "<div class='step'>";
    echo "<h3>📝 Hướng dẫn tiếp theo:</h3>";
    echo "<ol>";
    echo "<li>Nếu các cột đã được thêm thành công, hãy tạo hoặc chỉnh sửa một sự kiện</li>";
    echo "<li>Điền đầy đủ tất cả các thông tin trong form</li>";
    echo "<li>Lưu sự kiện và kiểm tra trang chi tiết</li>";
    echo "<li>Nếu vẫn không hiển thị, kiểm tra file log: <code>storage/logs/laravel.log</code></li>";
    echo "</ol>";
    echo "</div>";
    
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





























