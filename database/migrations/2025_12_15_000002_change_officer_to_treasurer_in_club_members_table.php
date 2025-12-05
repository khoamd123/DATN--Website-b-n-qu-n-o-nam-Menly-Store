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
        // Cập nhật tất cả position 'officer' thành 'treasurer' trong database
        DB::table('club_members')
            ->where('position', 'officer')
            ->update(['position' => 'treasurer']);
        
        // Sửa enum trong database (MySQL)
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







