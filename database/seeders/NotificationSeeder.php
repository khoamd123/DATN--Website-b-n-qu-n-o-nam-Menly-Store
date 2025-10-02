<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('notifications')->insert([
            [
                'sender_id' => 1,
                'title' => 'Thông báo họp CLB',
                'message' => 'Các thành viên vui lòng có mặt đúng giờ.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 2,
                'title' => 'Thông báo sự kiện thể thao',
                'message' => 'Hãy đăng ký tham gia sự kiện trước ngày 10/10.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 3,
                'title' => 'Nhắc nhở tham gia workshop',
                'message' => 'Workshop sẽ diễn ra tại phòng A1, xin chuẩn bị.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 1,
                'title' => 'Thông báo thay đổi lịch hoạt động',
                'message' => 'Lịch hoạt động CLB đã được cập nhật, kiểm tra lại lịch cá nhân.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 2,
                'title' => 'Thông báo kết quả cuộc thi',
                'message' => 'Kết quả cuộc thi đã được đăng trên bảng tin CLB.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 3,
                'title' => 'Thông báo đăng ký sự kiện',
                'message' => 'Vui lòng hoàn tất đăng ký trước ngày 12/10.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 1,
                'title' => 'Thông báo nghỉ họp',
                'message' => 'Buổi họp ngày mai sẽ hoãn, theo dõi thông báo tiếp theo.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 2,
                'title' => 'Thông báo học bổng',
                'message' => 'Các thành viên có thể nộp hồ sơ học bổng từ tuần sau.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 3,
                'title' => 'Thông báo huấn luyện',
                'message' => 'Buổi huấn luyện sẽ diễn ra vào thứ 7, tại sân tập CLB.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'sender_id' => 1,
                'title' => 'Thông báo kiểm tra thành tích',
                'message' => 'Yêu cầu các thành viên nộp báo cáo thành tích trước cuối tháng.',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ]);
    }
}
