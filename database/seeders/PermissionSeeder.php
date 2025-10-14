<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $permissions = [
            ['name' => 'manage_club', 'description' => 'Quản lý thông tin CLB (chỉnh sửa, xóa)'],
            ['name' => 'manage_members', 'description' => 'Thêm, xóa, duyệt thành viên CLB'],
            ['name' => 'create_event', 'description' => 'Tạo sự kiện cho CLB'],
            ['name' => 'post_announcement', 'description' => 'Tạo thông báo, đăng tin trong CLB'],
            ['name' => 'evaluate_member', 'description' => 'Đánh giá thành viên sau sự kiện'],
            ['name' => 'manage_department', 'description' => 'Quản lý phòng ban/ban trong CLB'],
            ['name' => 'manage_documents', 'description' => 'Quản lý tài liệu, file đính kèm'],
            ['name' => 'view_reports', 'description' => 'Xem báo cáo tổng hợp, thành tích, thống kê CLB'],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->insert([
                'name' => $perm['name'],
                'description' => $perm['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
