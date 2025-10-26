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
        Schema::table('fund_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', ['event_expense', 'operational_expense', 'income'])
                  ->nullable()
                  ->after('type')
                  ->comment('Loại giao dịch: chi cho sự kiện, chi vận hành CLB, hoặc thu');
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
