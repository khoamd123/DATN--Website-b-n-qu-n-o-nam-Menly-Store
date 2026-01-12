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
        // TRƯỚC TIÊN: Thêm 'treasurer' vào ENUM (nếu chưa có)
        // Kiểm tra xem ENUM có 'treasurer' chưa
        $columnInfo = DB::select("SHOW COLUMNS FROM club_members WHERE Field = 'position'");
        if (!empty($columnInfo)) {
            $enumValues = $columnInfo[0]->Type;
            if (strpos($enumValues, 'treasurer') === false) {
                // Nếu chưa có 'treasurer', thêm vào ENUM trước
                DB::statement("ALTER TABLE club_members MODIFY COLUMN position ENUM('leader', 'vice_president', 'treasurer', 'member', 'officer') NOT NULL DEFAULT 'member'");
            }
        }
        
        // SAU ĐÓ: Cập nhật tất cả position 'officer' thành 'treasurer' trong database
        // Sử dụng raw SQL với giá trị được quote để tránh lỗi ENUM
        DB::statement("UPDATE club_members SET position = 'treasurer' WHERE position = 'officer'");
        
        // CUỐI CÙNG: Xóa 'officer' khỏi ENUM, chỉ giữ lại các giá trị mới
        DB::statement("ALTER TABLE club_members MODIFY COLUMN position ENUM('leader', 'vice_president', 'treasurer', 'member') NOT NULL DEFAULT 'member'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cập nhật lại 'treasurer' thành 'officer'
        DB::table('club_members')
            ->where('position', 'treasurer')
            ->update(['position' => 'officer']);
        
        // Khôi phục enum cũ
        DB::statement("ALTER TABLE club_members MODIFY COLUMN position ENUM('leader', 'vice_president', 'officer', 'member') NOT NULL DEFAULT 'member'");
    }
};







