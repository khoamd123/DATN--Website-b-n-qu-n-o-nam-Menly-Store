<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('funds') && Schema::hasColumn('funds', 'source')) {
            Schema::table('funds', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('funds') && ! Schema::hasColumn('funds', 'source')) {
            Schema::table('funds', function (Blueprint $table) {
                $table->string('source')->nullable()->after('approval_note');
            });
        }
    }
};