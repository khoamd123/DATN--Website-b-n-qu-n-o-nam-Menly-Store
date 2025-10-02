<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\ClubMember;
use App\Models\EventRegistration;
use App\Models\EventMemberEvaluation;
use Carbon\Carbon;

class EventMemberEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $evaluations = [];

        // Lấy 10 event gần nhất
        $events = Event::latest()->take(10)->get();

        foreach ($events as $event) {
            // Lấy danh sách user đã tham gia sự kiện
            $participants = EventRegistration::where('event_id', $event->id)
                                             ->whereIn('status', ['approved','attended'])
                                             ->pluck('user_id');

            if ($participants->isEmpty()) continue;

            // Lấy evaluator: chủ nhiệm hoặc trưởng ban
            $evaluatorMember = ClubMember::where('club_id', $event->club_id)
                                         ->whereIn('role_in_club', ['chunhiem','bandieuhanh'])
                                         ->inRandomOrder()
                                         ->first();

            if (!$evaluatorMember) continue;

            $evaluator_id = $evaluatorMember->user_id;

            // Chọn 1-3 thành viên để đánh giá
            $membersToEvaluate = $participants->filter(fn($uid) => $uid != $evaluator_id)
                                              ->shuffle()
                                              ->take(rand(1,3));

            foreach ($membersToEvaluate as $member_id) {
                $evaluations[] = [
                    'event_id' => $event->id,
                    'club_id' => $event->club_id,
                    'evaluator_id' => $evaluator_id,
                    'member_id' => $member_id,
                    'score' => rand(6,10),
                    'comment' => 'Đánh giá tích cực cho thành viên ' . $member_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Chèn dữ liệu vào DB
        EventMemberEvaluation::insert($evaluations);
    }
}
