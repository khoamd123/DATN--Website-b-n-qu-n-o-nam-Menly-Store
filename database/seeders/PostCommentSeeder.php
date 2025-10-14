<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\PostComment;
use Carbon\Carbon;

class PostCommentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $posts = Post::all(); 
        $users = User::all();

        $comments = [];

        foreach ($posts as $index => $post) {
            // Lấy 1-2 user ngẫu nhiên, khác chủ post
            $commentUsers = $users->where('id', '!=', $post->user_id)
                                  ->shuffle()
                                  ->take(rand(1,2));

            foreach ($commentUsers as $user) {
                $comments[] = [
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'parent_id' => null,
                    'content' => 'Bình luận post ' . ($index+1),
                    'status' => 'visible',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert tất cả bình luận cùng lúc
        PostComment::insert($comments);
    }
}
