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
            ['name' => 'quan_ly_clb', 'description' => 'Quản lý thông tin CLB (chỉnh sửa, xóa)'],
            ['name' => 'quan_ly_thanh_vien', 'description' => 'Thêm, xóa, duyệt thành viên CLB'],
            ['name' => 'tao_su_kien', 'description' => 'Tạo sự kiện cho CLB'],
            ['name' => 'dang_thong_bao', 'description' => 'Tạo thông báo, đăng tin trong CLB'],
            ['name' => 'xem_bao_cao', 'description' => 'Xem báo cáo tổng hợp, thành tích, thống kê CLB'],
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
