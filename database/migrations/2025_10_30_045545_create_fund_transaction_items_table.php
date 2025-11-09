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
        Schema::create('fund_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('item_name', 255); // Tên khoản mục: "Ăn uống", "Địa điểm"...
            $table->decimal('amount', 15, 2); // Số tiền của khoản mục này
            $table->text('notes')->nullable(); // Ghi chú cho khoản mục
            $table->timestamps();
            
            $table->foreign('transaction_id')
                  ->references('id')
                  ->on('fund_transactions')
                  ->onDelete('cascade');
            
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_transaction_items');
    }
};
