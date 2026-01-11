<?php
/**
 * Script kiểm tra các cột trong database cho thông tin tổ chức, tài liệu và khách mời
 */

// Load Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Event;

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Kiểm tra Database - Events</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; border-left: 4px solid #2196F3; padding-left: 15px; }
        h3 { color: #666; margin-top: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f5f5f5; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        .info { background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 15px; margin: 20px 0; border-left: 4px solid #2196F3; border-radius: 5px; }
        .error-box { background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); padding: 15px; margin: 20px 0; border-left: 4px solid #f44336; border-radius: 5px; }
        .success-box { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 15px; margin: 20px 0; border-left: 4px solid #4CAF50; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ddd; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        .badge-success { background: #4CAF50; color: white; }
        .badge-error { background: #f44336; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .section { margin: 30px 0; padding: 20px; background: #fafafa; border-radius: 8px; border: 1px solid #e0e0e0; }
    </style>
</head>
<body>
<div class='container'>";

try {
    echo "<h1>🔍 Kiểm tra Database - Bảng Events</h1>";
    
    // ========== PHẦN 1: KIỂM TRA CÁC CỘT TRONG DATABASE ==========
    echo "<div class='section'>";
    echo "<h2>1. Kiểm tra các cột trong Database</h2>";
    
    $columns = DB::select("SHOW COLUMNS FROM events");
    $columnNames = array_column($columns, 'Field');
    
    // Nhóm các cột theo chức năng
    $columnGroups = [
        'Thông tin tổ chức' => [
            'main_organizer' => 'Người phụ trách chính',
            'organizing_team' => 'Ban tổ chức / Đội ngũ thực hiện',
            'co_organizers' => 'Đơn vị phối hợp hoặc đồng tổ chức',
            'contact_info' => 'Liên hệ / Thông tin người chịu trách nhiệm',
        ],
        'Tài liệu và File' => [
            'proposal_file' => 'Kế hoạch chi tiết (Proposal / Plan file)',
            'poster_file' => 'Poster / Ấn phẩm truyền thông',
            'permit_file' => 'Giấy phép / Công văn xin tổ chức',
        ],
        'Các khách mời' => [
            'guests' => 'Các khách mời',
        ],
        'Thông tin khác' => [
            'registration_deadline' => 'Hạn chót đăng ký tham gia',
        ],
    ];
    
    $allColumns = [];
    foreach ($columnGroups as $group => $cols) {
        foreach ($cols as $col => $desc) {
            $allColumns[$col] = $desc;
        }
    }
    
    echo "<table>";
    echo "<tr>";
    echo "<th>STT</th>";
    echo "<th>Nhóm thông tin</th>";
    echo "<th>Tên cột</th>";
    echo "<th>Mô tả</th>";
    echo "<th>Trạng thái trong DB</th>";
    echo "<th>Kiểu dữ liệu</th>";
    echo "</tr>";
    
    $index = 1;
    $missingColumns = [];
    $existingColumns = [];
    
    foreach ($columnGroups as $groupName => $cols) {
        $firstRow = true;
        foreach ($cols as $columnName => $description) {
            $exists = in_array($columnName, $columnNames);
            $columnInfo = null;
            
            if ($exists) {
                foreach ($columns as $col) {
                    if ($col->Field === $columnName) {
                        $columnInfo = $col;
                        break;
                    }
                }
                $existingColumns[] = $columnName;
            } else {
                $missingColumns[] = $columnName;
            }
            
            echo "<tr>";
            echo "<td>" . ($firstRow ? $index++ : "") . "</td>";
            echo "<td>" . ($firstRow ? "<strong>{$groupName}</strong>" : "") . "</td>";
            echo "<td><code>{$columnName}</code></td>";
            echo "<td>{$description}</td>";
            
            if ($exists) {
                echo "<td><span class='success'>✓ Đã tồn tại</span></td>";
                echo "<td>" . ($columnInfo ? $columnInfo->Type : 'N/A') . "</td>";
            } else {
                echo "<td><span class='error'>✗ Chưa tồn tại</span></td>";
                echo "<td>-</td>";
            }
            
            echo "</tr>";
            $firstRow = false;
        }
    }
    
    echo "</table>";
    echo "</div>";
    
    // ========== PHẦN 2: TỔNG KẾT ==========
    echo "<div class='section'>";
    echo "<h2>2. Tổng kết</h2>";
    
    if (empty($missingColumns)) {
        echo "<div class='success-box'>";
        echo "<h3>✅ Tất cả các cột đã tồn tại trong Database!</h3>";
        echo "<p>Bạn có thể tiếp tục kiểm tra dữ liệu ở phần 3.</p>";
        echo "</div>";
    } else {
        echo "<div class='error-box'>";
        echo "<h3>❌ Còn thiếu " . count($missingColumns) . " cột trong Database:</h3>";
        echo "<ul>";
        foreach ($missingColumns as $col) {
            echo "<li><code>{$col}</code> - {$allColumns[$col]}</li>";
        }
        echo "</ul>";
        echo "<p><strong>Giải pháp:</strong></p>";
        echo "<ol>";
        echo "<li>Chạy migration: <code>php artisan migrate</code></li>";
        echo "<li>Hoặc chạy script: <a href='add-event-fields-manual.php' target='_blank'>add-event-fields-manual.php</a></li>";
        echo "<li>Hoặc chạy SQL trực tiếp trong phpMyAdmin</li>";
        echo "</ol>";
        echo "</div>";
    }
    echo "</div>";
    
    // ========== PHẦN 3: KIỂM TRA DỮ LIỆU MẪU ==========
    if (empty($missingColumns)) {
        echo "<div class='section'>";
        echo "<h2>3. Kiểm tra dữ liệu mẫu</h2>";
        
        $events = Event::orderBy('id', 'desc')->limit(5)->get();
        
        if ($events->isEmpty()) {
            echo "<div class='warning'>";
            echo "<p>Không có sự kiện nào trong database.</p>";
            echo "</div>";
        } else {
            echo "<p><strong>Tìm thấy " . $events->count() . " sự kiện gần nhất:</strong></p>";
            
            foreach ($events as $event) {
                echo "<div class='info'>";
                echo "<h3>📅 Sự kiện ID: {$event->id} - {$event->title}</h3>";
                
                echo "<table>";
                echo "<tr><th>Nhóm thông tin</th><th>Cột</th><th>Giá trị</th><th>Trạng thái</th></tr>";
                
                // Thông tin tổ chức
                echo "<tr><td rowspan='4'><strong>Thông tin tổ chức</strong></td>";
                echo "<td><code>main_organizer</code></td>";
                echo "<td>" . ($event->main_organizer ? htmlspecialchars($event->main_organizer) : "<span class='warning'>NULL</span>") . "</td>";
                echo "<td>" . ($event->main_organizer ? "<span class='success'>✓ Có dữ liệu</span>" : "<span class='warning'>Chưa có</span>") . "</td></tr>";
                
                echo "<tr><td><code>organizing_team</code></td>";
                echo "<td>" . ($event->organizing_team ? htmlspecialchars(substr($event->organizing_team, 0, 100)) . (strlen($event->organizing_team) > 100 ? '...' : '') : "<span class='warning'>NULL</span>") . "</td>";
                echo "<td>" . ($event->organizing_team ? "<span class='success'>✓ Có dữ liệu</span>" : "<span class='warning'>Chưa có</span>") . "</td></tr>";
                
                echo "<tr><td><code>co_organizers</code></td>";
                echo "<td>" . ($event->co_organizers ? htmlspecialchars(substr($event->co_organizers, 0, 100)) . (strlen($event->co_organizers) > 100 ? '...' : '') : "<span class='warning'>NULL</span>") . "</td>";
                echo "<td>" . ($event->co_organizers ? "<span class='success'>✓ Có dữ liệu</span>" : "<span class='warning'>Chưa có</span>") . "</td></tr>";
                
                echo "<tr><td><code>contact_info</code></td>";
                $contact = null;
                if ($event->contact_info) {
                    $contact = is_array($event->contact_info) ? $event->contact_info : json_decode($event->contact_info, true);
                }
                if ($contact && (isset($contact['phone']) || isset($contact['email']))) {
                    echo "<td><pre>" . json_encode($contact, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre></td>";
                    echo "<td><span class='success'>✓ Có dữ liệu</span></td>";
                } else {
                    echo "<td><span class='warning'>NULL</span></td>";
                    echo "<td><span class='warning'>Chưa có</span></td>";
                }
                echo "</tr>";
                
                // Tài liệu và File
                echo "<tr><td rowspan='3'><strong>Tài liệu và File</strong></td>";
                echo "<td><code>proposal_file</code></td>";
                echo "<td>" . ($event->proposal_file ? htmlspecialchars($event->proposal_file) : "<span class='warning'>NULL</span>") . "</td>";
                echo "<td>" . ($event->proposal_file ? "<span class='success'>✓ Có file</span>" : "<span class='warning'>Chưa có</span>") . "</td></tr>";
                
                echo "<tr><td><code>poster_file</code></td>";
                echo "<td>" . ($event->poster_file ? htmlspecialchars($event->poster_file) : "<span class='warning'>NULL</span>") . "</td>";
                echo "<td>" . ($event->poster_file ? "<span class='success'>✓ Có file</span>" : "<span class='warning'>Chưa có</span>") . "</td></tr>";
                
                echo "<tr><td><code>permit_file</code></td>";
                echo "<td>" . ($event->permit_file ? htmlspecialchars($event->permit_file) : "<span class='warning'>NULL</span>") . "</td>";
                echo "<td>" . ($event->permit_file ? "<span class='success'>✓ Có file</span>" : "<span class='warning'>Chưa có</span>") . "</td></tr>";
                
                // Khách mời
                echo "<tr><td><strong>Các khách mời</strong></td>";
                echo "<td><code>guests</code></td>";
                $guestData = null;
                if ($event->guests) {
                    $guestData = is_array($event->guests) ? $event->guests : json_decode($event->guests, true);
                }
                if ($guestData && !empty($guestData['types'])) {
                    echo "<td><pre>" . json_encode($guestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre></td>";
                    echo "<td><span class='success'>✓ Có dữ liệu</span></td>";
                } else {
                    echo "<td><span class='warning'>NULL</span></td>";
                    echo "<td><span class='warning'>Chưa có</span></td>";
                }
                echo "</tr>";
                
                echo "</table>";
                echo "</div>";
            }
        }
        echo "</div>";
    }
    
    // ========== PHẦN 4: HƯỚNG DẪN ==========
    echo "<div class='section'>";
    echo "<h2>4. Hướng dẫn</h2>";
    echo "<div class='info'>";
    echo "<h3>Nếu các cột chưa tồn tại:</h3>";
    echo "<ol>";
    echo "<li><strong>Chạy migration:</strong> <code>php artisan migrate</code></li>";
    echo "<li><strong>Hoặc chạy script:</strong> <a href='add-event-fields-manual.php' target='_blank'>add-event-fields-manual.php</a></li>";
    echo "<li><strong>Hoặc chạy SQL:</strong> Xem file <code>add-event-fields.sql</code></li>";
    echo "</ol>";
    
    echo "<h3>Nếu các cột đã tồn tại nhưng chưa có dữ liệu:</h3>";
    echo "<ol>";
    echo "<li>Tạo mới hoặc chỉnh sửa một sự kiện</li>";
    echo "<li>Điền đầy đủ các thông tin: Thông tin tổ chức, Tài liệu và File, Các khách mời</li>";
    echo "<li>Lưu sự kiện</li>";
    echo "<li>Kiểm tra lại trang này</li>";
    echo "</ol>";
    echo "</div>";
    echo "</div>";
    
} catch (\Exception $e) {
    echo "<div class='error-box'>";
    echo "<h3>❌ Lỗi</h3>";
    echo "<p><strong>Thông báo lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "</div></body></html>";





























