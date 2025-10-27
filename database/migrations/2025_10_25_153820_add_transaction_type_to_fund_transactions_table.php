<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionTypeToFundTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Xóa cột cũ nếu tồn tại (nếu có enum cũ)
        if (Schema::hasColumn('fund_transactions', 'transaction_type')) {
            Schema::table('fund_transactions', function (Blueprint $table) {
                $table->dropColumn('transaction_type');
            });
        }
        
        // Thêm cột mới với enum mới
        Schema::table('fund_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', ['event_expense', 'operational_expense', 'income', 'settlement', 'refund'])
                  ->nullable()
                  ->after('type')
                  ->comment('Loại giao dịch: chi cho sự kiện, chi vận hành CLB, quyết toán, hoàn tiền, hoặc thu');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fund_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
}
