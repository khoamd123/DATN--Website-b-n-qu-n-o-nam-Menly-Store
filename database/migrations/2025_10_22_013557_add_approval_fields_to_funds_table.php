<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('funds')) {
            return;
        }

        Schema::table('funds', function (Blueprint $table) {
            if (! Schema::hasColumn('funds', 'voucher_path')) {
                $table->string('voucher_path')->nullable()->after('content');
            }
            if (! Schema::hasColumn('funds', 'status')) {
                $table->enum('status', ['pending','approved','rejected'])->default('pending')->after('voucher_path');
            }
            if (! Schema::hasColumn('funds', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }
            if (! Schema::hasColumn('funds', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('funds', 'approved_amount')) {
                $table->decimal('approved_amount', 15, 2)->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('funds', 'approval_note')) {
                $table->text('approval_note')->nullable()->after('approved_amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('funds')) {
            return;
        }

        Schema::table('funds', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('funds','voucher_path')) $cols[] = 'voucher_path';
            if (Schema::hasColumn('funds','status')) $cols[] = 'status';
            if (Schema::hasColumn('funds','approved_by')) $cols[] = 'approved_by';
            if (Schema::hasColumn('funds','approved_at')) $cols[] = 'approved_at';
            if (Schema::hasColumn('funds','approved_amount')) $cols[] = 'approved_amount';
            if (Schema::hasColumn('funds','approval_note')) $cols[] = 'approval_note';

            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};