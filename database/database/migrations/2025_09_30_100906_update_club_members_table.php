<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClubMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('club_members', function (Blueprint $table) {
            // Thêm cột position nếu chưa có
            if (!Schema::hasColumn('club_members', 'position')) {
                $table->enum('position', ['leader', 'vice_president', 'officer', 'member'])->default('member');
            }
            
            // Thêm cột status nếu chưa có
            if (!Schema::hasColumn('club_members', 'status')) {
                $table->enum('status', ['active', 'pending', 'inactive', 'approved', 'rejected'])->default('active');
            }
            
            // Thêm cột joined_at nếu chưa có
            if (!Schema::hasColumn('club_members', 'joined_at')) {
                $table->timestamp('joined_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('club_members', function (Blueprint $table) {
            $table->dropColumn(['position', 'status', 'joined_at']);
        });
    }
}

