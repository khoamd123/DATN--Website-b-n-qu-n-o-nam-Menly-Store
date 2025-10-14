<?php

namespace App\Http\Controllers;

use App\Models\ClubResource;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ClubResourceController extends Controller
{
    /**
     * Display a listing of resources
     */
    public function index(Request $request)
    {
        $query = ClubResource::with(['club', 'user']);
        
        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filter by club
        if ($request->has('club_id') && $request->club_id) {
            $query->where('club_id', $request->club_id);
        }
        
        // Filter by resource type
        if ($request->has('resource_type') && $request->resource_type) {
            $query->where('resource_type', $request->resource_type);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'deleted') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->status);
            }
        } else {
            $query->withoutTrashed();
        }
        
        $resources = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.club-resources.index', compact('resources', 'clubs'));
    }

    /**
     * Show the form for creating a new resource
     */
    public function create()
    {
        $clubs = Club::where('status', 'active')->get();
        return view('admin.club-resources.create', compact('clubs'));
    }

    /**
     * Store a newly created resource
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:5|max:255',
            'description' => 'nullable|string|max:1000',
            'resource_type' => 'required|in:form,image,video,pdf,document,guide,other',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:active,inactive,archived',
            'file' => 'nullable|file|max:20480', // 20MB
            'external_link' => 'nullable|url|max:500',
            'tags' => 'nullable|string'
        ], [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.min' => 'Tiêu đề phải có ít nhất :min ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá :max ký tự.',
            'resource_type.required' => 'Vui lòng chọn loại tài nguyên.',
            'club_id.required' => 'Vui lòng chọn câu lạc bộ.',
            'file.max' => 'File không được vượt quá 20MB.',
            'external_link.url' => 'Link không hợp lệ.'
        ]);

        $title = $request->input('title');
        $tags = $request->tags ? explode(',', $request->tags) : null;
        
        $resource = ClubResource::create([
            'title' => $title,
            'slug' => Str::slug($title) . '-' . time(),
            'description' => $request->description,
            'resource_type' => $request->resource_type,
            'club_id' => $request->club_id,
            'user_id' => session('user_id') ?? 1,
            'status' => $request->status,
            'external_link' => $request->external_link,
            'tags' => $tags
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            $this->handleFileUpload($resource, $request->file('file'));
        }

        return redirect()->route('test.club-resources.index')
            ->with('success', 'Tài nguyên đã được tạo thành công!');
    }

    /**
     * Display the specified resource
     */
    public function show($id)
    {
        $resource = ClubResource::with(['club', 'user'])->findOrFail($id);
        $resource->incrementViewCount();
        
        return view('admin.club-resources.show', compact('resource'));
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit($id)
    {
        $resource = ClubResource::findOrFail($id);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.club-resources.edit', compact('resource', 'clubs'));
    }

    /**
     * Update the specified resource
     */
    public function update(Request $request, $id)
    {
        $resource = ClubResource::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|min:5|max:255',
            'description' => 'nullable|string|max:1000',
            'resource_type' => 'required|in:form,image,video,pdf,document,guide,other',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:active,inactive,archived',
            'file' => 'nullable|file|max:20480',
            'external_link' => 'nullable|url|max:500',
            'tags' => 'nullable|string'
        ]);

        $title = $request->input('title');
        $tags = $request->tags ? explode(',', $request->tags) : null;
        
        $resource->update([
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $resource->id,
            'description' => $request->description,
            'resource_type' => $request->resource_type,
            'club_id' => $request->club_id,
            'status' => $request->status,
            'external_link' => $request->external_link,
            'tags' => $tags
        ]);

        // Handle new file upload
        if ($request->hasFile('file')) {
            // Delete old file
            if ($resource->file_path) {
                Storage::delete('public/' . $resource->file_path);
                if ($resource->thumbnail_path) {
                    Storage::delete('public/' . $resource->thumbnail_path);
                }
            }
            
            $this->handleFileUpload($resource, $request->file('file'));
        }

        return redirect()->route('test.club-resources.index')
            ->with('success', 'Tài nguyên đã được cập nhật thành công!');
    }

    /**
     * Update resource status
     */
    public function updateStatus(Request $request, $id)
    {
        $resource = ClubResource::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:active,inactive,archived'
        ]);
        
        $resource->update(['status' => $request->status]);
        
        $statusLabels = [
            'active' => 'Hoạt động',
            'inactive' => 'Không hoạt động',
            'archived' => 'Lưu trữ'
        ];
        
        return redirect()->route('test.club-resources.index')
            ->with('success', "Tài nguyên đã được cập nhật trạng thái thành '{$statusLabels[$request->status]}'!");
    }

    /**
     * Remove the specified resource (soft delete)
     */
    public function destroy($id)
    {
        $resource = ClubResource::findOrFail($id);
        $resource->delete();

        return redirect()->route('test.club-resources.index')
            ->with('success', 'Tài nguyên đã được xóa thành công!');
    }

    /**
     * Download the resource file
     */
    public function download($id)
    {
        $resource = ClubResource::findOrFail($id);
        
        if (!$resource->file_path) {
            return redirect()->back()->with('error', 'Không tìm thấy file để tải xuống.');
        }
        
        $resource->incrementDownloadCount();
        
        return Storage::download('public/' . $resource->file_path, $resource->file_name);
    }

    /**
     * Restore a soft deleted resource
     */
    public function restore($id)
    {
        $resource = ClubResource::onlyTrashed()->findOrFail($id);
        $resource->restore();
        $resource->update(['status' => 'active']);

        return redirect()->route('admin.club-resources.index')
            ->with('success', 'Tài nguyên đã được khôi phục thành công!');
    }

    /**
     * Handle file upload
     */
    private function handleFileUpload($resource, $file)
    {
        $uploadPath = 'club-resources/' . $resource->club_id;
        
        // Generate unique filename
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $fullPath = $uploadPath . '/' . $filename;
        
        // Store file
        $file->storeAs('public/' . $uploadPath, $filename);
        
        // Create thumbnail for images
        $thumbnailPath = null;
        if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])) {
            $thumbnailPath = $this->createThumbnail($file, $uploadPath, $filename);
        }
        
        // Update resource
        $resource->update([
            'file_path' => $fullPath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'thumbnail_path' => $thumbnailPath
        ]);
    }

    /**
     * Create thumbnail for image
     */
    private function createThumbnail($file, $uploadPath, $filename)
    {
        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($file);
            $img->resize(300, 300);
            
            $thumbnailPath = 'public/' . $uploadPath . '/thumbnails';
            $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.' . pathinfo($filename, PATHINFO_EXTENSION);
            
            Storage::makeDirectory($thumbnailPath);
            $img->save(storage_path('app/' . $thumbnailPath . '/' . $thumbnailFilename));
            
            return $uploadPath . '/thumbnails/' . $thumbnailFilename;
        } catch (\Exception $e) {
            Log::error('Thumbnail creation failed: ' . $e->getMessage());
            return null;
        }
    }
}
