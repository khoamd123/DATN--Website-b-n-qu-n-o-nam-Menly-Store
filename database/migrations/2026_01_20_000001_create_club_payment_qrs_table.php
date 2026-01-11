<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubPaymentQrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('club_payment_qrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id'); // CLB nào
            $table->string('payment_method')->default('VietQR'); // Phương thức thanh toán (VietQR, Momo, etc.)
            $table->string('account_number'); // Số tài khoản
            $table->string('bank_code')->nullable(); // Mã ngân hàng (TCB, VCB, etc.)
            $table->string('account_name')->nullable(); // Tên chủ tài khoản
            $table->text('qr_code_data')->nullable(); // Dữ liệu QR code (base64 hoặc URL)
            $table->string('qr_code_image')->nullable(); // Đường dẫn ảnh QR code
            $table->boolean('is_primary')->default(false); // QR code mặc định của CLB
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->text('description')->nullable(); // Mô tả (ví dụ: "Tài khoản chính của CLB")
            $table->unsignedBigInteger('created_by'); // Người tạo (leader)
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('club_payment_qrs');
    }
}

