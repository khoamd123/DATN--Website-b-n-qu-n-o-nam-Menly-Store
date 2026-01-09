<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code')->unique(); // Mã thanh toán duy nhất
            $table->unsignedBigInteger('user_id'); // Người thanh toán
            $table->unsignedBigInteger('fund_id')->nullable(); // Quỹ liên quan (nếu có)
            $table->unsignedBigInteger('event_id')->nullable(); // Sự kiện liên quan (nếu có)
            $table->unsignedBigInteger('club_id')->nullable(); // CLB liên quan (nếu có)
            
            // Thông tin thanh toán
            $table->decimal('amount', 15, 2); // Số tiền thanh toán
            $table->string('currency', 3)->default('VND'); // Loại tiền tệ
            $table->string('payment_method')->nullable(); // Phương thức thanh toán (vnpay, momo, etc.)
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->enum('payment_type', ['event_registration', 'club_fee', 'fund_contribution', 'other'])->default('other');
            
            // Thông tin giao dịch từ cổng thanh toán
            $table->string('transaction_id')->nullable(); // Mã giao dịch từ cổng thanh toán
            $table->string('bank_code')->nullable(); // Mã ngân hàng (nếu có)
            $table->text('payment_url')->nullable(); // URL thanh toán (cho VNPay)
            $table->text('callback_data')->nullable(); // Dữ liệu callback từ cổng thanh toán (JSON)
            
            // Thông tin bổ sung
            $table->string('description')->nullable(); // Mô tả thanh toán
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamp('paid_at')->nullable(); // Thời gian thanh toán thành công
            $table->timestamp('expires_at')->nullable(); // Thời gian hết hạn thanh toán
            
            // Liên kết với giao dịch quỹ (nếu có)
            $table->unsignedBigInteger('fund_transaction_id')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('set null');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
            $table->foreign('fund_transaction_id')->references('id')->on('fund_transactions')->onDelete('set null');
            
            // Indexes
            $table->index('payment_code');
            $table->index('user_id');
            $table->index('status');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
