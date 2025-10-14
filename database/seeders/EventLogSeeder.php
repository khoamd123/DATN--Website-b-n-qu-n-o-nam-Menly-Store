<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use App\Models\EventLog;
use Carbon\Carbon;

class EventLogSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $logs = [];

        $events = Event::all();

        foreach ($events as $event) {
            // 1. Log tạo event bởi creator
            $logs[] = [
                'event_id' => $event->id,
                'user_id' => $event->created_by,
                'action' => 'created',
                'reason' => 'Tạo sự kiện "' . $event->title . '"',
                'created_at' => $now,
            ];

            // 2. Log ngẫu nhiên cho 1-2 thành viên khác
            $otherUsers = User::where('id', '!=', $event->created_by)->inRandomOrder()->take(rand(1,2))->get();
            foreach ($otherUsers as $user) {
                $logs[] = [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'action' => ['approved','rejected','canceled','completed'][rand(0,3)],
                    'reason' => 'Thực hiện hành động trên sự kiện',
                    'created_at' => $now,
                ];
            }
        }

        EventLog::insert($logs);
    }
}
