<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\Club;
use Carbon\Carbon;

class NotificationTargetSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $notifications = Notification::all();

        foreach ($notifications as $notification) {

            // Lấy club của sender trực tiếp (owner_id = sender_id)
            $club = Club::where('owner_id', $notification->sender_id)->first();

            if (!$club) continue;

            DB::table('notification_targets')->insert([
                'notification_id' => $notification->id,
                'target_type' => 'club', // gửi tới CLB
                'target_id' => $club->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
