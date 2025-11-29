<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kiểm tra xem permission đã tồn tại chưa
        $exists = DB::table('permissions')
            ->where('name', 'quan_ly_quy')
            ->exists();
        
        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => 'quan_ly_quy',
                'description' => 'Quản lý quỹ CLB (tạo giao dịch, xem báo cáo tài chính)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->where('name', 'quan_ly_quy')
            ->delete();
    }
};






