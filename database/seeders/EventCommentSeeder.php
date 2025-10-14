<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use App\Models\EventComment;
use Carbon\Carbon;

class EventCommentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $events = Event::all();
        $users = User::all();

        $comments = [];

        foreach ($events as $index => $event) {
            $commentUsers = $users->where('id', '!=', $event->created_by)
                                  ->shuffle()
                                  ->take(rand(1,2));

            foreach ($commentUsers as $user) {
                $comments[] = [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'parent_id' => null,
                    'content' => 'Bình luận ' . ($index+1),
                    'status' => 'visible',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        EventComment::insert($comments);
    }
}
