<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Club;
use App\Models\Field;
use App\Models\User;
use Illuminate\Support\Str;
class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clubs  = [
            ['name' => 'CLB Bóng đá', 'description' => 'Nơi tập hợp các bạn yêu thích bóng đá', 'field' => 'Bóng đá', 'owner_email' => 'tranthib@university.edu.vn'],
            ['name' => 'CLB Bóng rổ', 'description' => 'Hoạt động giao lưu và thi đấu bóng rổ', 'field' => 'Bóng rổ', 'owner_email' => 'levanc@university.edu.vn'],
            ['name' => 'CLB Cầu lông', 'description' => 'Rèn luyện sức khỏe với cầu lông', 'field' => 'Cầu lông', 'owner_email' => 'phamthid@university.edu.vn'],
            ['name' => 'CLB Bóng chuyền', 'description' => 'Hoạt động đồng đội, thi đấu bóng chuyền', 'field' => 'Bóng chuyền', 'owner_email' => 'hoangvane@university.edu.vn'],
            ['name' => 'CLB Bơi lội', 'description' => 'Tập luyện bơi lội, nâng cao thể lực', 'field' => 'Bơi lội', 'owner_email' => 'nguyenthif@university.edu.vn'],
            ['name' => 'CLB Điền kinh', 'description' => 'Chạy, nhảy, ném – rèn luyện thể lực', 'field' => 'Điền kinh', 'owner_email' => 'tranvang@university.edu.vn'],
            ['name' => 'CLB Cờ vua', 'description' => 'Môn trí tuệ, phát triển tư duy logic', 'field' => 'Cờ vua', 'owner_email' => 'lethih@university.edu.vn'],
            ['name' => 'CLB Âm nhạc', 'description' => 'Đam mê ca hát và biểu diễn nhạc cụ', 'field' => 'Âm nhạc', 'owner_email' => 'phamvani@university.edu.vn'],
            ['name' => 'CLB Mỹ thuật', 'description' => 'Vẽ tranh, thiết kế, sáng tạo nghệ thuật', 'field' => 'Mỹ thuật', 'owner_email' => 'dothij@university.edu.vn'],
            ['name' => 'CLB Khoa học công nghệ', 'description' => 'Nghiên cứu, chế tạo robot, lập trình, STEM', 'field' => 'Khoa học công nghệ', 'owner_email' => 'admin@university.edu.vn']
        ];
        foreach ($clubs as $clubData) {
            
            $field = Field::where('name', $clubData['field'])->first();
            if (!$field) {
                continue; 
            }
            $owner = User::where('email', $clubData['owner_email'])->first();
            if (!$owner) {
                continue; 
            }
            Club::create([
                'name' => $clubData['name'],
                'slug' => Str::slug($clubData['name']),
                'description' => $clubData['description'],
                'logo' => 'images/logos/logo_club.png',
                'field_id' => $field->id,
                'owner_id' => $owner->id,
                'max_members' => rand(50, 100),
                'status' => 'active',
            ]);
        }
    }
}
