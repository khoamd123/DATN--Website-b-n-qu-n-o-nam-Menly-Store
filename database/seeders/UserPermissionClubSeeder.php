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
            // Chủ nhiệm CLB (user_id=1): tất cả quyền
            ['user_id'=>1,'club_id'=>1,'permission_ids'=>[1,2,3,4,5,6,7,8,9]],

            // Các thành viên khác có quyền khác nhau
            ['user_id'=>2,'club_id'=>1,'permission_ids'=>[2,4,5,8]], // create_event, post_announcement, evaluate_member, view_reports
            ['user_id'=>3,'club_id'=>1,'permission_ids'=>[2,4,5,8]],
            ['user_id'=>4,'club_id'=>1,'permission_ids'=>[2,4,5,8]],
            ['user_id'=>5,'club_id'=>1,'permission_ids'=>[2,4,5,8]],
            ['user_id'=>6,'club_id'=>1,'permission_ids'=>[2,4,5,8]],
            ['user_id'=>7,'club_id'=>1,'permission_ids'=>[2,4,5,8]],
            ['user_id'=>8,'club_id'=>1,'permission_ids'=>[2,4,5,8]],
            ['user_id'=>9,'club_id'=>1,'permission_ids'=>[2,4,5,8]],
            ['user_id'=>10,'club_id'=>1,'permission_ids'=>[2,4,5,8]],
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
