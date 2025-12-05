-- Script SQL để thêm cột deletion_reason và deleted_at vào bảng comments
-- Chạy script này trong phpMyAdmin hoặc MySQL client

-- Thêm cột vào bảng post_comments
ALTER TABLE `post_comments` 
ADD COLUMN `deletion_reason` TEXT NULL AFTER `status`,
ADD COLUMN `deleted_at` TIMESTAMP NULL AFTER `deletion_reason`;

-- Thêm cột vào bảng event_comments
ALTER TABLE `event_comments` 
ADD COLUMN `deletion_reason` TEXT NULL AFTER `status`,
ADD COLUMN `deleted_at` TIMESTAMP NULL AFTER `deletion_reason`;

