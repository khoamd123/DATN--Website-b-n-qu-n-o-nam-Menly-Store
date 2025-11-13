<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request)
    {
        $query = Post::where('type', 'post')->with(['club', 'user']);

        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Lọc theo CLB
        if ($request->has('club_id') && $request->club_id) {
            $query->where('club_id', $request->club_id);
        }

        // Lọc theo trạng thái
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
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:draft,published,archived',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();
        $data['user_id'] = session('user_id');
        $data['type'] = 'post';

        // Xử lý upload ảnh
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('public/posts', $filename);
            $data['image'] = 'posts/' . $filename;
        }

        Post::create($data);

        return redirect()->route('admin.posts')->with('success', 'Tạo bài viết thành công!');
    }

    /**
     * Display the specified post
     */
    public function show($id)
    {
        $post = Post::with(['club', 'user'])->findOrFail($id);
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
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:draft,published,archived',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Xử lý upload ảnh mới
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($post->image) {
                Storage::delete('public/' . $post->image);
            }

            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('public/posts', $filename);
            $data['image'] = 'posts/' . $filename;
        }

        $post->update($data);

        return redirect()->route('admin.posts')->with('success', 'Cập nhật bài viết thành công!');
    }

    /**
     * Remove the specified post
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->route('admin.posts')->with('success', 'Xóa bài viết thành công!');
    }

    /**
     * Update post status
     */
    public function updateStatus(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        $post->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái bài viết thành công!');
    }

    /**
     * Restore soft deleted post
     */
    public function restore($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->restore();

        return redirect()->route('admin.posts')->with('success', 'Khôi phục bài viết thành công!');
    }

    /**
     * Upload image from editor and return public URL (AJAX)
     */
    public function uploadEditorImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $path = $request->file('image')->store('public/posts/content');
        $url = Storage::url($path); // e.g. /storage/posts/content/xxx.jpg

        return response()->json([
            'url' => asset($url),
        ]);
    }
}
