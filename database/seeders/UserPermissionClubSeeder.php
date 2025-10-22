<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserPermissionClubSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            // Admin (user_id=1): tất cả quyền (5 quyền: ID 1,2,3,4,5)
            ['user_id'=>1,'club_id'=>1,'permission_ids'=>[1,2,3,4,5]],

            // Tran Thi B (user_id=2): leader quyền
            ['user_id'=>2,'club_id'=>1,'permission_ids'=>[1,2,3,4,5]], // leader có tất cả quyền

            // Pham Thi D (user_id=4): officer quyền
            ['user_id'=>4,'club_id'=>1,'permission_ids'=>[3,4,5]], // tao_su_kien, dang_thong_bao, xem_bao_cao

            // Hoang Van E (user_id=5): officer quyền
            ['user_id'=>5,'club_id'=>1,'permission_ids'=>[3,4,5]],

            // Nguyen Thi F (user_id=6): member quyền
            ['user_id'=>6,'club_id'=>1,'permission_ids'=>[5]], // chỉ xem_bao_cao

            // Tran Van G (user_id=7): member quyền
            ['user_id'=>7,'club_id'=>1,'permission_ids'=>[5]], // chỉ xem_bao_cao
        ];

        foreach ($data as $item) {
            foreach ($item['permission_ids'] as $permId) {
                DB::table('user_permissions_club')->insert([
                    'user_id' => $item['user_id'],
                    'club_id' => $item['club_id'],
                    'permission_id' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
