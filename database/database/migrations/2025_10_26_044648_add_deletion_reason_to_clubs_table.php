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
        Schema::table('clubs', function (Blueprint $table) {
            // Thêm cột deletion_reason, cho phép giá trị NULL, sau cột 'rejection_reason'
            $table->text('deletion_reason')->nullable()->after('rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            // Xóa cột deletion_reason nếu migration được hoàn tác
            $table->dropColumn('deletion_reason');
        });
    }
};
