<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToFundTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fund_transactions', function (Blueprint $table) {
            // Thêm các trường cho nộp quỹ
            $table->string('payment_method')->nullable()->after('category'); // Phương thức thanh toán (VietQR, Momo, etc.)
            $table->string('transaction_code')->nullable()->after('payment_method'); // Mã giao dịch/Số bill
            $table->string('payer_name')->nullable()->after('transaction_code'); // Tên người nộp
            $table->string('payer_phone')->nullable()->after('payer_name'); // Số điện thoại người nộp
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
            $table->dropColumn(['payment_method', 'transaction_code', 'payer_name', 'payer_phone']);
        });
    }
}




