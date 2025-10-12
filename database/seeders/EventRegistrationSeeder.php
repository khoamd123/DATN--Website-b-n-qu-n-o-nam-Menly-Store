<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use App\Models\EventRegistration;
use Carbon\Carbon;

class EventRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $events = Event::all();
        $users = User::all();

        $registrations = [];

        // Lấy 10 cặp user-event để tạo 10 record
        for ($i = 0; $i < 10; $i++) {
            $event = $events->random();
            $user = $users->where('id', '!=', $event->created_by)->random(); // user khác creator

            $registrations[] = [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => 'approved', // có thể là 'pending', 'registered'...
                'joined_at' => $now->copy()->subDays(rand(0,5)),
                'left_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
        }

        // Chèn vào DB
        EventRegistration::insert($registrations);
    }
}
