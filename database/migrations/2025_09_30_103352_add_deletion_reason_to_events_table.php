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
        // Kiểm tra cột đã tồn tại chưa để tránh lỗi khi chạy lại migration
        if (!Schema::hasColumn('events', 'deletion_reason')) {
            Schema::table('events', function (Blueprint $table) {
                // Kiểm tra cột cancelled_at có tồn tại không để đặt vị trí AFTER
                $hasCancelledAt = Schema::hasColumn('events', 'cancelled_at');
                if ($hasCancelledAt) {
                    $table->text('deletion_reason')->nullable()->after('cancelled_at');
                } else {
                    $table->text('deletion_reason')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('deletion_reason');
        });
    }
};




