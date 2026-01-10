<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteSamplePosts extends Command
{
    protected $signature = 'app:delete-sample-posts';
    protected $description = 'Delete sample posts generated for clubs (and related comments/attachments)';

    public function handle(): int
    {
        $patterns = [
            'Bài viết mẫu %',
            'Bài viết mẫu%',
            'Hoạt động nổi bật của%',
            'Nhìn lại tuần qua%',
            'Thông tin mới từ%',
            'Điểm tin%',
            'Sự kiện sắp tới của%',
        ];

        $query = \App\Models\Post::query();
        $query->where(function($q) use ($patterns) {
            foreach ($patterns as $p) {
                $q->orWhere('title', 'like', $p);
            }
        });

        $posts = $query->get();
        $count = 0;

        foreach ($posts as $post) {
            // delete related
            try {
                $post->attachments()->delete();
            } catch (\Throwable $e) {}
            try {
                $post->comments()->delete();
            } catch (\Throwable $e) {}
            $post->forceDelete();
            $count++;
        }

        $this->info("Deleted {$count} sample posts.");
        return Command::SUCCESS;
    }
}


