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
        Schema::table('post_comments', function (Blueprint $table) {
            // Drop the old foreign key
            $table->dropForeign(['post_id']);

            // Add the new foreign key with onDelete('cascade')
            $table->foreign('post_id')
                  ->references('id')
                  ->on('posts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            // Revert to the old constraint if you roll back
            $table->dropForeign(['post_id']);

            $table->foreign('post_id')
                  ->references('id')
                  ->on('posts');
        });
    }
};
