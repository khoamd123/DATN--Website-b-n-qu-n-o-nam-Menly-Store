<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationTarget;
use App\Models\ClubMember;
use Carbon\Carbon;

class NotificationReadSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Lấy tất cả target
        $targets = NotificationTarget::all();

        foreach ($targets as $target) {
            if ($target->target_type === 'club' && $target->target_id) {

                // Lấy tất cả thành viên của club
                $members = ClubMember::where('club_id', $target->target_id)
                                     ->pluck('user_id');

                foreach ($members as $userId) {
                    DB::table('notification_reads')->insert([
                        'notification_id' => $target->notification_id,
                        'user_id' => $userId,
                        'is_read' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }
            }

            // Nếu target_type = 'all', bạn có thể thêm tất cả user trong DB
            // else if ($target->target_type === 'all') { ... }
        }
    }
}
