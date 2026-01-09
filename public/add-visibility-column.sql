-- Script SQL để thêm cột visibility vào bảng events
-- Chạy script này trong phpMyAdmin hoặc MySQL client

-- Kiểm tra và thêm cột visibility nếu chưa tồn tại
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS visibility ENUM('public', 'internal') DEFAULT 'public' AFTER status;

-- Cập nhật tất cả các sự kiện hiện có thành 'public' nếu cột visibility là NULL
UPDATE events SET visibility = 'public' WHERE visibility IS NULL;

