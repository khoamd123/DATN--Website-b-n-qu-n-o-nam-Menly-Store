<?php
// Test hiển thị sự kiện
echo "<!DOCTYPE html>";
echo "<html><head><title>Test Event Show</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
echo "</head><body class='container mt-4'>";
echo "<h1>🧪 Test Event Show</h1>";

try {
    // Kết nối database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=datn_uniclubs', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lấy sự kiện ID 20
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([20]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        echo "<div class='alert alert-danger'>Không tìm thấy sự kiện ID 20</div>";
        exit;
    }
    
    echo "<div class='alert alert-info'>";
    echo "<h4>Thông tin sự kiện ID 20:</h4>";
    echo "<p><strong>Title:</strong> {$event['title']}</p>";
    echo "<p><strong>Status:</strong> {$event['status']}</p>";
    echo "<p><strong>Cancellation Reason:</strong> " . ($event['cancellation_reason'] ?? 'NULL') . "</p>";
    echo "<p><strong>Cancelled At:</strong> " . ($event['cancelled_at'] ?? 'NULL') . "</p>";
    echo "</div>";
    
    // Hiển thị giao diện giống như view thật
    if ($event['status'] === 'cancelled') {
        echo "<div class='card'>";
        echo "<div class='card-header bg-danger text-white'>";
        echo "<h4><i class='fas fa-calendar-alt me-2'></i>{$event['title']}</h4>";
        echo "<span class='badge bg-light text-dark'>Đã hủy</span>";
        echo "<div class='mt-2'>";
        echo "<small class='text-danger'>";
        echo "<i class='fas fa-exclamation-triangle me-1'></i>";
        echo "<strong>Lý do hủy:</strong> " . Str::limit($event['cancellation_reason'] ?? 'Sự kiện đã bị hủy bởi quản trị viên', 50);
        echo "</small>";
        echo "</div>";
        echo "</div>";
        echo "<div class='card-body'>";
        
        // Khung lý do hủy chi tiết
        echo "<div class='cancellation-info mb-4' style='background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%); border-radius: 15px; padding: 0; border: 1px solid #feb2b2; box-shadow: 0 4px 20px rgba(239, 68, 68, 0.1);'>";
        echo "<div class='cancellation-header' style='background: linear-gradient(135deg, #f56565, #e53e3e); color: white; padding: 1.25rem 1.5rem; display: flex; align-items: center;'>";
        echo "<div class='cancellation-icon' style='width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; font-size: 1.2rem;'>";
        echo "<i class='fas fa-exclamation-triangle'></i>";
        echo "</div>";
        echo "<h5 class='cancellation-title' style='margin: 0; font-size: 1.25rem; font-weight: 600;'>Lý do hủy sự kiện</h5>";
        echo "</div>";
        echo "<div class='cancellation-content' style='padding: 1.5rem;'>";
        echo "<div class='cancellation-text' style='font-size: 1.1rem; line-height: 1.7; color: #2d3748; margin-bottom: 1rem; text-align: justify; position: relative; padding-left: 1rem; border-left: 4px solid #f56565;'>";
        echo $event['cancellation_reason'] ?? 'Sự kiện đã bị hủy bởi quản trị viên. Vui lòng liên hệ để biết thêm thông tin chi tiết.';
        echo "</div>";
        echo "<div class='cancellation-footer' style='display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid #feb2b2; background: rgba(245, 101, 101, 0.05); margin: 0 -1.5rem -1.5rem -1.5rem; padding: 1rem 1.5rem;'>";
        echo "<small class='text-muted'>";
        echo "<i class='fas fa-clock me-1'></i>";
        echo "Hủy lúc: " . ($event['cancelled_at'] ?? $event['updated_at']);
        echo "</small>";
        echo "<small class='text-muted'>";
        echo "<i class='fas fa-user me-1'></i>";
        echo "Bởi: Admin";
        echo "</small>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        echo "</div>";
        echo "</div>";
    }
    
    echo "<div class='alert alert-success mt-4'>";
    echo "<h4>✅ Test hoàn tất!</h4>";
    echo "<p>Nếu bạn thấy khung màu đỏ 'Lý do hủy sự kiện' ở trên, thì tính năng đã hoạt động.</p>";
    echo "<p><a href='../admin/events/20' class='btn btn-primary'>Xem trang thật</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Lỗi:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>










































