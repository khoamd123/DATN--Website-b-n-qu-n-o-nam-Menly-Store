<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fund_id'); // Quỹ nào
            $table->enum('type', ['income', 'expense']); // Thu hay chi
            $table->decimal('amount', 15, 2); // Số tiền
            $table->string('title'); // Tiêu đề giao dịch
            $table->text('description')->nullable(); // Mô tả
            $table->string('category')->nullable(); // Danh mục (ăn uống, vận chuyển, etc.)
            $table->date('transaction_date'); // Ngày giao dịch
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Trạng thái duyệt
            $table->text('rejection_reason')->nullable(); // Lý do từ chối
            $table->string('receipt_path')->nullable(); // Đường dẫn hóa đơn/chứng từ
            $table->unsignedBigInteger('created_by'); // Người tạo
            $table->unsignedBigInteger('approved_by')->nullable(); // Người duyệt
            $table->timestamp('approved_at')->nullable(); // Thời gian duyệt
            $table->unsignedBigInteger('event_id')->nullable(); // Liên kết với sự kiện
            $table->timestamps();
            
            $table->foreign('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fund_transactions');
    }
}
