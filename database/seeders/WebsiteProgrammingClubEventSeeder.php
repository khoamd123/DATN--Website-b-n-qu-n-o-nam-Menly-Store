<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Club;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WebsiteProgrammingClubEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tìm CLB LẬP TRÌNH WEBSITE
        $club = Club::where('name', 'LIKE', '%LẬP TRÌNH WEBSITE%')
            ->orWhere('name', 'LIKE', '%LẬP TRÌNH%')
            ->orWhere('name', 'LIKE', '%WEBSITE%')
            ->first();

        if (!$club) {
            $this->command->warn('Không tìm thấy CLB LẬP TRÌNH WEBSITE. Vui lòng tạo CLB trước.');
            return;
        }

        // Tìm user để làm created_by (ưu tiên owner, sau đó là leader)
        $createdBy = $club->owner_id;
        if (!$createdBy) {
            $leader = \App\Models\ClubMember::where('club_id', $club->id)
                ->where('position', 'leader')
                ->first();
            if ($leader) {
                $createdBy = $leader->user_id;
            }
        }

        if (!$createdBy) {
            // Nếu không có owner hoặc leader, lấy user đầu tiên
            $createdBy = User::first()->id ?? 1;
        }

        $events = [
            [
                'title' => 'Workshop Lập trình Web với React và Node.js',
                'description' => 'Tham gia workshop để học cách xây dựng ứng dụng web hiện đại với React và Node.js. Sự kiện bao gồm:
                
• Giới thiệu về React và Node.js
• Hướng dẫn setup môi trường phát triển
• Xây dựng ứng dụng todo list đơn giản
• Tích hợp API với Node.js
• Q&A và chia sẻ kinh nghiệm

Phù hợp cho người mới bắt đầu và có kiến thức cơ bản về JavaScript.',
                'mode' => 'online',
                'location' => 'Phòng học A101 hoặc Online qua Zoom',
                'max_participants' => 50,
                'start_time' => Carbon::now()->addDays(7)->setTime(14, 0),
                'end_time' => Carbon::now()->addDays(7)->setTime(17, 0),
                'registration_deadline' => Carbon::now()->addDays(5),
                'status' => 'approved',
                'visibility' => 'public',
            ],
            [
                'title' => 'Hackathon: Xây dựng Website cho Câu lạc bộ',
                'description' => 'Cuộc thi lập trình hackathon với chủ đề xây dựng website quản lý câu lạc bộ. Các đội sẽ có 24 giờ để hoàn thành dự án.

Thể lệ:
• Thành lập đội từ 2-4 thành viên
• Sử dụng công nghệ tự chọn (React, Vue, Angular, hoặc framework khác)
• Trình bày và demo sản phẩm trước ban giám khảo
• Giải thưởng: 1.000.000 VNĐ cho đội nhất, 500.000 VNĐ cho đội nhì

Đăng ký trước ngày diễn ra sự kiện.',
                'mode' => 'offline',
                'location' => 'Phòng máy tính Khoa CNTT - Tầng 3, Tòa nhà A',
                'max_participants' => 30,
                'start_time' => Carbon::now()->addDays(14)->setTime(8, 0),
                'end_time' => Carbon::now()->addDays(15)->setTime(8, 0),
                'registration_deadline' => Carbon::now()->addDays(12),
                'status' => 'approved',
                'visibility' => 'public',
            ],
            [
                'title' => 'Seminar: Xu hướng Web Development 2026',
                'description' => 'Buổi seminar chia sẻ về các xu hướng công nghệ web mới nhất trong năm 2026.

Nội dung:
• Web3 và Blockchain trong Web Development
• AI/ML tích hợp vào ứng dụng web
• Progressive Web Apps (PWA)
• Serverless Architecture
• Performance Optimization
• Security Best Practices

Diễn giả: Chuyên gia từ các công ty công nghệ hàng đầu Việt Nam.

Sự kiện mở cửa cho tất cả sinh viên quan tâm đến lập trình web.',
                'mode' => 'hybrid',
                'location' => 'Hội trường lớn - Tòa nhà B, hoặc Online',
                'max_participants' => 100,
                'start_time' => Carbon::now()->addDays(21)->setTime(18, 0),
                'end_time' => Carbon::now()->addDays(21)->setTime(20, 30),
                'registration_deadline' => Carbon::now()->addDays(19),
                'status' => 'approved',
                'visibility' => 'public',
            ],
        ];

        foreach ($events as $eventData) {
            // Kiểm tra xem sự kiện đã tồn tại chưa (tránh duplicate)
            $existingEvent = Event::where('club_id', $club->id)
                ->where('title', $eventData['title'])
                ->first();

            if ($existingEvent) {
                $this->command->info("Sự kiện '{$eventData['title']}' đã tồn tại. Bỏ qua.");
                continue;
            }

            Event::create([
                'club_id' => $club->id,
                'created_by' => $createdBy,
                'title' => $eventData['title'],
                'slug' => Str::slug($eventData['title']) . '-' . Str::random(6),
                'description' => $eventData['description'],
                'start_time' => $eventData['start_time'],
                'end_time' => $eventData['end_time'],
                'registration_deadline' => $eventData['registration_deadline'],
                'mode' => $eventData['mode'],
                'location' => $eventData['location'],
                'max_participants' => $eventData['max_participants'],
                'status' => $eventData['status'],
                'visibility' => $eventData['visibility'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("Đã tạo sự kiện: {$eventData['title']}");
        }

        $this->command->info("Hoàn thành! Đã tạo " . count($events) . " sự kiện cho CLB: {$club->name}");
    }
}



