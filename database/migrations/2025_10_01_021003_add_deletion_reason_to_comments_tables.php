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
        // Thêm trường deletion_reason và deleted_at vào post_comments
        Schema::table('post_comments', function (Blueprint $table) {
            $table->text('deletion_reason')->nullable()->after('status');
            $table->timestamp('deleted_at')->nullable()->after('deletion_reason');
        });

        // Thêm trường deletion_reason và deleted_at vào event_comments
        Schema::table('event_comments', function (Blueprint $table) {
            $table->text('deletion_reason')->nullable()->after('status');
            $table->timestamp('deleted_at')->nullable()->after('deletion_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropColumn(['deletion_reason', 'deleted_at']);
        });

        Schema::table('event_comments', function (Blueprint $table) {
            $table->dropColumn(['deletion_reason', 'deleted_at']);
        });
    }
};





















