<?php

namespace App\Http\Controllers;

use App\Models\ClubResource;
use App\Models\ClubResourceFile;
use App\Models\ClubResourceImage;
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
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:active,inactive,archived',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|mimes:doc,docx,xls,xlsx|max:20480', // 20MB per file
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,avi,mov|max:5120', // 5MB per file
            'external_link' => 'nullable|url|max:500',
            'tags' => 'nullable|string'
        ], [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.min' => 'Tiêu đề phải có ít nhất :min ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá :max ký tự.',
            'club_id.required' => 'Vui lòng chọn câu lạc bộ.',
            'files.max' => 'Tối đa 10 file.',
            'files.*.mimes' => 'File phải có định dạng: doc, docx, xls, xlsx.',
            'files.*.max' => 'Mỗi file không được vượt quá 20MB.',
            'images.max' => 'Tối đa 10 file.',
            'images.*.mimes' => 'File phải có định dạng: jpeg, png, jpg, gif, mp4, avi, mov.',
            'images.*.max' => 'Mỗi file không được vượt quá 5MB.',
            'external_link.url' => 'Link không hợp lệ.'
        ]);

        $title = $request->input('title');
        $tags = $request->tags ? explode(',', $request->tags) : null;
        
        $resource = ClubResource::create([
            'title' => $title,
            'slug' => Str::slug($title) . '-' . time(),
            'description' => $request->description,
            'resource_type' => 'other', // Default value
            'club_id' => $request->club_id,
            'user_id' => session('user_id') ?? 1,
            'status' => $request->status,
            'external_link' => $request->external_link,
            'tags' => $tags
        ]);

        // Handle file album upload
        if ($request->hasFile('files')) {
            $this->handleFileAlbumUpload($resource, $request->file('files'));
        }

        // Handle image album upload
        if ($request->hasFile('images')) {
            $this->handleImageAlbumUpload($resource, $request->file('images'));
        }

        return redirect()->route('admin.club-resources.index')
            ->with('success', 'Tài nguyên đã được tạo thành công!');
    }

    /**
     * Display the specified resource
     */
    public function show($id)
    {
        $resource = ClubResource::with(['club', 'user', 'images', 'files'])->findOrFail($id);
        $resource->incrementViewCount();
        
        return view('admin.club-resources.show', compact('resource'));
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit($id)
    {
        $resource = ClubResource::with(['images', 'files'])->findOrFail($id);
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
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:active,inactive,archived',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|mimes:doc,docx,xls,xlsx|max:20480', // 20MB per file
            'deleted_files' => 'nullable|array',
            'deleted_files.*' => 'integer|exists:club_resource_files,id',
            'primary_file_id' => 'nullable|integer|exists:club_resource_files,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,avi,mov|max:5120', // 5MB per file
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'integer|exists:club_resource_images,id',
            'primary_image_id' => 'nullable|integer|exists:club_resource_images,id',
            'external_link' => 'nullable|url|max:500',
            'tags' => 'nullable|string'
        ], [
            'files.max' => 'Tối đa 10 file.',
            'files.*.mimes' => 'File phải có định dạng: doc, docx, xls, xlsx.',
            'files.*.max' => 'Mỗi file không được vượt quá 20MB.',
            'deleted_files.*.exists' => 'File không tồn tại.',
            'primary_file_id.exists' => 'File chính không tồn tại.',
            'images.max' => 'Tối đa 10 file.',
            'images.*.mimes' => 'File phải có định dạng: jpeg, png, jpg, gif, mp4, avi, mov.',
            'images.*.max' => 'Mỗi file không được vượt quá 5MB.',
            'deleted_images.*.exists' => 'Hình ảnh không tồn tại.',
            'primary_image_id.exists' => 'Hình ảnh chính không tồn tại.'
        ]);

        $title = $request->input('title');
        $tags = $request->tags ? explode(',', $request->tags) : null;
        
        $resource->update([
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $resource->id,
            'description' => $request->description,
            'club_id' => $request->club_id,
            'status' => $request->status,
            'external_link' => $request->external_link,
            'tags' => $tags
        ]);

        // Handle file album updates
        $this->handleFileAlbumUpdate($resource, $request);

        // Handle image album updates
        $this->handleImageAlbumUpdate($resource, $request);

        return redirect()->route('admin.club-resources.index')
            ->with('success', 'Tài nguyên đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource (soft delete)
     */
    public function destroy($id)
    {
        $resource = ClubResource::findOrFail($id);
        $resource->delete();

        return redirect()->route('admin.club-resources.index')
            ->with('success', 'Tài nguyên đã được chuyển vào thùng rác!');
    }

    /**
     * Show trash (soft deleted resources)
     */
    public function trash()
    {
        $resources = ClubResource::onlyTrashed()
            ->with(['club', 'user'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);
        
        return view('admin.club-resources.trash', compact('resources'));
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
     * Force delete resource permanently
     */
    public function forceDelete($id)
    {
        $resource = ClubResource::onlyTrashed()->findOrFail($id);
        
        // Delete associated files
        if ($resource->file_path) {
            Storage::delete('public/' . $resource->file_path);
        }
        
        if ($resource->thumbnail_path) {
            Storage::delete('public/' . $resource->thumbnail_path);
        }
        
        // Delete associated images and files
        foreach ($resource->images as $image) {
            if ($image->image_path) {
                Storage::delete('public/' . $image->image_path);
            }
            if ($image->thumbnail_path) {
                Storage::delete('public/' . $image->thumbnail_path);
            }
        }
        
        foreach ($resource->files as $file) {
            if ($file->file_path) {
                Storage::delete('public/' . $file->file_path);
            }
            if ($file->thumbnail_path) {
                Storage::delete('public/' . $file->thumbnail_path);
            }
        }
        
        $resource->forceDelete();

        return redirect()->route('admin.club-resources.trash')
            ->with('success', 'Tài nguyên đã được xóa vĩnh viễn!');
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
            
            // For videos, we'll create a thumbnail from the first frame
            if (str_contains($file->getMimeType(), 'video')) {
                // For now, return null for video thumbnails
                // In a real implementation, you'd use FFmpeg to extract a frame
                return null;
            }
            
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

    /**
     * Handle image album upload
     */
    private function handleImageAlbumUpload($resource, $images)
    {
        $uploadPath = 'club-resources/' . $resource->club_id . '/images';
        
        foreach ($images as $index => $image) {
            // Generate unique filename
            $filename = time() . '_' . $index . '_' . Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $image->getClientOriginalExtension();
            $fullPath = $uploadPath . '/' . $filename;
            
            // Store image
            $image->storeAs('public/' . $uploadPath, $filename);
            
            // Create thumbnail for images and videos
            $thumbnailPath = null;
            if (str_contains($image->getMimeType(), 'image') || str_contains($image->getMimeType(), 'video')) {
                $thumbnailPath = $this->createThumbnail($image, $uploadPath, $filename);
            }
            
            // Create image record
            $resource->images()->create([
                'image_path' => $fullPath,
                'image_name' => $image->getClientOriginalName(),
                'image_type' => $image->getMimeType(),
                'image_size' => $image->getSize(),
                'thumbnail_path' => $thumbnailPath,
                'sort_order' => $index,
                'is_primary' => $index === 0 // First image is primary
            ]);
        }
    }

    /**
     * Handle file album upload
     */
    private function handleFileAlbumUpload($resource, $files)
    {
        $uploadPath = 'club-resources/' . $resource->club_id . '/files';
        
        foreach ($files as $index => $file) {
            // Generate unique filename
            $filename = time() . '_' . $index . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $fullPath = $uploadPath . '/' . $filename;
            
            // Store file
            $file->storeAs('public/' . $uploadPath, $filename);
            
            // Create thumbnail for images and videos
            $thumbnailPath = null;
            if (str_contains($file->getMimeType(), 'image') || str_contains($file->getMimeType(), 'video')) {
                $thumbnailPath = $this->createThumbnail($file, $uploadPath, $filename);
            }
            
            // Create file record
            $resource->files()->create([
                'file_path' => $fullPath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'thumbnail_path' => $thumbnailPath,
                'sort_order' => $index,
                'is_primary' => $index === 0 // First file is primary
            ]);
        }
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload($resource, $image)
    {
        $uploadPath = 'club-resources/' . $resource->club_id . '/images';
        
        // Generate unique filename
        $filename = time() . '_' . Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $image->getClientOriginalExtension();
        $fullPath = $uploadPath . '/' . $filename;
        
        // Store image
        $image->storeAs('public/' . $uploadPath, $filename);
        
        // Create thumbnail
        $thumbnailPath = $this->createThumbnail($image, $uploadPath, $filename);
        
        // Update resource with image info
        $resource->update([
            'thumbnail_path' => $thumbnailPath
        ]);
    }

    /**
     * Handle file album update
     */
    private function handleFileAlbumUpdate($resource, $request)
    {
        // Debug: Log request data
        Log::info('File Album Update Request:', [
            'deleted_files' => $request->deleted_files,
            'primary_file_id' => $request->primary_file_id,
            'has_files' => $request->hasFile('files'),
            'files_count' => $request->hasFile('files') ? count($request->file('files')) : 0,
            'all_request_data' => $request->all()
        ]);

        // Delete selected files
        if ($request->has('deleted_files')) {
            foreach ($request->deleted_files as $fileId) {
                $file = ClubResourceFile::find($fileId);
                if ($file) {
                    // Delete files from storage
                    Storage::delete('public/' . $file->file_path);
                    if ($file->thumbnail_path) {
                        Storage::delete('public/' . $file->thumbnail_path);
                    }
                    
                    // Delete from database
                    $file->delete();
                }
            }
        }

        // Set primary file
        if ($request->has('primary_file_id')) {
            // Remove primary from all files
            ClubResourceFile::where('club_resource_id', $resource->id)
                ->update(['is_primary' => false]);
            
            // Set new primary
            ClubResourceFile::where('id', $request->primary_file_id)
                ->where('club_resource_id', $resource->id)
                ->update(['is_primary' => true]);
        }

        // Add new files
        if ($request->hasFile('files')) {
            $this->handleFileAlbumUpload($resource, $request->file('files'));
        }
    }

    /**
     * Handle image album updates (add, delete, set primary)
     */
    private function handleImageAlbumUpdate($resource, $request)
    {
        // Debug: Log request data
        Log::info('Image Album Update Request:', [
            'deleted_images' => $request->deleted_images,
            'primary_image_id' => $request->primary_image_id,
            'has_images' => $request->hasFile('images'),
            'images_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'all_request_data' => $request->all()
        ]);

        // Delete selected images
        if ($request->has('deleted_images')) {
            foreach ($request->deleted_images as $imageId) {
                $image = ClubResourceImage::find($imageId);
                if ($image) {
                    // Delete files from storage
                    Storage::delete('public/' . $image->image_path);
                    if ($image->thumbnail_path) {
                        Storage::delete('public/' . $image->thumbnail_path);
                    }
                    
                    // Delete from database
                    $image->delete();
                }
            }
        }

        // Set primary image
        if ($request->has('primary_image_id')) {
            // Remove primary from all images
            ClubResourceImage::where('club_resource_id', $resource->id)
                ->update(['is_primary' => false]);
            
            // Set new primary
            ClubResourceImage::where('id', $request->primary_image_id)
                ->where('club_resource_id', $resource->id)
                ->update(['is_primary' => true]);
        }

        // Add new images
        if ($request->hasFile('images')) {
            $this->handleImageAlbumUpload($resource, $request->file('images'));
        }
    }
}
