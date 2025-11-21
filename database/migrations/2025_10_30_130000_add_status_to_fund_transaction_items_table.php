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
        Schema::table('fund_transaction_items', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->after('amount'); // approved, rejected
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fund_transaction_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason']);
        });
    }
};


















