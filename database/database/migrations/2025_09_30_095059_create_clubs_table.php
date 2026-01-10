<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name',255);
            $table->string('slug',255)->uniqid();
            $table->text('description');
            $table->string('logo',255);
            $table->unsignedBigInteger('field_id');
            $table->unsignedBigInteger('owner_id');
            $table->foreign('field_id')->references('id')->on('fields');
            $table->foreign('owner_id')->references('id')->on('users');
            $table->integer('max_members')->default(50);
            $table->enum('status',['pending','approved','rejected','active','inactive'])->default('pending')
            ->comment('Trạng thái câu lạc bộ:
                        - pending: đang chờ duyệt
                        - approved: đã được duyệt nhưng chưa active
                        - rejected: bị từ chối
                        - active: đang hoạt động
                        - inactive: không hoạt động');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
