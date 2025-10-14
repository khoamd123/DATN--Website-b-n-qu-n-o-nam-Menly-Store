<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Club;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $clubs = Club::all();

        $posts = [
            [
                'title' => 'Thông báo tập luyện Bóng đá tuần này',
                'content' => 'Các thành viên CLB Bóng đá tập luyện vào thứ 3 và thứ 6 lúc 17h.',
                'type' => 'announcement',
                'status' => 'published',
            ],
            [
                'title' => 'Chia sẻ kỹ thuật Bóng rổ',
                'content' => 'Hướng dẫn cách ném bóng chính xác và phòng thủ hiệu quả.',
                'type' => 'post',
                'status' => 'published',
            ],
            [
                'title' => 'Lịch tập luyện Cầu lông',
                'content' => 'Buổi sáng từ 6h30 đến 8h tại nhà thi đấu trung tâm.',
                'type' => 'announcement',
                'status' => 'published',
            ],
            [
                'title' => 'Giải Bóng chuyền nội bộ',
                'content' => 'Thi đấu giữa các đội trong CLB Bóng chuyền vào cuối tuần.',
                'type' => 'post',
                'status' => 'published',
            ],
            [
                'title' => 'Bơi lội nâng cao',
                'content' => 'Các bài tập bơi kỹ thuật nâng cao cho thành viên CLB.',
                'type' => 'post',
                'status' => 'published',
            ],
            [
                'title' => 'Điền kinh thử sức',
                'content' => 'Các bài tập chạy, nhảy, ném nâng cao thể lực.',
                'type' => 'post',
                'status' => 'published',
            ],
            [
                'title' => 'Học chiến thuật Cờ vua',
                'content' => 'Phân tích ván cờ, nâng cao tư duy logic cho thành viên.',
                'type' => 'post',
                'status' => 'published',
            ],
            [
                'title' => 'Workshop Âm nhạc',
                'content' => 'Biểu diễn nhạc cụ, ca hát và hòa tấu cùng nhau.',
                'type' => 'post',
                'status' => 'published',
            ],
            [
                'title' => 'Workshop Mỹ thuật',
                'content' => 'Thực hành vẽ tranh, sáng tạo nghệ thuật trong CLB.',
                'type' => 'post',
                'status' => 'published',
            ],
            [
                'title' => 'Khám phá Khoa học công nghệ',
                'content' => 'Hoạt động lập trình, STEM và chế tạo robot.',
                'type' => 'post',
                'status' => 'published',
            ],
        ];

        $postsToInsert = [];

        foreach ($clubs as $index => $club) {
            $ownerId = $club->owner_id;
            if (!$ownerId) continue;

            // Lấy post tương ứng dựa vào index (10 CLB, 10 post)
            $data = $posts[$index % count($posts)];

            $postsToInsert[] = [
                'club_id' => $club->id,
                'user_id' => $ownerId,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'content' => $data['content'],
                'type' => $data['type'],
                'status' => $data['status'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
        }

        Post::insert($postsToInsert);
    }
}
