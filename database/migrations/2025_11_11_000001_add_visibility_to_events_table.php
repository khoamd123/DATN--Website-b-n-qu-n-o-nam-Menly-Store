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
            // Thêm trường visibility: public (công khai) hoặc internal (chỉ nội bộ CLB)
            if (!Schema::hasColumn('events', 'visibility')) {
                $table->enum('visibility', ['public', 'internal'])->default('public')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'visibility')) {
                $table->dropColumn('visibility');
            }
        });
    }
};

