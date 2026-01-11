<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateClubPaymentQrTable extends Command
{
    protected $signature = 'create:club-payment-qr-table';
    protected $description = 'Tạo bảng club_payment_qrs và thêm các trường vào fund_transactions';

    public function handle()
    {
        $this->info('Đang tạo bảng club_payment_qrs...');

        try {
            // Tạo bảng club_payment_qrs
            DB::statement("
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
            ");

            $this->info('✅ Bảng club_payment_qrs đã được tạo!');

            // Thêm foreign keys
            try {
                DB::statement("ALTER TABLE `club_payment_qrs` ADD CONSTRAINT `club_payment_qrs_club_id_foreign` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE");
                $this->info('✅ Foreign key club_id đã được thêm!');
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    $this->warn('⚠️  Foreign key club_id đã tồn tại, bỏ qua.');
                } else {
                    $this->error('❌ Lỗi khi thêm foreign key club_id: ' . $e->getMessage());
                }
            }

            try {
                DB::statement("ALTER TABLE `club_payment_qrs` ADD CONSTRAINT `club_payment_qrs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE");
                $this->info('✅ Foreign key created_by đã được thêm!');
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    $this->warn('⚠️  Foreign key created_by đã tồn tại, bỏ qua.');
                } else {
                    $this->error('❌ Lỗi khi thêm foreign key created_by: ' . $e->getMessage());
                }
            }

            // Thêm các cột vào fund_transactions
            $this->info('Đang thêm các trường vào bảng fund_transactions...');

            $existingColumns = Schema::getColumnListing('fund_transactions');

            $columns = [
                'payment_method' => "varchar(255) DEFAULT NULL COMMENT 'Phương thức thanh toán' AFTER `category`",
                'transaction_code' => "varchar(255) DEFAULT NULL COMMENT 'Mã giao dịch/Số bill' AFTER `payment_method`",
                'payer_name' => "varchar(255) DEFAULT NULL COMMENT 'Tên người nộp' AFTER `transaction_code`",
                'payer_phone' => "varchar(20) DEFAULT NULL COMMENT 'Số điện thoại người nộp' AFTER `payer_name`"
            ];

            foreach ($columns as $columnName => $columnDef) {
                if (in_array($columnName, $existingColumns)) {
                    $this->warn("⚠️  Cột `{$columnName}` đã tồn tại, bỏ qua.");
                } else {
                    try {
                        DB::statement("ALTER TABLE `fund_transactions` ADD COLUMN `{$columnName}` {$columnDef}");
                        $this->info("✅ Đã thêm cột `{$columnName}` vào bảng fund_transactions!");
                        $existingColumns[] = $columnName;
                    } catch (\Exception $e) {
                        $this->error("❌ Lỗi khi thêm cột `{$columnName}`: " . $e->getMessage());
                    }
                }
            }

            $this->newLine();
            $this->info('✅ HOÀN TẤT! Tất cả các bảng và cột đã được tạo thành công!');
            $this->info('Bây giờ bạn có thể sử dụng tính năng quản lý QR code thanh toán.');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Lỗi: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}




