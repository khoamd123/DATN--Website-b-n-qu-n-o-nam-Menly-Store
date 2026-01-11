<?php
// Debug script đơn giản
echo "Testing event access...\n";

// Kiểm tra file .env
if (file_exists('.env')) {
    echo "✅ .env file exists\n";
} else {
    echo "❌ .env file not found\n";
}

// Kiểm tra database connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=datn_uniclubs', 'root', '');
    echo "✅ Database connection successful\n";
    
    // Kiểm tra sự kiện ID 18
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([18]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        echo "✅ Event ID 18 found:\n";
        echo "- Title: " . $event['title'] . "\n";
        echo "- Status: " . $event['status'] . "\n";
        echo "- Created: " . $event['created_at'] . "\n";
        echo "- Updated: " . $event['updated_at'] . "\n";
        
        // Kiểm tra các trường mới
        if (isset($event['cancellation_reason'])) {
            echo "- Cancellation Reason: " . ($event['cancellation_reason'] ?? 'NULL') . "\n";
        } else {
            echo "- Cancellation Reason: Field not exists\n";
        }
        
        if (isset($event['cancelled_at'])) {
            echo "- Cancelled At: " . ($event['cancelled_at'] ?? 'NULL') . "\n";
        } else {
            echo "- Cancelled At: Field not exists\n";
        }
    } else {
        echo "❌ Event ID 18 not found\n";
        
        // Liệt kê tất cả sự kiện
        $stmt = $pdo->prepare("SELECT id, title, status FROM events ORDER BY id");
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "All events:\n";
        foreach ($events as $e) {
            echo "- ID: {$e['id']}, Title: {$e['title']}, Status: {$e['status']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}










































