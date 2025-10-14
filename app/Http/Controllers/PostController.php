<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostImage;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request)
    {
        $query = Post::with(['club', 'user', 'featuredImage']);
        
        // Filter by search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filter by club
        if ($request->has('club_id') && $request->club_id) {
            $query->where('club_id', $request->club_id);
        }
        
        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $posts = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.posts.index', compact('posts', 'clubs'));
    }

    /**
     * Show the form for creating a new post
     */
    public function create()
    {
        $clubs = Club::where('status', 'active')->get();
        return view('admin.posts.create', compact('clubs'));
    }

    /**
     * Store a newly created post
     */
    public function store(StorePostRequest $request)
    {
        $title = $request->input('title');
        $content = $request->input('content');
        
        $post = Post::create([
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $content,
            'type' => $request->type,
            'club_id' => $request->club_id,
            'user_id' => session('user_id') ?? 1, // Fallback to admin user
            'status' => $request->status
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $this->handleImageUploads($post, $request->file('images'), $request->input('featured_image'));
        }

        return redirect()->route('admin.posts')
            ->with('success', 'Bài viết đã được tạo thành công!');
    }

    /**
     * Display the specified post
     */
    public function show($id)
    {
        $post = Post::with(['club', 'user', 'comments', 'images'])->findOrFail($id);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post
     */
    public function edit($id)
    {
        $post = Post::findOrFail($id);
        $clubs = Club::where('status', 'active')->get();
        return view('admin.posts.edit', compact('post', 'clubs'));
    }

    /**
     * Update the specified post
     */
    public function update(UpdatePostRequest $request, $id)
    {
        $post = Post::findOrFail($id);
        
        $title = $request->input('title');
        $content = $request->input('content');
        
        $post->update([
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $content,
            'type' => $request->type,
            'club_id' => $request->club_id,
            'status' => $request->status
        ]);

        return redirect()->route('admin.posts')
            ->with('success', 'Bài viết đã được cập nhật thành công!');
    }

    /**
     * Remove the specified post (soft delete)
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->update(['status' => 'deleted']);

        return redirect()->route('admin.posts')
            ->with('success', 'Bài viết đã được xóa thành công!');
    }

    /**
     * Update post status
     */
    public function updateStatus(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:published,hidden,deleted'
        ]);
        
        $post->update([
            'status' => $request->status
        ]);
        
        $statusLabels = [
            'published' => 'Đã xuất bản',
            'hidden' => 'Ẩn',
            'deleted' => 'Đã xóa'
        ];
        
        return redirect()->route('admin.posts')
            ->with('success', "Bài viết đã được chuyển sang trạng thái: {$statusLabels[$request->status]}");
    }

    /**
     * Restore deleted post
     */
    public function restore($id)
    {
        $post = Post::findOrFail($id);
        $post->update(['status' => 'published']);

        return redirect()->route('admin.posts')
            ->with('success', 'Bài viết đã được khôi phục thành công!');
    }

    /**
     * Handle image uploads for posts
     */
    private function handleImageUploads($post, $images, $featuredImageIndex = null)
    {
        $uploadPath = 'posts/' . $post->id;
        
        foreach ($images as $index => $image) {
            // Generate unique filename
            $filename = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
            $fullPath = $uploadPath . '/' . $filename;
            
            // Store original image
            $image->storeAs('public/' . $uploadPath, $filename);
            
            // Create thumbnail
            $this->createThumbnail($image, $uploadPath, $filename);
            
            // Save image record
            PostImage::create([
                'post_id' => $post->id,
                'image_path' => $fullPath,
                'image_name' => $image->getClientOriginalName(),
                'image_type' => 'gallery',
                'sort_order' => $index,
                'alt_text' => $post->title,
                'is_featured' => $featuredImageIndex !== null && $index == $featuredImageIndex
            ]);
        }
    }

    /**
     * Create thumbnail for uploaded image
     */
    private function createThumbnail($image, $uploadPath, $filename)
    {
        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($image);
            $img->resize(300, 300);
            
            $thumbnailPath = 'public/' . $uploadPath . '/thumbnails';
            $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.' . pathinfo($filename, PATHINFO_EXTENSION);
            
            Storage::makeDirectory($thumbnailPath);
            $img->save(storage_path('app/' . $thumbnailPath . '/' . $thumbnailFilename));
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            Log::error('Thumbnail creation failed: ' . $e->getMessage());
        }
    }
}
