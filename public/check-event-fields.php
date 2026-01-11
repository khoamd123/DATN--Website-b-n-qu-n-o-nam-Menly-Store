<?php
/**
 * Script kiểm tra các cột mới trong bảng events đã được thêm vào database chưa
 */

// Load Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h2>Kiểm tra các cột mới trong bảng events</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .exists { color: green; font-weight: bold; }
    .missing { color: red; font-weight: bold; }
    .info { background-color: #e7f3ff; padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3; }
</style>";

try {
    // Lấy danh sách tất cả các cột trong bảng events
    $columns = DB::select("SHOW COLUMNS FROM events");
    $columnNames = array_column($columns, 'Field');
    
    // Danh sách các cột mới cần kiểm tra
    $requiredColumns = [
        'registration_deadline' => 'Hạn chót đăng ký tham gia',
        'main_organizer' => 'Người phụ trách chính',
        'organizing_team' => 'Ban tổ chức / đội ngũ thực hiện',
        'co_organizers' => 'Đơn vị phối hợp hoặc đồng tổ chức',
        'contact_info' => 'Liên hệ / thông tin người chịu trách nhiệm',
        'proposal_file' => 'Kế hoạch chi tiết (Proposal / Plan file)',
        'poster_file' => 'Poster / ấn phẩm truyền thông',
        'permit_file' => 'Giấy phép / công văn xin tổ chức',
        'guests' => 'Các khách mời',
    ];
    
    echo "<div class='info'>";
    echo "<h3>Thông tin bảng events</h3>";
    echo "<p><strong>Tổng số cột:</strong> " . count($columnNames) . "</p>";
    echo "</div>";
    
    echo "<table>";
    echo "<tr>";
    echo "<th>STT</th>";
    echo "<th>Tên cột</th>";
    echo "<th>Mô tả</th>";
    echo "<th>Trạng thái</th>";
    echo "<th>Kiểu dữ liệu</th>";
    echo "</tr>";
    
    $index = 1;
    $missingColumns = [];
    
    foreach ($requiredColumns as $columnName => $description) {
        $exists = in_array($columnName, $columnNames);
        $columnInfo = null;
        
        if ($exists) {
            foreach ($columns as $col) {
                if ($col->Field === $columnName) {
                    $columnInfo = $col;
                    break;
                }
            }
        }
        
        echo "<tr>";
        echo "<td>" . $index++ . "</td>";
        echo "<td><strong>" . $columnName . "</strong></td>";
        echo "<td>" . $description . "</td>";
        
        if ($exists) {
            echo "<td class='exists'>✓ Đã tồn tại</td>";
            echo "<td>" . ($columnInfo ? $columnInfo->Type : 'N/A') . "</td>";
        } else {
            echo "<td class='missing'>✗ Chưa tồn tại</td>";
            echo "<td>-</td>";
            $missingColumns[] = $columnName;
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Kiểm tra dữ liệu mẫu
    echo "<h3 style='margin-top: 30px;'>Kiểm tra dữ liệu mẫu</h3>";
    
    try {
        $sampleEvent = DB::table('events')->orderBy('id', 'desc')->first();
        
        if ($sampleEvent) {
            echo "<div class='info'>";
            echo "<h4>Sự kiện mới nhất (ID: {$sampleEvent->id})</h4>";
            echo "<table>";
            echo "<tr><th>Cột</th><th>Giá trị</th></tr>";
            
            foreach ($requiredColumns as $columnName => $description) {
                $value = $sampleEvent->$columnName ?? null;
                echo "<tr>";
                echo "<td><strong>{$columnName}</strong></td>";
                if ($value === null) {
                    echo "<td class='missing'>NULL (Chưa có dữ liệu)</td>";
                } else {
                    if (in_array($columnName, ['contact_info', 'guests'])) {
                        // Hiển thị JSON đẹp hơn
                        $decoded = json_decode($value, true);
                        if ($decoded) {
                            echo "<td><pre style='margin:0;'>" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre></td>";
                        } else {
                            echo "<td>" . htmlspecialchars(substr($value, 0, 100)) . "...</td>";
                        }
                    } else {
                        echo "<td>" . htmlspecialchars(substr($value, 0, 100)) . (strlen($value) > 100 ? '...' : '') . "</td>";
                    }
                }
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
        } else {
            echo "<p>Không có sự kiện nào trong database.</p>";
        }
    } catch (\Exception $e) {
        echo "<p style='color: red;'>Lỗi khi lấy dữ liệu mẫu: " . $e->getMessage() . "</p>";
    }
    
    // Tổng kết
    echo "<h3 style='margin-top: 30px;'>Tổng kết</h3>";
    echo "<div class='info'>";
    
    if (empty($missingColumns)) {
        echo "<p style='color: green; font-weight: bold;'>✓ Tất cả các cột đã được thêm vào database!</p>";
        echo "<p>Bạn có thể tạo hoặc sửa sự kiện để kiểm tra việc lưu dữ liệu.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ Còn thiếu " . count($missingColumns) . " cột:</p>";
        echo "<ul>";
        foreach ($missingColumns as $col) {
            echo "<li><strong>{$col}</strong> - {$requiredColumns[$col]}</li>";
        }
        echo "</ul>";
        echo "<p><strong>Giải pháp:</strong> Chạy migration để thêm các cột này:</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>php artisan migrate</pre>";
    }
    
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<div style='background-color: #ffebee; padding: 15px; margin: 20px 0; border-left: 4px solid #f44336;'>";
    echo "<h3 style='color: red;'>Lỗi</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}





























