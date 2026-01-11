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
        Schema::table('events', function (Blueprint $table) {
            // Hạn chót đăng ký tham gia
            if (!Schema::hasColumn('events', 'registration_deadline')) {
                $table->dateTime('registration_deadline')->nullable()->after('end_time');
            }
            
            // Người phụ trách chính
            if (!Schema::hasColumn('events', 'main_organizer')) {
                $table->string('main_organizer', 255)->nullable()->after('registration_deadline');
            }
            
            // Ban tổ chức / đội ngũ thực hiện
            if (!Schema::hasColumn('events', 'organizing_team')) {
                $table->text('organizing_team')->nullable()->after('main_organizer');
            }
            
            // Đơn vị phối hợp hoặc đồng tổ chức
            if (!Schema::hasColumn('events', 'co_organizers')) {
                $table->text('co_organizers')->nullable()->after('organizing_team');
            }
            
            // Liên hệ / thông tin người chịu trách nhiệm (JSON để lưu phone, email)
            if (!Schema::hasColumn('events', 'contact_info')) {
                $table->text('contact_info')->nullable()->after('co_organizers');
            }
            
            // Kế hoạch chi tiết (Proposal / Plan file)
            if (!Schema::hasColumn('events', 'proposal_file')) {
                $table->string('proposal_file', 500)->nullable()->after('contact_info');
            }
            
            // Poster / ấn phẩm truyền thông
            if (!Schema::hasColumn('events', 'poster_file')) {
                $table->string('poster_file', 500)->nullable()->after('proposal_file');
            }
            
            // Giấy phép / công văn xin tổ chức
            if (!Schema::hasColumn('events', 'permit_file')) {
                $table->string('permit_file', 500)->nullable()->after('poster_file');
            }
            
            // Các khách mời
            if (!Schema::hasColumn('events', 'guests')) {
                $table->text('guests')->nullable()->after('permit_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $columns = [
                'registration_deadline',
                'main_organizer',
                'organizing_team',
                'co_organizers',
                'contact_info',
                'proposal_file',
                'poster_file',
                'permit_file',
                'guests'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
