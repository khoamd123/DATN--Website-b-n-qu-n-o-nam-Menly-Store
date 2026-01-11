-- Tạo bảng club_payment_qrs
CREATE TABLE IF NOT EXISTS `club_payment_qrs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `club_id` bigint(20) UNSIGNED NOT NULL COMMENT 'CLB nào',
  `payment_method` varchar(255) NOT NULL DEFAULT 'VietQR' COMMENT 'Phương thức thanh toán (VietQR, Momo, etc.)',
  `account_number` varchar(255) NOT NULL COMMENT 'Số tài khoản',
  `bank_code` varchar(50) DEFAULT NULL COMMENT 'Mã ngân hàng (TCB, VCB, etc.)',
  `account_name` varchar(255) DEFAULT NULL COMMENT 'Tên chủ tài khoản',
  `qr_code_data` text DEFAULT NULL COMMENT 'Dữ liệu QR code (base64 hoặc URL)',
  `qr_code_image` varchar(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh QR code',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'QR code mặc định của CLB',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái hoạt động',
  `description` text DEFAULT NULL COMMENT 'Mô tả',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT 'Người tạo (leader)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `club_payment_qrs_club_id_foreign` (`club_id`),
  KEY `club_payment_qrs_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm foreign keys
ALTER TABLE `club_payment_qrs`
  ADD CONSTRAINT `club_payment_qrs_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_payment_qrs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Thêm các trường vào bảng fund_transactions (nếu chưa có)
-- Kiểm tra và thêm payment_method
SET @dbname = DATABASE();
SET @tablename = 'fund_transactions';
SET @columnname = 'payment_method';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' varchar(255) DEFAULT NULL COMMENT ''Phương thức thanh toán'' AFTER `category`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kiểm tra và thêm transaction_code
SET @columnname = 'transaction_code';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' varchar(255) DEFAULT NULL COMMENT ''Mã giao dịch/Số bill'' AFTER `payment_method`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kiểm tra và thêm payer_name
SET @columnname = 'payer_name';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' varchar(255) DEFAULT NULL COMMENT ''Tên người nộp'' AFTER `transaction_code`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kiểm tra và thêm payer_phone
SET @columnname = 'payer_phone';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' varchar(20) DEFAULT NULL COMMENT ''Số điện thoại người nộp'' AFTER `payer_name`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

