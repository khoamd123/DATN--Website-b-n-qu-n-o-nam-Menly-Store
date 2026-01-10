-- Script để thêm cột type, related_id, related_type vào bảng notifications
-- Chạy script này nếu migration chưa được chạy

-- Kiểm tra và thêm cột type
ALTER TABLE notifications 
ADD COLUMN IF NOT EXISTS type VARCHAR(50) NULL AFTER sender_id;

-- Kiểm tra và thêm cột related_id
ALTER TABLE notifications 
ADD COLUMN IF NOT EXISTS related_id BIGINT UNSIGNED NULL AFTER type;

-- Kiểm tra và thêm cột related_type
ALTER TABLE notifications 
ADD COLUMN IF NOT EXISTS related_type VARCHAR(50) NULL AFTER related_id;

