<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Club;
use App\Models\Post;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostSampleSeeder extends Seeder
{
    /**
     * Create 5 posts with images for every club.
     */
    public function run(): void
    {
        $clubs = Club::all();
        if ($clubs->isEmpty()) {
            return;
        }

        // Collect sample images from public/uploads/posts
        $imagesDir = public_path('uploads/posts');
        $imagePatterns = ['*.jpg', '*.jpeg', '*.png', '*.webp', '*.gif'];
        $imageFiles = [];
        foreach ($imagePatterns as $pattern) {
            $imageFiles = array_merge($imageFiles, glob($imagesDir . DIRECTORY_SEPARATOR . $pattern));
        }

        $now = Carbon::now();
        $defaultContents = [
            'Bài viết mẫu nhằm minh hoạ giao diện trang Tin tức cho câu lạc bộ.',
            'Nội dung mẫu: cập nhật hoạt động, hình ảnh và thông tin liên quan CLB.',
            'Bản tin ngắn với ảnh đại diện để kiểm tra hiển thị trên giao diện người dùng.',
            'Chia sẻ hoạt động nổi bật và các điểm nhấn trong tuần của CLB.',
            'Thông tin hữu ích dành cho thành viên và người quan tâm đến CLB.',
        ];

        foreach ($clubs as $club) {
            // Determine how many posts exist for this club; create up to 5 additional
            $existingCount = Post::where('club_id', $club->id)->count();
            $toCreate = 5; // always create 5 as requested

            for ($i = 0; $i < $toCreate; $i++) {
                $title = 'Bài viết mẫu ' . ($i + 1) . ' - ' . $club->name;
                $slugBase = Str::slug($title);
                $slug = $slugBase;
                $k = 1;
                while (Post::where('slug', $slug)->exists()) {
                    $slug = $slugBase . '-' . time() . '-' . $k;
                    $k++;
                }

                // Assign an image if available
                $imagePath = null;
                if (!empty($imageFiles)) {
                    $picked = $imageFiles[($i + $club->id) % count($imageFiles)];
                    $imagePath = 'uploads/posts/' . basename($picked);
                }

                Post::create([
                    'club_id' => $club->id,
                    'user_id' => $club->owner_id ?? 1,
                    'title' => $title,
                    'slug' => $slug,
                    'content' => '<p>' . e($defaultContents[$i % count($defaultContents)]) . '</p>',
                    'type' => 'post',
                    'status' => 'published',
                    'image' => $imagePath,
                    'created_at' => $now->copy()->subDays(rand(0, 14)),
                    'updated_at' => $now,
                ]);
            }
        }
    }
}



