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
        if (Schema::hasTable('notifications')) {
            if (!Schema::hasColumn('notifications', 'type')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->string('type', 50)->nullable()->after('sender_id')->comment('event_registration, club_rejection, club, etc');
                });
            }
            
            if (!Schema::hasColumn('notifications', 'related_id')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->unsignedBigInteger('related_id')->nullable()->after('type')->comment('ID của event, club_join_request, etc');
                });
            }
            
            if (!Schema::hasColumn('notifications', 'related_type')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->string('related_type', 50)->nullable()->after('related_id')->comment('Event, ClubJoinRequest, Club, etc');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'type')) {
                    $table->dropColumn('type');
                }
                if (Schema::hasColumn('notifications', 'related_id')) {
                    $table->dropColumn('related_id');
                }
                if (Schema::hasColumn('notifications', 'related_type')) {
                    $table->dropColumn('related_type');
                }
            });
        }
    }
};

