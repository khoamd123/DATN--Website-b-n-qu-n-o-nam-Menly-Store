<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên quỹ
            $table->text('description')->nullable(); // Mô tả quỹ
            $table->decimal('initial_amount', 15, 2)->default(0); // Số tiền ban đầu
            $table->decimal('current_amount', 15, 2)->default(0); // Số tiền hiện tại
            $table->string('source')->nullable(); // Nguồn tiền (nhà trường, tài trợ, etc.)
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
            $table->unsignedBigInteger('club_id')->nullable(); // Quỹ của CLB nào
            $table->unsignedBigInteger('created_by'); // Người tạo
            $table->timestamps();
            
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
        Schema::dropIfExists('funds');
    }
}
