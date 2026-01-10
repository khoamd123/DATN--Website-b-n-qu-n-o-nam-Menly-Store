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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'location')) {
                $table->string('location', 255)->nullable()->after('mode');
            }

            // Sửa enum nếu cột tồn tại với giá trị khác. Dùng string thay vì enum để linh hoạt
            if (Schema::hasColumn('events', 'mode')) {
                $table->string('mode', 16)->change(); // offline, online, hybrid
            }
            if (Schema::hasColumn('events', 'status')) {
                $table->string('status', 16)->change(); // draft, pending, approved, ongoing, completed, cancelled
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Không revert kiểu dữ liệu enum để tránh lỗi, chỉ bỏ cột location nếu có
            if (Schema::hasColumn('events', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};


