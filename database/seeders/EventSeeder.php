<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Club;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = Club::all();
        $events = [
            [
                'title' => 'Luyện tập Bóng đá cơ bản',
                'description' => 'Buổi luyện tập kỹ thuật và chiến thuật cơ bản cho các thành viên CLB Bóng đá.',
                'mode' => 'public',
                'max_participants' => 30,
            ],
            [
                'title' => 'Giải Bóng rổ nội bộ',
                'description' => 'Tổ chức giải bóng rổ giữa các đội trong CLB để nâng cao tinh thần đồng đội.',
                'mode' => 'public',
                'max_participants' => 25,
            ],
            [
                'title' => 'Tập luyện Cầu lông buổi sáng',
                'description' => 'Luyện tập kỹ thuật đánh cầu lông, nâng cao sức khỏe cho các thành viên.',
                'mode' => 'private',
                'max_participants' => 20,
            ],
            [
                'title' => 'Thi đấu Bóng chuyền giao hữu',
                'description' => 'Buổi thi đấu giao hữu giữa các đội trong CLB Bóng chuyền.',
                'mode' => 'public',
                'max_participants' => 40,
            ],
            [
                'title' => 'Bơi lội nâng cao',
                'description' => 'Tập bơi kỹ thuật nâng cao và rèn luyện thể lực toàn diện.',
                'mode' => 'private',
                'max_participants' => 15,
            ],
            [
                'title' => 'Điền kinh thử sức',
                'description' => 'Các bài tập chạy, nhảy, ném giúp phát triển thể lực và kỹ năng điền kinh.',
                'mode' => 'public',
                'max_participants' => 35,
            ],
            [
                'title' => 'Cờ vua chiến thuật',
                'description' => 'Học các chiến thuật cờ vua và thi đấu nội bộ giữa các thành viên.',
                'mode' => 'private',
                'max_participants' => 20,
            ],
            [
                'title' => 'Hội thảo Âm nhạc',
                'description' => 'Buổi học về ca hát, biểu diễn nhạc cụ và hòa tấu cho các thành viên CLB.',
                'mode' => 'public',
                'max_participants' => 30,
            ],
            [
                'title' => 'Workshop Mỹ thuật',
                'description' => 'Thực hành vẽ tranh, thiết kế sáng tạo trong không gian CLB Mỹ thuật.',
                'mode' => 'private',
                'max_participants' => 15,
            ],
            [
                'title' => 'Khám phá Khoa học công nghệ',
                'description' => 'Hoạt động nghiên cứu, chế tạo robot và lập trình STEM.',
                'mode' => 'public',
                'max_participants' => 25,
            ],
        ];
        foreach ($clubs as $index => $club) {
            $ownerId = $club->owner_id;
            if (!$ownerId) continue;

            $data = $events[$index]; // lấy sự kiện tương ứng CLB

            Event::create([
                'club_id' => $club->id,
                'created_by' => $ownerId,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'],
                'start_time' => Carbon::now()->addDays(rand(1, 30)),
                'end_time' => Carbon::now()->addDays(rand(31, 40)),
                'mode' => $data['mode'],
                'max_participants' => $data['max_participants'],
                'status' => 'active',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
