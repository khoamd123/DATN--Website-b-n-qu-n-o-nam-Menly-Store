<?php

namespace Database\Seeders;

use App\Models\Field;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [
            ['name' => 'Bóng đá', 'description' => 'Môn thể thao vua, phổ biến nhất Việt Nam'],
            ['name' => 'Bóng rổ', 'description' => 'Môn thể thao đối kháng, phát triển thể lực và chiều cao'],
            ['name' => 'Cầu lông', 'description' => 'Môn thể thao nhẹ nhàng, chơi trong nhà hoặc ngoài trời'],
            ['name' => 'Bóng chuyền', 'description' => 'Môn thể thao đồng đội, thi đấu trên sân lưới'],
            ['name' => 'Bơi lội', 'description' => 'Môn thể thao dưới nước, rèn luyện sức khỏe toàn diện'],
            ['name' => 'Điền kinh', 'description' => 'Bao gồm chạy, nhảy, ném – rèn luyện thể lực'],
            ['name' => 'Cờ vua', 'description' => 'Môn trí tuệ, phát triển tư duy logic'],
            ['name' => 'Âm nhạc', 'description' => 'Hoạt động ca hát, biểu diễn nhạc cụ, hòa tấu'],
            ['name' => 'Mỹ thuật', 'description' => 'Vẽ tranh, thiết kế, sáng tạo nghệ thuật'],
            ['name' => 'Khoa học công nghệ', 'description' => 'Nghiên cứu, chế tạo robot, lập trình, STEM'],
        ];
        foreach ($fields as $field) {
            Field::create([
                'name' => $field['name'],
                'slug' => Str::slug($field['name']),
                'description' => $field['description'],
            ]);
        }
    }
}
