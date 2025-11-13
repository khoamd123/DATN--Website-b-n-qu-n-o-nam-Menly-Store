<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubJoinRequest;
use App\Models\Event;
use App\Models\Field;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\ClubJoinRequest as JoinReq;
use App\Models\Fund;
use App\Models\FundTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Check if user is logged in as student
     */
    private function checkStudentAuth()
    {
        if (!session('user_id') || session('is_admin')) {
            if (session('is_admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập với tài khoản sinh viên.');
        }

        $user = User::with('clubs')->find(session('user_id'));
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_email', 'is_admin']);
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn.');
        }

        return $user;
    }

    /**
     * Display student dashboard
     */
    public function dashboard()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.dashboard', compact('user'));
    }

    public function clubs()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.clubs.index', compact('user'));
    }

    public function events()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.events.index', compact('user'));
    }

    public function profile()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.profile.index', compact('user'));
    }

    public function notifications()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.notifications.index', compact('user'));
    }

    public function contact()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.contact', compact('user'));
    }

    public function clubManagement()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Luôn hiển thị trang, nhưng kiểm tra quyền để hiển thị nội dung phù hợp
        $hasManagementRole = false;
        $clubId = null;
        $userPosition = null;
        $userClub = null;
        $clubStats = [
            'members' => ['active' => 0, 'pending' => 0],
            'events' => ['total' => 0, 'upcoming' => 0],
            'announcements' => ['total' => 0, 'today' => 0],
        ];
        $clubMembers = collect();
        $allPermissions = collect();
        
        if ($user->clubs->count() > 0) {
            $userClub = $user->clubs->first();
            $clubId = $userClub->id;
            $userPosition = $user->getPositionInClub($clubId);
            $hasManagementRole = in_array($userPosition, ['leader', 'vice_president', 'officer']);

            // Tính toán thống kê cơ bản cho view
            $activeMembers = ClubMember::where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->count();
            $pendingMembers = ClubJoinRequest::where('club_id', $clubId)
                ->where('status', 'pending')
                ->count();
            $totalEvents = Event::where('club_id', $clubId)->count();
            $upcomingEvents = Event::where('club_id', $clubId)
                ->where('start_time', '>=', now())
                ->count();
            $totalAnnouncements = Post::where('club_id', $clubId)
                ->where('type', 'announcement')
                ->where('status', '!=', 'deleted')
                ->count();
            $todayAnnouncements = Post::where('club_id', $clubId)
                ->where('type', 'announcement')
                ->whereDate('created_at', now()->toDateString())
                ->count();

            $totalResources = \App\Models\ClubResource::where('club_id', $clubId)->count();
            $totalFiles = \App\Models\ClubResourceFile::whereHas('clubResource', function($q) use ($clubId) {
                $q->where('club_id', $clubId);
            })->count();

            $clubStats = [
                'members' => ['active' => $activeMembers, 'pending' => $pendingMembers],
                'events' => ['total' => $totalEvents, 'upcoming' => $upcomingEvents],
                'announcements' => ['total' => $totalAnnouncements, 'today' => $todayAnnouncements],
                'resources' => ['total' => $totalResources, 'files' => $totalFiles],
            ];

            // Danh sách thành viên (phục vụ các thẻ hoặc view cần)
            $clubMembers = ClubMember::with('user')
                ->where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->orderByRaw("FIELD(position, 'leader', 'vice_president', 'officer', 'member') ASC")
                ->orderByDesc('joined_at')
                ->get()
                ->map(function ($member) use ($clubId) {
                    $member->permission_names = $member->user
                        ? $member->user->getClubPermissions($clubId)
                        : [];
                    return $member;
                });
            $allPermissions = Permission::orderBy('name')->get();
        }

        // Truyền thêm $clubId để tránh lỗi undefined variable trong view
        return view(
            'student.club-management.index',
            compact('user', 'hasManagementRole', 'userPosition', 'userClub', 'clubId', 'clubStats', 'clubMembers', 'allPermissions')
        );
    }

    /**
     * Members management page
     */
    public function manageMembers($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        // Only leader/vice/officer can access members management
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president', 'officer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý thành viên CLB này.');
        }

        // Danh sách thành viên + gán quyền hiện có
        $clubMembers = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->orderByRaw("FIELD(position, 'leader', 'vice_president', 'officer', 'member') ASC")
            ->orderByDesc('joined_at')
            ->get()
            ->map(function ($member) use ($clubId) {
                $member->permission_names = $member->user
                    ? $member->user->getClubPermissions($clubId)
                    : [];
                return $member;
            });

        $allPermissions = Permission::orderBy('name')->get();

        return view('student.club-management.members', [
            'user' => $user,
            'club' => $club,
            'clubMembers' => $clubMembers,
            'allPermissions' => $allPermissions,
            'userPosition' => $position,
            'clubId' => $clubId,
        ]);
    }

    /**
     * Club settings page
     */
    public function clubSettings($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::with(['leader'])->findOrFail($clubId);

        // Only leader can access settings
        if ($user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB mới có quyền truy cập trang cài đặt.');
        }

        $fields = Field::orderBy('name')->get();

        // Simple stats for right sidebar
        $clubStats = [
            'members' => ClubMember::where('club_id', $clubId)->whereIn('status', ['approved', 'active'])->count(),
            'events' => Event::where('club_id', $clubId)->count(),
            'posts' => Post::where('club_id', $clubId)->count(),
        ];

        return view('student.club-management.settings', [
            'user' => $user,
            'club' => $club,
            'clubId' => $clubId,
            'fields' => $fields,
            'clubStats' => $clubStats,
        ]);
    }

    /**
     * Update club settings
     */
    public function updateClubSettings(Request $request, $clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        if ($user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới có quyền cập nhật cài đặt.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_members' => 'required|integer|min:1|max:1000',
            'field_id' => 'nullable|exists:fields,id',
            'logo' => 'nullable|image|max:2048',
        ]);

        $update = [
            'name' => $request->name,
            'description' => $request->description,
            'max_members' => $request->max_members,
        ];
        if ($request->filled('field_id')) {
            $update['field_id'] = $request->field_id;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $ext = $logo->getClientOriginalExtension();
            $filename = time() . '_' . $clubId . '.' . $ext;
            $dir = public_path('uploads/clubs/logos');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $logo->move($dir, $filename);
            // delete old
            if ($club->logo && file_exists(public_path($club->logo))) {
                @unlink(public_path($club->logo));
            }
            $update['logo'] = 'uploads/clubs/logos/' . $filename;
        }

        $club->update($update);

        return redirect()
            ->route('student.club-management.settings', ['club' => $clubId])
            ->with('success', 'Đã cập nhật cài đặt CLB thành công.');
    }

    /**
     * Club resources management page
     */
    public function clubResources($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        // Check permission: leader, vice_president, officer, or has permission
        $position = $user->getPositionInClub($clubId);
        $hasPermission = in_array($position, ['leader', 'vice_president', 'officer']) 
            || $user->hasPermission('quan_ly_clb', $clubId) 
            || $user->hasPermission('dang_thong_bao', $clubId);
        
        if (!$hasPermission) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý tài nguyên CLB này.');
        }

        // Get resources for this club
        $resources = \App\Models\ClubResource::with(['user', 'images', 'files'])
            ->where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get stats
        $totalResources = \App\Models\ClubResource::where('club_id', $clubId)->count();
        $totalFiles = \App\Models\ClubResourceFile::whereHas('clubResource', function($q) use ($clubId) {
            $q->where('club_id', $clubId);
        })->count();

        return view('student.club-management.resources', compact(
            'user', 
            'club', 
            'clubId', 
            'resources', 
            'totalResources', 
            'totalFiles'
        ));
    }

    /**
     * Show create resource form for student
     */
    public function createClubResource($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        // Check permission: leader, vice_president, officer, or has permission
        $position = $user->getPositionInClub($clubId);
        $hasPermission = in_array($position, ['leader', 'vice_president', 'officer']) 
            || $user->hasPermission('quan_ly_clb', $clubId) 
            || $user->hasPermission('dang_thong_bao', $clubId);
        
        if (!$hasPermission) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền tạo tài nguyên CLB này.');
        }

        return view('student.club-management.resources-create', compact('user', 'club', 'clubId'));
    }

    /**
     * Store resource created by student
     */
    public function storeClubResource(Request $request, $clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        // Check permission
        $position = $user->getPositionInClub($clubId);
        $hasPermission = in_array($position, ['leader', 'vice_president', 'officer']) 
            || $user->hasPermission('quan_ly_clb', $clubId) 
            || $user->hasPermission('dang_thong_bao', $clubId);
        
        if (!$hasPermission) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền tạo tài nguyên CLB này.');
        }

        // Validate files manually first to show specific file names
        $fileErrors = [];
        if ($request->hasFile('files')) {
            // Allowed MIME types for different file formats
            $allowedMimes = [
                // DOC files - can have different MIME types
                'application/msword',
                'application/x-msword',
                'application/vnd.ms-word',
                // DOCX files
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.wordprocessingml',
                // XLS files
                'application/vnd.ms-excel',
                'application/x-msexcel',
                'application/x-excel',
                // XLSX files
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.spreadsheetml',
                // PDF files
                'application/pdf',
                'application/x-pdf',
            ];
            
            // Allowed extensions
            $allowedExtensions = ['doc', 'docx', 'xls', 'xlsx', 'pdf'];
            
            foreach ($request->file('files') as $index => $file) {
                $mimeType = $file->getMimeType();
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = $file->getClientOriginalName();
                
                // Check both MIME type and extension - accept if either is valid
                $isValidMime = in_array($mimeType, $allowedMimes);
                $isValidExtension = in_array($extension, $allowedExtensions);
                
                if (!$isValidMime && !$isValidExtension) {
                    // Log for debugging
                    \Log::info('File validation failed', [
                        'file' => $fileName,
                        'mime_type' => $mimeType,
                        'extension' => $extension
                    ]);
                    $fileErrors["files.$index"] = "File \"{$fileName}\" không đúng định dạng. Chỉ chấp nhận: DOC, DOCX, XLS, XLSX, PDF. (Định dạng hiện tại: .{$extension}, MIME: {$mimeType})";
                }
                
                if ($file->getSize() > 20480 * 1024) {
                    $fileErrors["files.$index"] = "File \"{$fileName}\" vượt quá 20MB.";
                }
            }
        }

        $imageErrors = [];
        if ($request->hasFile('images')) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 
                             'video/mp4', 'video/x-msvideo', 'video/quicktime'];
            foreach ($request->file('images') as $index => $image) {
                if (!in_array($image->getMimeType(), $allowedMimes)) {
                    $imageErrors["images.$index"] = "File \"{$image->getClientOriginalName()}\" không đúng định dạng. Chỉ chấp nhận: JPG, PNG, GIF, MP4, AVI, MOV.";
                }
                if ($image->getSize() > 102400 * 1024) {
                    $imageErrors["images.$index"] = "File \"{$image->getClientOriginalName()}\" vượt quá 100MB.";
                }
            }
        }

        // Custom validation for files to show specific file names
        // Note: We validate files manually above, so we'll skip mimes validation here to avoid duplicate errors
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:255',
            'description' => 'nullable|string|max:1000',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:20480', // Size validation only, mimes checked manually above
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|max:102400', // Size validation only, mimes checked manually above
            'external_link' => 'nullable|url|max:500',
        ], [
            // Title validation messages
            'title.required' => 'Vui lòng nhập tiêu đề tài nguyên.',
            'title.string' => 'Tiêu đề phải là chuỗi ký tự.',
            'title.min' => 'Tiêu đề phải có ít nhất :min ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá :max ký tự.',
            
            // Description validation messages
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'description.max' => 'Mô tả không được vượt quá :max ký tự.',
            
            // Files validation messages
            'files.array' => 'File phải được gửi dưới dạng mảng.',
            'files.max' => 'Bạn chỉ có thể tải lên tối đa :max file.',
            'files.*.file' => 'Một hoặc nhiều file không hợp lệ.',
            'files.*.mimes' => 'File không đúng định dạng. Chỉ chấp nhận: DOC, DOCX, XLS, XLSX, PDF.',
            'files.*.max' => 'Một hoặc nhiều file vượt quá 20MB. Vui lòng chọn file nhỏ hơn.',
            
            // Images validation messages
            'images.array' => 'Hình ảnh phải được gửi dưới dạng mảng.',
            'images.max' => 'Bạn chỉ có thể tải lên tối đa :max hình ảnh/video.',
            'images.*.file' => 'Một hoặc nhiều hình ảnh/video không hợp lệ.',
            'images.*.mimes' => 'Hình ảnh/video không đúng định dạng. Chỉ chấp nhận: JPG, PNG, GIF, MP4, AVI, MOV.',
            'images.*.max' => 'Một hoặc nhiều hình ảnh/video vượt quá 100MB. Vui lòng chọn file nhỏ hơn.',
            
            // External link validation messages
            'external_link.url' => 'Link ngoài không hợp lệ. Vui lòng nhập URL đúng định dạng (ví dụ: https://example.com).',
            'external_link.max' => 'Link ngoài không được vượt quá :max ký tự.',
        ]);

        // Add custom file errors to validator
        foreach ($fileErrors as $key => $message) {
            $validator->errors()->add($key, $message);
        }

        foreach ($imageErrors as $key => $message) {
            $validator->errors()->add($key, $message);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $title = $request->input('title');
        
        $resource = \App\Models\ClubResource::create([
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . time(),
            'description' => $request->description,
            'resource_type' => 'other',
            'club_id' => $clubId,
            'user_id' => $user->id,
            'status' => 'active',
            'external_link' => $request->external_link,
        ]);

        // Handle file album upload
        if ($request->hasFile('files')) {
            $this->handleFileAlbumUpload($resource, $request->file('files'), $clubId);
        }

        // Handle image album upload
        if ($request->hasFile('images')) {
            $this->handleImageAlbumUpload($resource, $request->file('images'), $clubId);
        }

        return redirect()->route('student.club-management.resources', ['club' => $clubId])
            ->with('success', 'Tài nguyên đã được tạo thành công!');
    }

    /**
     * Handle file album upload
     */
    private function handleFileAlbumUpload($resource, $files, $clubId)
    {
        $uploadPath = 'club-resources/' . $clubId . '/files';
        
        foreach ($files as $index => $file) {
            $filename = time() . '_' . $index . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $fullPath = $uploadPath . '/' . $filename;
            
            $file->storeAs('public/' . $uploadPath, $filename);
            
            $thumbnailPath = null;
            if (str_contains($file->getMimeType(), 'image') || str_contains($file->getMimeType(), 'video')) {
                $thumbnailPath = $this->createThumbnail($file, $uploadPath, $filename);
            }
            
            $resource->files()->create([
                'file_path' => $fullPath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'thumbnail_path' => $thumbnailPath,
                'sort_order' => $index,
                'is_primary' => $index === 0
            ]);
        }
    }

    /**
     * Handle image album upload
     */
    private function handleImageAlbumUpload($resource, $images, $clubId)
    {
        $uploadPath = 'club-resources/' . $clubId . '/images';
        
        foreach ($images as $index => $image) {
            $filename = time() . '_' . $index . '_' . \Illuminate\Support\Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $image->getClientOriginalExtension();
            $fullPath = $uploadPath . '/' . $filename;
            
            $image->storeAs('public/' . $uploadPath, $filename);
            
            $thumbnailPath = null;
            if (str_contains($image->getMimeType(), 'image') || str_contains($image->getMimeType(), 'video')) {
                $thumbnailPath = $this->createThumbnail($image, $uploadPath, $filename);
            }
            
            $resource->images()->create([
                'image_path' => $fullPath,
                'image_name' => $image->getClientOriginalName(),
                'image_type' => $image->getMimeType(),
                'image_size' => $image->getSize(),
                'thumbnail_path' => $thumbnailPath,
                'sort_order' => $index,
                'is_primary' => $index === 0
            ]);
        }
    }

    /**
     * Create thumbnail for image/video
     */
    private function createThumbnail($file, $uploadPath, $filename)
    {
        // Simple implementation - just return the same path for now
        // In production, you might want to use Intervention Image or similar
        return $uploadPath . '/thumb_' . $filename;
    }

    /**
     * Show resource detail for student
     */
    public function showClubResource($clubId, $resourceId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);
        $resource = \App\Models\ClubResource::with(['club', 'user', 'images', 'files'])->findOrFail($resourceId);

        // Check if resource belongs to this club
        if ($resource->club_id != $clubId) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Tài nguyên không thuộc CLB này.');
        }

        // Check permission
        $position = $user->getPositionInClub($clubId);
        $hasPermission = in_array($position, ['leader', 'vice_president', 'officer']) 
            || $user->hasPermission('quan_ly_clb', $clubId) 
            || $user->hasPermission('dang_thong_bao', $clubId);
        
        if (!$hasPermission) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền xem tài nguyên CLB này.');
        }

        // Increment view count
        try {
            $resource->increment('view_count');
            $resource->refresh();
        } catch (\Throwable $e) {
            // ignore
        }

        return view('student.club-management.resources-show', compact('user', 'club', 'clubId', 'resource'));
    }

    /**
     * Show edit resource form for student
     */
    public function editClubResource($clubId, $resourceId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);
        $resource = \App\Models\ClubResource::with(['images', 'files'])->findOrFail($resourceId);

        // Check if resource belongs to this club
        if ($resource->club_id != $clubId) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Tài nguyên không thuộc CLB này.');
        }

        // Check permission - only creator or leader/vice/officer can edit
        $position = $user->getPositionInClub($clubId);
        $hasPermission = ($resource->user_id == $user->id) 
            || in_array($position, ['leader', 'vice_president', 'officer'])
            || $user->hasPermission('quan_ly_clb', $clubId);
        
        if (!$hasPermission) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Bạn không có quyền chỉnh sửa tài nguyên này.');
        }

        return view('student.club-management.resources-edit', compact('user', 'club', 'clubId', 'resource'));
    }

    /**
     * Update resource for student
     */
    public function updateClubResource(Request $request, $clubId, $resourceId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);
        $resource = \App\Models\ClubResource::findOrFail($resourceId);

        // Check if resource belongs to this club
        if ($resource->club_id != $clubId) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Tài nguyên không thuộc CLB này.');
        }

        // Check permission
        $position = $user->getPositionInClub($clubId);
        $hasPermission = ($resource->user_id == $user->id) 
            || in_array($position, ['leader', 'vice_president', 'officer'])
            || $user->hasPermission('quan_ly_clb', $clubId);
        
        if (!$hasPermission) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Bạn không có quyền chỉnh sửa tài nguyên này.');
        }

        // Validate files manually first
        $fileErrors = [];
        if ($request->hasFile('files')) {
            $allowedMimes = [
                'application/msword', 'application/x-msword', 'application/vnd.ms-word',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.wordprocessingml',
                'application/vnd.ms-excel', 'application/x-msexcel', 'application/x-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.spreadsheetml',
                'application/pdf', 'application/x-pdf',
            ];
            $allowedExtensions = ['doc', 'docx', 'xls', 'xlsx', 'pdf'];
            
            foreach ($request->file('files') as $index => $file) {
                $mimeType = $file->getMimeType();
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = $file->getClientOriginalName();
                
                $isValidMime = in_array($mimeType, $allowedMimes);
                $isValidExtension = in_array($extension, $allowedExtensions);
                
                if (!$isValidMime && !$isValidExtension) {
                    $fileErrors["files.$index"] = "File \"{$fileName}\" không đúng định dạng. Chỉ chấp nhận: DOC, DOCX, XLS, XLSX, PDF.";
                }
                
                if ($file->getSize() > 20480 * 1024) {
                    $fileErrors["files.$index"] = "File \"{$fileName}\" vượt quá 20MB.";
                }
            }
        }

        $imageErrors = [];
        if ($request->hasFile('images')) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 
                             'video/mp4', 'video/x-msvideo', 'video/quicktime'];
            foreach ($request->file('images') as $index => $image) {
                if (!in_array($image->getMimeType(), $allowedMimes)) {
                    $imageErrors["images.$index"] = "File \"{$image->getClientOriginalName()}\" không đúng định dạng. Chỉ chấp nhận: JPG, PNG, GIF, MP4, AVI, MOV.";
                }
                if ($image->getSize() > 102400 * 1024) {
                    $imageErrors["images.$index"] = "File \"{$image->getClientOriginalName()}\" vượt quá 100MB.";
                }
            }
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:255',
            'description' => 'nullable|string|max:1000',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:20480',
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|max:102400',
            'external_link' => 'nullable|url|max:500',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề tài nguyên.',
            'title.min' => 'Tiêu đề phải có ít nhất :min ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá :max ký tự.',
            'description.max' => 'Mô tả không được vượt quá :max ký tự.',
            'files.max' => 'Bạn chỉ có thể tải lên tối đa :max file.',
            'images.max' => 'Bạn chỉ có thể tải lên tối đa :max hình ảnh/video.',
            'external_link.url' => 'Link ngoài không hợp lệ.',
            'external_link.max' => 'Link ngoài không được vượt quá :max ký tự.',
        ]);

        foreach ($fileErrors as $key => $message) {
            $validator->errors()->add($key, $message);
        }

        foreach ($imageErrors as $key => $message) {
            $validator->errors()->add($key, $message);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $title = $request->input('title');
        
        $resource->update([
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . $resource->id,
            'description' => $request->description,
            'external_link' => $request->external_link,
        ]);

        // Handle file album upload
        if ($request->hasFile('files')) {
            $this->handleFileAlbumUpload($resource, $request->file('files'), $clubId);
        }

        // Handle image album upload
        if ($request->hasFile('images')) {
            $this->handleImageAlbumUpload($resource, $request->file('images'), $clubId);
        }

        // Handle deleted files
        if ($request->has('deleted_files') && $request->deleted_files) {
            $deletedFiles = is_array($request->deleted_files) 
                ? $request->deleted_files 
                : explode(',', $request->deleted_files);
            
            foreach ($deletedFiles as $fileId) {
                $fileId = (int) trim($fileId);
                if ($fileId > 0) {
                    $file = \App\Models\ClubResourceFile::find($fileId);
                    if ($file && $file->club_resource_id == $resource->id) {
                        if ($file->file_path && \Storage::exists('public/' . $file->file_path)) {
                            \Storage::delete('public/' . $file->file_path);
                        }
                        if ($file->thumbnail_path && \Storage::exists('public/' . $file->thumbnail_path)) {
                            \Storage::delete('public/' . $file->thumbnail_path);
                        }
                        $file->delete();
                    }
                }
            }
        }

        // Handle deleted images
        if ($request->has('deleted_images') && $request->deleted_images) {
            $deletedImages = is_array($request->deleted_images) 
                ? $request->deleted_images 
                : explode(',', $request->deleted_images);
            
            foreach ($deletedImages as $imageId) {
                $imageId = (int) trim($imageId);
                if ($imageId > 0) {
                    $image = \App\Models\ClubResourceImage::find($imageId);
                    if ($image && $image->club_resource_id == $resource->id) {
                        if ($image->image_path && \Storage::exists('public/' . $image->image_path)) {
                            \Storage::delete('public/' . $image->image_path);
                        }
                        if ($image->thumbnail_path && \Storage::exists('public/' . $image->thumbnail_path)) {
                            \Storage::delete('public/' . $image->thumbnail_path);
                        }
                        $image->delete();
                    }
                }
            }
        }

        return redirect()->route('student.club-management.resources.show', ['club' => $clubId, 'resource' => $resourceId])
            ->with('success', 'Tài nguyên đã được cập nhật thành công!');
    }

    /**
     * Delete resource for student
     */
    public function destroyClubResource($clubId, $resourceId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);
        $resource = \App\Models\ClubResource::where('club_id', $clubId)->findOrFail($resourceId);

        // Check permission
        $position = $user->getPositionInClub($clubId);
        $hasPermission = ($resource->user_id == $user->id) 
            || in_array($position, ['leader', 'vice_president', 'officer'])
            || $user->hasPermission('quan_ly_clb', $clubId)
            || $user->hasPermission('dang_thong_bao', $clubId);
        
        if (!$hasPermission) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Bạn không có quyền xóa tài nguyên này.');
        }

        // Delete associated files and images
        foreach ($resource->files as $file) {
            if ($file->file_path) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $file->file_path);
            }
            if ($file->thumbnail_path) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $file->thumbnail_path);
            }
        }

        foreach ($resource->images as $image) {
            if ($image->image_path) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $image->image_path);
            }
            if ($image->thumbnail_path) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $image->thumbnail_path);
            }
        }

        $resource->delete();

        return redirect()->route('student.club-management.resources', ['club' => $clubId])
            ->with('success', 'Tài nguyên đã được xóa thành công!');
    }

    /**
     * Update member permissions in a club
     */
    public function updateMemberPermissions(Request $request, $clubId, $memberId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Bạn không có quyền cập nhật phân quyền.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->firstOrFail();

        if (!$clubMember->user || $clubMember->user_id === $user->id) {
            return redirect()->back()->with('error', 'Không hợp lệ.');
        }

        $permissionNames = $request->input('permissions', []);
        $permissionIds = Permission::whereIn('name', (array) $permissionNames)->pluck('id');

        DB::transaction(function () use ($clubMember, $clubId, $permissionIds) {
            DB::table('user_permissions_club')
                ->where('user_id', $clubMember->user_id)
                ->where('club_id', $clubId)
                ->delete();
            foreach ($permissionIds as $pid) {
                DB::table('user_permissions_club')->insert([
                    'user_id' => $clubMember->user_id,
                    'club_id'  => $clubId,
                    'permission_id' => $pid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Đã cập nhật phân quyền.');
    }

    /**
     * Remove a member from the club
     */
    public function removeMember(Request $request, $clubId, $memberId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa thành viên.');
        }

        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->firstOrFail();

        if (!$clubMember->user || $clubMember->user_id === $user->id) {
            return redirect()->back()->with('error', 'Không hợp lệ.');
        }
        if (in_array($clubMember->position, ['leader','owner'])) {
            return redirect()->back()->with('error', 'Không thể xóa Trưởng CLB/Chủ nhiệm.');
        }

        $reason = trim((string) $request->input('reason'));
        DB::transaction(function () use ($clubMember, $clubId, $reason) {
            DB::table('user_permissions_club')
                ->where('user_id', $clubMember->user_id)
                ->where('club_id', $clubId)
                ->delete();
            $clubMember->update([
                'status' => 'inactive',
                'left_at' => now(),
                'left_reason' => $reason ?: null,
            ]);
            $clubMember->delete();
        });

        return redirect()->back()->with('success', 'Đã xóa thành viên.');
    }

    /**
     * List club join requests
     */
    public function clubJoinRequests($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president', 'officer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền xem đơn tham gia CLB này.');
        }

        $requests = JoinReq::with('user')
            ->where('club_id', $clubId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('student.club-management.join-requests', [
            'user' => $user,
            'club' => $club,
            'clubId' => $clubId,
            'requests' => $requests,
        ]);
    }

    public function approveClubJoinRequest($clubId, $requestId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president', 'officer'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền duyệt đơn.');
        }
        $req = JoinReq::where('club_id', $clubId)->findOrFail($requestId);
        if ($req->status === 'pending') {
            $req->status = 'approved';
            $req->approved_by = $user->id;
            $req->approved_at = now();
            $req->save();
            // thêm vào bảng club_members nếu cần, tùy logic hiện có
        }
        return redirect()->back()->with('success', 'Đã duyệt đơn tham gia.');
    }

    public function rejectClubJoinRequest($clubId, $requestId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president', 'officer'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền từ chối đơn.');
        }
        $req = JoinReq::where('club_id', $clubId)->findOrFail($requestId);
        if ($req->status === 'pending') {
            $req->status = 'rejected';
            $req->approved_by = $user->id;
            $req->approved_at = now();
            $req->save();
        }
        return redirect()->back()->with('success', 'Đã từ chối đơn tham gia.');
    }

    /**
     * Club reports dashboard (fund + activities)
     */
    public function clubReports(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Determine user's club (first club)
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        $club = $user->clubs->first();
        $clubId = $club->id;

        // Simple stats
        $totalMembers = ClubMember::where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->count();
        $newMembersThisMonth = ClubMember::where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $totalEvents = Event::where('club_id', $clubId)->count();
        $upcomingEvents = Event::where('club_id', $clubId)
            ->where('start_time', '>=', now())
            ->count();

        // Fund stats
        $fund = Fund::where('club_id', $clubId)->first();
        $fundId = $fund?->id ?? null;
        $totalIncome = 0;
        $totalExpense = 0;
        $balance = 0;
        $expenseByCategory = [];

        if ($fundId) {
            $totalIncome = FundTransaction::where('fund_id', $fundId)
                ->where('type', 'income')
                ->where('status', 'approved')
                ->sum('amount');
            $totalExpense = FundTransaction::where('fund_id', $fundId)
                ->where('type', 'expense')
                ->where('status', 'approved')
                ->sum('amount');
            $balance = max(0, (int) $totalIncome - (int) $totalExpense);
            // breakdown by category (if field exists)
            $expenseByCategory = FundTransaction::where('fund_id', $fundId)
                ->where('type', 'expense')
                ->where('status', 'approved')
                ->select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')
                ->pluck('total', 'category')
                ->toArray();
        }

        // Member structure (leader/officer/member) simple distribution
        $structureMap = [
            'leader' => 'Trưởng',
            'vice_president' => 'Phó',
            'officer' => 'Cán sự',
            'member' => 'Thành viên',
        ];
        $memberStructureCounts = ClubMember::where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->select('position', DB::raw('COUNT(*) as cnt'))
            ->groupBy('position')
            ->pluck('cnt', 'position')
            ->toArray();
        $memberStructure = [
            'labels' => array_values($structureMap),
            'data' => [
                $memberStructureCounts['leader'] ?? 0,
                $memberStructureCounts['vice_president'] ?? 0,
                $memberStructureCounts['officer'] ?? 0,
                $memberStructureCounts['member'] ?? 0,
            ],
        ];

        // Events by month (last 12 months)
        $labels12 = [];
        $events12 = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $labels12[] = $d->format('m/Y');
            $events12[] = Event::where('club_id', $clubId)
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->count();
        }

        // Member growth (last 3 months cumulative)
        $labels3 = [];
        $data3 = [];
        for ($i = 2; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $labels3[] = $d->format('m/Y');
            $data3[] = ClubMember::where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->where('created_at', '<=', $d->copy()->endOfMonth())
                ->count();
        }

        // Activity trend (6 months): new members & new events in month
        $labels6 = [];
        $newMembers6 = [];
        $newEvents6 = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $labels6[] = $d->format('m/Y');
            $newMembers6[] = ClubMember::where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->count();
            $newEvents6[] = Event::where('club_id', $clubId)
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->count();
        }

        $stats = [
            'totalMembers' => $totalMembers,
            'newMembersThisMonth' => $newMembersThisMonth,
            'totalEvents' => $totalEvents,
            'upcomingEvents' => $upcomingEvents,
            'fund' => [
                'totalIncome' => (int) $totalIncome,
                'totalExpense' => (int) $totalExpense,
                'balance' => (int) $balance,
                'expenseByCategory' => $expenseByCategory,
            ],
            'memberStructure' => $memberStructure,
            'eventsByMonth' => ['labels' => $labels12, 'data' => $events12],
            'memberGrowth' => ['labels' => $labels3, 'data' => $data3],
            'activityTrend' => [
                'labels' => $labels6,
                'newMembers' => $newMembers6,
                'newEvents' => $newEvents6,
            ],
        ];

        return view('student.club-management.reports', compact('user', 'club', 'stats'));
    }
    
    /**
     * Fund transactions list for student (read-only)
     */
    public function fundTransactions(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        $club = $user->clubs->first();

        // Permission: reuse view report permission
        if (!$user->hasPermission('xem_bao_cao', $club->id)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền xem giao dịch quỹ.');
        }

        $fund = Fund::where('club_id', $club->id)->first();
        if (!$fund) {
            return view('student.club-management.fund-transactions', [
                'user' => $user,
                'club' => $club,
                'transactions' => collect(),
                'summary' => ['income' => 0, 'expense' => 0, 'balance' => 0],
                'filterType' => $request->input('type'),
            ]);
        }

        $query = FundTransaction::where('fund_id', $fund->id)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc');

        $filterType = $request->input('type'); // income | expense | null
        if (in_array($filterType, ['income', 'expense'])) {
            $query->where('type', $filterType);
        }

        $transactions = $query->paginate(20)->withQueryString();

        $income = FundTransaction::where('fund_id', $fund->id)
            ->where('status', 'approved')->where('type', 'income')->sum('amount');
        $expense = FundTransaction::where('fund_id', $fund->id)
            ->where('status', 'approved')->where('type', 'expense')->sum('amount');
        $summary = [
            'income' => (int) $income,
            'expense' => (int) $expense,
            'balance' => (int) $income - (int) $expense,
        ];

        return view('student.club-management.fund-transactions', [
            'user' => $user,
            'club' => $club,
            'transactions' => $transactions,
            'summary' => $summary,
            'filterType' => $filterType,
        ]);
    }

    /**
     * Show create transaction form
     */
    public function fundTransactionCreate(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        $club = $user->clubs->first();
        // Officer/Leader can create, member view only
        $position = $user->getPositionInClub($club->id);
        if (!in_array($position, ['leader', 'vice_president', 'officer'])) {
            return redirect()->route('student.club-management.fund-transactions')
                ->with('error', 'Bạn không có quyền tạo giao dịch.');
        }
        return view('student.club-management.fund-transaction-create', [
            'user' => $user,
            'club' => $club,
        ]);
    }

    /**
     * Store transaction (leader auto-approved, officer pending)
     */
    public function fundTransactionStore(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        $club = $user->clubs->first();
        $fund = Fund::firstOrCreate(['club_id' => $club->id], [
            'name' => 'Quỹ ' . $club->name,
            'current_amount' => 0,
        ]);

        $position = $user->getPositionInClub($club->id);
        if (!in_array($position, ['leader', 'vice_president', 'officer'])) {
            return redirect()->route('student.club-management.fund-transactions')
                ->with('error', 'Bạn không có quyền tạo giao dịch.');
        }

        $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:1',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:5120',
        ]);

        // Leader auto-approved, Officer pending
        $status = $position === 'leader' ? 'approved' : 'pending';

        $tx = new FundTransaction();
        $tx->fund_id = $fund->id;
        $tx->type = $request->type;
        $tx->amount = (int) $request->amount;
        $tx->category = $request->category ?: null;
        $tx->description = $request->description ?: null;
        $tx->status = $status;
        $tx->created_by = $user->id;
        // Thiết lập tiêu đề giao dịch để tránh lỗi DB yêu cầu 'title' NOT NULL
        $autoTitle = trim(($request->category ?: ucfirst($request->type)) . ' ' . number_format((int) $request->amount, 0, ',', '.') . ' VNĐ');
        $tx->title = $request->input('title', $autoTitle);
        // Ngày giao dịch: nếu bảng có cột transaction_date thì luôn set
        if (Schema::hasColumn('fund_transactions', 'transaction_date')) {
            $tx->transaction_date = $request->filled('transaction_date')
                ? \Carbon\Carbon::parse($request->transaction_date)
                : now();
        } else {
            // fallback: nếu không có cột transaction_date, có thể set created_at cho khớp hiển thị
            if ($request->filled('transaction_date')) {
                $tx->created_at = \Carbon\Carbon::parse($request->transaction_date);
            }
        }
        // Attachment (optional simple store public/uploads/fund/)
        if ($request->hasFile('attachment')) {
            $dir = public_path('uploads/fund');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $name = time() . '_' . $user->id . '.' . $request->file('attachment')->getClientOriginalExtension();
            $request->file('attachment')->move($dir, $name);
            $storedPath = 'uploads/fund/' . $name;
            // Chỉ gán nếu cột tồn tại trong DB
            if (Schema::hasColumn('fund_transactions', 'attachment_path')) {
                $tx->attachment_path = $storedPath;
            } elseif (Schema::hasColumn('fund_transactions', 'attachment')) {
                // Một số schema có thể dùng 'attachment'
                $tx->attachment = $storedPath;
            } else {
                // Nếu không có cột file, ghép vào description để không mất thông tin
                $tx->description = trim(($tx->description ? $tx->description . ' ' : '') . '(chứng từ: ' . $storedPath . ')');
            }
        }
        $tx->save();

        // If approved and type expense, could validate balance; for simplicity we rely on admin reconciliation
        return redirect()->route('student.club-management.fund-transactions')
            ->with('success', $status === 'approved' ? 'Đã tạo giao dịch và duyệt.' : 'Đã tạo giao dịch, chờ duyệt.');
    }

    /**
     * Approve transaction (leader only)
     */
    public function approveFundTransaction($transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        $club = $user->clubs->first();
        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới được duyệt.');
        }
        $tx = FundTransaction::findOrFail($transactionId);
        $fund = Fund::where('club_id', $club->id)->first();
        if ($tx->fund_id !== ($fund->id ?? null)) {
            return redirect()->back()->with('error', 'Giao dịch không thuộc quỹ CLB của bạn.');
        }
        if ($tx->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch không ở trạng thái chờ duyệt.');
        }
        $tx->status = 'approved';
        $tx->save();
        return redirect()->back()->with('success', 'Đã duyệt giao dịch.');
    }

    /**
     * Reject transaction (leader only)
     */
    public function rejectFundTransaction($transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        $club = $user->clubs->first();
        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới được từ chối.');
        }
        $tx = FundTransaction::findOrFail($transactionId);
        $fund = Fund::where('club_id', $club->id)->first();
        if ($tx->fund_id !== ($fund->id ?? null)) {
            return redirect()->back()->with('error', 'Giao dịch không thuộc quỹ CLB của bạn.');
        }
        if ($tx->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch không ở trạng thái chờ duyệt.');
        }
        $tx->status = 'rejected';
        $tx->save();
        return redirect()->back()->with('success', 'Đã từ chối giao dịch.');
    }

    /**
     * Show transaction detail
     */
    public function fundTransactionShow($transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        $club = $user->clubs->first();
        $tx = FundTransaction::findOrFail($transactionId);
        $fund = Fund::where('club_id', $club->id)->first();
        if ($tx->fund_id !== ($fund->id ?? null)) {
            return redirect()->route('student.club-management.fund-transactions')
                ->with('error', 'Giao dịch không thuộc quỹ CLB của bạn.');
        }
        return view('student.club-management.fund-transaction-show', [
            'user' => $user,
            'club' => $club,
            'tx' => $tx,
        ]);
    }

    /**
     * Display posts for students with member-only access control
     */
    public function posts(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Lấy danh sách CLB mà user là thành viên
        $userClubIds = $user->clubs->pluck('id')->toArray();
        
        // Base query cho bài viết có quyền xem
        $baseQuery = Post::with(['club', 'user', 'attachments'])
            ->where(function($q) use ($userClubIds) {
                // Bài viết công khai
                $q->where('status', 'published')
                  // Hoặc bài viết chỉ thành viên CLB mà user là thành viên
                  ->orWhere(function($subQ) use ($userClubIds) {
                      $subQ->where('status', 'members_only')
                           ->whereIn('club_id', $userClubIds);
                  });
            })
            // Chỉ hiển thị bài viết hợp lệ cho trang tin tức
            ->whereIn('type', ['post', 'announcement'])
            // Loại bỏ bài viết đã xóa mềm (ở thùng rác)
            ->whereNull('deleted_at')
            // Loại bỏ bài có status legacy 'deleted' nếu còn
            ->where('status', '!=', 'deleted');

        // Tách query cho bài viết và thông báo
        $postsQuery = (clone $baseQuery);
        $announcementsQuery = (clone $baseQuery);

        // Bài viết: loại trừ announcement (trừ khi filter theo type = announcement)
        if ($request->has('type') && $request->type === 'announcement') {
            $postsQuery->where('type', 'announcement');
        } else {
            $postsQuery->where('type', '!=', 'announcement');
        }

        // Thông báo: chỉ lấy announcement
        $announcementsQuery->where('type', 'announcement');

        // Filter by club cho cả 2
        if ($request->has('club_id') && $request->club_id) {
            $postsQuery->where('club_id', $request->club_id);
            $announcementsQuery->where('club_id', $request->club_id);
        }

        // Search cho cả 2
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $postsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
            $announcementsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Filter by type (all, latest, popular) cho bài viết
        $filter = $request->input('filter', 'all');
        if ($filter === 'latest') {
            $postsQuery->orderBy('created_at', 'desc');
        } elseif ($filter === 'popular') {
            $postsQuery->orderBy('views', 'desc')->orderBy('created_at', 'desc');
        } else {
            $postsQuery->orderBy('created_at', 'desc');
        }

        // Thông báo luôn sắp xếp theo mới nhất
        $announcementsQuery->orderBy('created_at', 'desc');

        $posts = $postsQuery->paginate(3);
        $announcements = $announcementsQuery->limit(5)->get();
        $clubs = Club::where('status', 'active')->get();

        // Lấy thông báo mới nhất để hiển thị modal
        $latestAnnouncement = $announcementsQuery->first();

        // Kiểm tra xem có thông báo mới hơn thông báo đã xem gần nhất không
        // Modal sẽ hiển thị mỗi lần vào trang cho đến khi có thông báo mới (ID lớn hơn)
        $lastViewedAnnouncementId = session('last_viewed_announcement_id', 0);
        $shouldShowModal = false;
        if ($latestAnnouncement) {
            // Hiển thị modal nếu:
            // 1. Chưa có thông báo nào được xem (lastViewedAnnouncementId = 0)
            // 2. Hoặc thông báo hiện tại có ID >= thông báo đã xem (hiển thị lại mỗi lần)
            // Modal sẽ tiếp tục hiển thị cho đến khi có thông báo mới (ID lớn hơn)
            if ($latestAnnouncement->id >= $lastViewedAnnouncementId) {
                $shouldShowModal = true;
            }
        }

        return view('student.posts.index', compact('posts', 'clubs', 'user', 'latestAnnouncement', 'shouldShowModal', 'announcements'));
    }

    /**
     * Display single post with member-only access control
     */
    public function showPost($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $post = Post::with(['club', 'user', 'comments.user', 'attachments'])->findOrFail($id);
        
        // Kiểm tra quyền xem bài viết
        $canView = false;
        
        if ($post->status === 'published') {
            $canView = true;
        } elseif ($post->status === 'members_only') {
            // Kiểm tra xem user có phải thành viên của CLB không
            $userClubIds = $user->clubs->pluck('id')->toArray();
            $canView = in_array($post->club_id, $userClubIds);
        }

        if (!$canView) {
            return redirect()->route('student.posts')->with('error', 'Bạn không có quyền xem bài viết này.');
        }

        // Bài viết liên quan: cùng CLB, đã xuất bản (hoặc members_only nếu là thành viên CLB), loại post/announcement
        $relatedQuery = Post::with(['club', 'user'])
            ->where('id', '!=', $post->id)
            ->where('club_id', $post->club_id)
            ->whereIn('type', ['post', 'announcement'])
            ->orderBy('created_at', 'desc')
            ->limit(6);

        // Quyền xem cho bài viết liên quan
        $relatedQuery->where(function($q) use ($user) {
            $userClubIds = $user->clubs->pluck('id')->toArray();
            $q->where('status', 'published')
              ->orWhere(function($sub) use ($userClubIds) {
                  $sub->where('status', 'members_only')
                      ->whereIn('club_id', $userClubIds);
              });
        });

        $relatedPosts = $relatedQuery->get();

        // Tăng lượt xem (đơn giản, không đếm trùng phiên)
        try {
            $post->increment('views');
            $post->refresh();
        } catch (\Throwable $e) {
            // ignore
        }

        return view('student.posts.show', compact('post', 'user', 'relatedPosts'));
    }

    /**
     * Add a new comment to a post (students)
     */
    public function addPostComment(Request $request, $id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $post = Post::findOrFail($id);

        // Kiểm tra quyền bình luận (phải xem được bài viết)
        $canComment = false;
        if ($post->status === 'published') {
            $canComment = true;
        } elseif ($post->status === 'members_only') {
            $userClubIds = $user->clubs->pluck('id')->toArray();
            $canComment = in_array($post->club_id, $userClubIds);
        }

        if (!$canComment) {
            return redirect()->route('student.posts')->with('error', 'Bạn không có quyền bình luận bài viết này.');
        }

        $request->validate([
            'content' => 'required|string|max:2000'
        ]);

        \App\Models\PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $request->content
        ]);

        return redirect()->route('student.posts.show', $post->id)->with('success', 'Đã gửi bình luận!');
    }
    
    /**
     * Show create post form for student
     */
    public function createPost()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $clubs = Club::whereIn('id', $user->clubs->pluck('id'))->where('status', 'active')->get();
        if ($clubs->isEmpty()) {
            return redirect()->route('student.posts')->with('error', 'Bạn cần tham gia CLB trước khi tạo bài viết.');
        }
        return view('student.posts.create', compact('user', 'clubs'));
    }

    /**
     * Store student post
     */
    public function storePost(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:published,members_only,hidden',
            'type' => 'nullable|in:post,announcement,document',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096'
        ]);
        $data = $request->only(['title','content','club_id','status']);
        $data['type'] = $request->input('type', 'post');
        $data['user_id'] = $user->id;
        // Generate unique slug
        $baseSlug = Str::slug($data['title']);
        $slug = $baseSlug;
        $suffix = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
        $data['slug'] = $slug;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $destination = public_path('uploads/posts');
            if (!is_dir($destination)) {
                @mkdir($destination, 0755, true);
            }
            $image->move($destination, $filename);
            $data['image'] = 'uploads/posts/' . $filename;
        }
        // Remove featured image from content if present
        if (!empty($data['image']) && !empty($data['content'])) {
            $relative = ltrim($data['image'], '/');
            $assetUrl = asset($relative);
            $pattern = '#<img[^>]+src=["\\\'](?:' . preg_quote($assetUrl, '#') . '|' . preg_quote('/' . $relative, '#') . '|' . preg_quote($relative, '#') . ')[^"\\\']*["\\\'][^>]*>#i';
            $data['content'] = preg_replace($pattern, '', $data['content']);
        }
        $post = \App\Models\Post::create($data);
        return redirect()->route('student.posts.show', $post->id)->with('success', 'Tạo bài viết thành công!');
    }

    /**
     * Show edit post form
     */
    public function editPost($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $post = Post::findOrFail($id);
        if ($post->user_id !== $user->id) {
            return redirect()->route('student.posts.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa bài viết này.');
        }
        $clubs = Club::whereIn('id', $user->clubs->pluck('id'))->where('status', 'active')->get();
        return view('student.posts.edit', compact('user','post','clubs'));
    }

    /**
     * Update student post
     */
    public function updatePost(Request $request, $id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $post = Post::findOrFail($id);
        if ($post->user_id !== $user->id) {
            return redirect()->route('student.posts.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa bài viết này.');
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:published,members_only,hidden',
            'type' => 'nullable|in:post,announcement,document',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_image' => 'nullable|in:0,1'
        ]);
        $data = $request->only(['title','content','club_id','status']);
        $data['type'] = $request->input('type', $post->type ?? 'post');
        // Regenerate slug if title changed or slug missing
        if ($post->title !== $data['title'] || empty($post->slug)) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $suffix = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }
            $data['slug'] = $slug;
        }
        if ($request->input('remove_image') === '1' && !empty($post->image)) {
            if (\Illuminate\Support\Str::startsWith($post->image, ['uploads/','/uploads/'])) {
                @unlink(public_path(ltrim($post->image,'/')));
            }
            $post->image = null;
        }
        if ($request->hasFile('image')) {
            if (!empty($post->image) && \Illuminate\Support\Str::startsWith($post->image, ['uploads/','/uploads/'])) {
                @unlink(public_path(ltrim($post->image,'/')));
            }
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $destination = public_path('uploads/posts');
            if (!is_dir($destination)) {
                @mkdir($destination, 0755, true);
            }
            $image->move($destination, $filename);
            $data['image'] = 'uploads/posts/' . $filename;
        }
        // Remove featured image(s) from content if present (both old and new featured images)
        if (!empty($data['content'])) {
            $imagesToStrip = [];
            if (!empty($post->image)) {
                $imagesToStrip[] = $post->image;
            }
            if (!empty($data['image'])) {
                $imagesToStrip[] = $data['image'];
            }
            foreach ($imagesToStrip as $imgPath) {
                $relative = ltrim($imgPath, '/');
                $assetUrl = asset($relative);
                $pattern = '#<img[^>]+src=["\\\'](?:' . preg_quote($assetUrl, '#') . '|' . preg_quote('/' . $relative, '#') . '|' . preg_quote($relative, '#') . ')[^"\\\']*["\\\'][^>]*>#i';
                $data['content'] = preg_replace($pattern, '', $data['content']);
            }
        }
        $post->update($data);
        return redirect()->route('student.posts.show', $post->id)->with('success', 'Cập nhật bài viết thành công!');
    }

    /**
     * List posts created by current student
     */
    public function myPosts(\Illuminate\Http\Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $query = Post::with(['club'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        $posts = $query->paginate(12);
        return view('student.posts.manage', compact('user','posts'));
    }

    /**
     * Delete a post created by current student
     */
    public function deletePost($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $post = Post::findOrFail($id);
        if ($post->user_id !== $user->id) {
            return redirect()->route('student.posts.manage')->with('error', 'Bạn không có quyền xóa bài viết này.');
        }
        // remove featured image file if under uploads
        if (!empty($post->image) && \Illuminate\Support\Str::startsWith($post->image, ['uploads/','/uploads/'])) {
            @unlink(public_path(ltrim($post->image,'/')));
        }
        $post->delete();
        return redirect()->route('student.posts.manage')->with('success', 'Đã xóa bài viết.');
    }

    /**
     * Mark announcement as viewed
     */
    public function markAnnouncementViewed(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['success' => false], 401);
        }

        $announcementId = $request->input('announcement_id');
        if ($announcementId) {
            // Chỉ cập nhật ID thông báo đã xem nếu thông báo này mới hơn
            // Điều này cho phép modal hiển thị lại mỗi lần vào trang cho đến khi có thông báo mới
            $lastViewedId = session('last_viewed_announcement_id', 0);
            if ($announcementId > $lastViewedId) {
                session(['last_viewed_announcement_id' => $announcementId]);
            }
            // Nếu đóng modal của thông báo cũ, không cập nhật - để modal tiếp tục hiển thị
        }

        return response()->json(['success' => true]);
    }

}
