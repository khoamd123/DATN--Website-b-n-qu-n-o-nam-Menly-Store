<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettlementStatusToFundRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            // Thêm trạng thái quyết toán
            $table->enum('settlement_status', ['pending', 'settlement_pending', 'settled', 'cancelled'])
                  ->default('pending')
                  ->after('status');
            
            // Thêm thông tin quyết toán
            $table->text('settlement_notes')->nullable()->after('settlement_status');
            $table->json('settlement_documents')->nullable()->after('settlement_notes');
            $table->decimal('actual_amount', 15, 2)->nullable()->after('settlement_documents');
            $table->timestamp('settlement_date')->nullable()->after('actual_amount');
            $table->unsignedBigInteger('settled_by')->nullable()->after('settlement_date');
            
            // Foreign key cho người quyết toán
            $table->foreign('settled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropForeign(['settled_by']);
            $table->dropColumn([
                'settlement_status',
                'settlement_notes', 
                'settlement_documents',
                'actual_amount',
                'settlement_date',
                'settled_by'
            ]);
        });
    }
}
