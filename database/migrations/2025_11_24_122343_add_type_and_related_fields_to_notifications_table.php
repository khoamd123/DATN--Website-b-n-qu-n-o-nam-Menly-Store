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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type', 50)->nullable()->after('sender_id')->comment('event_registration, club_rejection, etc');
            $table->unsignedBigInteger('related_id')->nullable()->after('type')->comment('ID của event, club_join_request, etc');
            $table->string('related_type', 50)->nullable()->after('related_id')->comment('Event, ClubJoinRequest, etc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['type', 'related_id', 'related_type']);
        });
    }
};
