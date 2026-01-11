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
            $table->timestamp('read_at')->nullable()->after('message');
            $table->string('type', 50)->nullable()->default('general')->after('sender_id');
            $table->unsignedBigInteger('related_id')->nullable()->after('type');
            $table->string('related_type', 50)->nullable()->after('related_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['read_at', 'type', 'related_id', 'related_type']);
        });
    }
};

