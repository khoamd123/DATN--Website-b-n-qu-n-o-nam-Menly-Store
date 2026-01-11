-- Tạo bảng club_payment_qrs (Phiên bản đơn giản - chạy trực tiếp)
CREATE TABLE IF NOT EXISTS `club_payment_qrs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `club_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'VietQR',
  `account_number` varchar(255) NOT NULL,
  `bank_code` varchar(50) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `qr_code_data` text DEFAULT NULL,
  `qr_code_image` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `club_payment_qrs_club_id_foreign` (`club_id`),
  KEY `club_payment_qrs_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm foreign keys (chạy từng lệnh, bỏ qua lỗi nếu đã tồn tại)
-- Nếu bị lỗi "Duplicate foreign key", có thể bỏ qua
ALTER TABLE `club_payment_qrs`
  ADD CONSTRAINT `club_payment_qrs_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

ALTER TABLE `club_payment_qrs`
  ADD CONSTRAINT `club_payment_qrs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Thêm các trường vào fund_transactions
-- Chạy từng lệnh một, nếu cột đã tồn tại sẽ báo lỗi nhưng không sao, bỏ qua và chạy tiếp
ALTER TABLE `fund_transactions` ADD COLUMN `payment_method` varchar(255) DEFAULT NULL AFTER `category`;
ALTER TABLE `fund_transactions` ADD COLUMN `transaction_code` varchar(255) DEFAULT NULL AFTER `payment_method`;
ALTER TABLE `fund_transactions` ADD COLUMN `payer_name` varchar(255) DEFAULT NULL AFTER `transaction_code`;
ALTER TABLE `fund_transactions` ADD COLUMN `payer_phone` varchar(20) DEFAULT NULL AFTER `payer_name`;

