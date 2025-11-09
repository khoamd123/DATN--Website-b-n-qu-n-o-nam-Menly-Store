<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fund_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tiêu đề yêu cầu
            $table->text('description'); // Mô tả chi tiết
            $table->decimal('requested_amount', 15, 2); // Số tiền xin cấp
            $table->unsignedBigInteger('event_id'); // Sự kiện liên quan
            $table->unsignedBigInteger('club_id'); // CLB xin cấp
            $table->enum('status', ['pending', 'approved', 'rejected', 'partially_approved'])->default('pending');
            $table->decimal('approved_amount', 15, 2)->nullable(); // Số tiền được duyệt
            $table->text('rejection_reason')->nullable(); // Lý do từ chối
            $table->text('approval_notes')->nullable(); // Ghi chú duyệt
            $table->unsignedBigInteger('created_by'); // Người tạo yêu cầu
            $table->unsignedBigInteger('approved_by')->nullable(); // Người duyệt
            $table->timestamp('approved_at')->nullable(); // Thời gian duyệt
            $table->json('expense_items')->nullable(); // Danh sách các mục chi tiêu
            $table->string('supporting_documents')->nullable(); // Tài liệu hỗ trợ
            $table->timestamps();
            
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fund_requests');
    }
}
