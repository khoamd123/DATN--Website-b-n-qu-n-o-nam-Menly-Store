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
        // Thêm deleted_at cho users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Thêm deleted_at cho posts
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Thêm deleted_at cho clubs
        Schema::table('clubs', function (Blueprint $table) {
            if (!Schema::hasColumn('clubs', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Thêm deleted_at cho club_members
        Schema::table('club_members', function (Blueprint $table) {
            if (!Schema::hasColumn('club_members', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Thêm deleted_at cho post_comments
        Schema::table('post_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('post_comments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa deleted_at
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('clubs', function (Blueprint $table) {
            if (Schema::hasColumn('clubs', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('club_members', function (Blueprint $table) {
            if (Schema::hasColumn('club_members', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('post_comments', function (Blueprint $table) {
            if (Schema::hasColumn('post_comments', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
