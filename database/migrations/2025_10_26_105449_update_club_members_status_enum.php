<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateClubMembersStatusEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify the enum to add 'approved' and 'rejected' values
        DB::statement("ALTER TABLE club_members MODIFY COLUMN status ENUM('active', 'pending', 'inactive', 'approved', 'rejected') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE club_members MODIFY COLUMN status ENUM('active', 'pending', 'inactive') NOT NULL DEFAULT 'active'");
    }
}
