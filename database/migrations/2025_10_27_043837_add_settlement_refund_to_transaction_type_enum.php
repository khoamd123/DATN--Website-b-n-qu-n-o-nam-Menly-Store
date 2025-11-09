<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSettlementRefundToTransactionTypeEnum extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kiểm tra xem cột đã tồn tại chưa
        if (!Schema::hasColumn('fund_transactions', 'transaction_type')) {
            // Nếu chưa tồn tại, thêm cột mới
            DB::statement("ALTER TABLE `fund_transactions` ADD COLUMN `transaction_type` ENUM('event_expense', 'operational_expense', 'income', 'settlement', 'refund') NULL COMMENT 'Loại giao dịch: chi cho sự kiện, chi vận hành CLB, quyết toán, hoàn tiền, hoặc thu' AFTER `type`");
        } else {
            // Nếu đã tồn tại, chỉ cần modify enum
            DB::statement("ALTER TABLE `fund_transactions` MODIFY COLUMN `transaction_type` ENUM('event_expense', 'operational_expense', 'income', 'settlement', 'refund') NULL COMMENT 'Loại giao dịch: chi cho sự kiện, chi vận hành CLB, quyết toán, hoàn tiền, hoặc thu'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('fund_transactions', 'transaction_type')) {
            DB::statement("ALTER TABLE `fund_transactions` MODIFY COLUMN `transaction_type` ENUM('event_expense', 'operational_expense', 'income') NULL");
        }
    }
}
