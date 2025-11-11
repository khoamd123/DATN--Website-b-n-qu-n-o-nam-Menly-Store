<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubJoinRequest;
use App\Models\Permission;
use App\Models\Event;

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

        return redirect()->route('home');
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
        $clubMembers = collect();
        $allPermissions = collect();
        $clubStats = [
            'members' => ['active' => 0, 'pending' => 0],
            'events' => ['total' => 0, 'upcoming' => 0],
            'announcements' => ['total' => 0, 'today' => 0],
        ];

        if ($user->clubs->count() > 0) {
            $userClub = $user->clubs->first();
            $clubId = $userClub->id;
            $userPosition = $user->getPositionInClub($clubId);
            $hasManagementRole = in_array($userPosition, ['leader', 'vice_president', 'officer']);

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

            $clubStats = [
                'members' => [
                    'active' => $clubMembers->count(),
                    'pending' => ClubJoinRequest::where('club_id', $clubId)->pending()->count(),
                ],
                'events' => [
                    'total' => Event::where('club_id', $clubId)->count(),
                    'upcoming' => Event::where('club_id', $clubId)
                        ->where('start_time', '>=', now())
                        ->count(),
                ],
                'announcements' => [
                    'total' => Post::where('club_id', $clubId)
                        ->where('type', 'announcement')
                        ->count(),
                    'today' => Post::where('club_id', $clubId)
                        ->where('type', 'announcement')
                        ->whereDate('created_at', now()->toDateString())
                        ->count(),
                ],
            ];
        }

        return view('student.club-management.index', compact(
            'user',
            'hasManagementRole',
            'userPosition',
            'userClub',
            'clubMembers',
            'allPermissions',
            'clubStats',
            'clubId'
        ));
    }

    /**
     * Manage members page (separate view)
     */
    public function manageMembers($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        // Require permission to manage members in this club
        if (!$user->hasPermission('quan_ly_thanh_vien', $clubId) && $user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền truy cập trang quản lý thành viên của CLB này.');
        }

        $userPosition = $user->getPositionInClub($clubId);

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
            'clubId' => $clubId,
            'userPosition' => $userPosition,
            'clubMembers' => $clubMembers,
            'allPermissions' => $allPermissions,
        ]);
    }

    /**
     * Update club permissions for a specific member
     */
    public function updateMemberPermissions(Request $request, $clubId, $memberId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;

        if (!$clubId) {
            return redirect()->route('student.club-management.index')->with('error', 'Không xác định được CLB.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if (!$user->hasPermission('quan_ly_clb', $clubId) && $user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->back()->with('error', 'Bạn không có quyền cập nhật phân quyền.');
        }

        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->firstOrFail();

        if (!$clubMember->user) {
            return redirect()->back()->with('error', 'Không tìm thấy thành viên.');
        }

        if ($clubMember->user_id === $user->id) {
            return redirect()->back()->with('error', 'Bạn không thể thay đổi quyền của chính mình.');
        }

        $permissionNames = $request->input('permissions', []);
        $permissionNames = is_array($permissionNames) ? $permissionNames : [];

        $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id', 'name');

        // Đảm bảo quyền xem báo cáo luôn tồn tại cho thành viên
        if (!$permissionIds->has('xem_bao_cao')) {
            $viewReportPermission = Permission::where('name', 'xem_bao_cao')->first();
            if ($viewReportPermission) {
                $permissionIds->put('xem_bao_cao', $viewReportPermission->id);
            }
        }

        DB::transaction(function () use ($clubMember, $clubId, $permissionIds) {
            DB::table('user_permissions_club')
                ->where('user_id', $clubMember->user_id)
                ->where('club_id', $clubId)
                ->delete();

            foreach ($permissionIds as $permissionId) {
                DB::table('user_permissions_club')->insert([
                    'user_id' => $clubMember->user_id,
                    'club_id' => $clubId,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('student.club-management.index')
            ->with('success', 'Đã cập nhật phân quyền cho thành viên.');
    }

    /**
     * Remove a member from club (soft delete membership and revoke permissions)
     */
    public function removeMember(Request $request, $clubId, $memberId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;

        // Require manage members permission or leader
        if (!$user->hasPermission('quan_ly_thanh_vien', $clubId) && $user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa thành viên.');
        }

        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->firstOrFail();

        // Safety checks
        if (!$clubMember->user) {
            return redirect()->back()->with('error', 'Không tìm thấy thành viên.');
        }
        if ($clubMember->user_id === $user->id) {
            return redirect()->back()->with('error', 'Bạn không thể tự xóa chính mình.');
        }
        if ($clubMember->position === 'leader' || $clubMember->position === 'owner') {
            return redirect()->back()->with('error', 'Không thể xóa Trưởng CLB/Chủ nhiệm.');
        }

        $reason = trim((string) $request->input('reason'));

        \DB::transaction(function () use ($clubMember, $clubId, $reason) {
            // Revoke all club-specific permissions
            \DB::table('user_permissions_club')
                ->where('user_id', $clubMember->user_id)
                ->where('club_id', $clubId)
                ->delete();

            // Mark inactive then soft delete membership
            $clubMember->update([
                'status' => 'inactive',
                'left_at' => now(),
                'left_reason' => $reason ?: null,
            ]);
            $clubMember->delete();
        });

        return redirect()
            ->back()
            ->with('success', 'Đã xóa thành viên khỏi CLB.');
    }

    /**
     * Club join requests - list for club leaders/managers
     */
    public function clubJoinRequests($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail($clubId);

        // Only leader/vice/officer can view
        if (!in_array($user->getPositionInClub($clubId), ['leader', 'vice_president', 'officer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền xem đơn tham gia CLB này.');
        }

        $requests = ClubJoinRequest::with('user')
            ->where('club_id', $clubId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('student.club-management.join-requests', compact('user', 'club', 'requests', 'clubId'));
    }

    /**
     * Approve club join request (club side)
     */
    public function approveClubJoinRequest($clubId, $requestId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!in_array($user->getPositionInClub($clubId), ['leader', 'vice_president', 'officer'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền duyệt đơn.');
        }

        $req = ClubJoinRequest::where('club_id', $clubId)->findOrFail($requestId);
        if (!$req->isApproved()) {
            $req->approve($user->id);
        }
        return redirect()->back()->with('success', 'Đã duyệt đơn tham gia.');
    }

    /**
     * Reject club join request (club side)
     */
    public function rejectClubJoinRequest($clubId, $requestId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!in_array($user->getPositionInClub($clubId), ['leader', 'vice_president', 'officer'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền từ chối đơn.');
        }

        $req = ClubJoinRequest::where('club_id', $clubId)->findOrFail($requestId);
        if (!$req->isRejected()) {
            $req->reject($user->id);
        }
        return redirect()->back()->with('success', 'Đã từ chối đơn tham gia.');
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
        
        // Query bài viết với logic kiểm tra quyền
        $query = Post::with(['club', 'user'])
            ->where(function($q) use ($userClubIds) {
                // Bài viết công khai
                $q->where('status', 'published')
                  // Hoặc bài viết chỉ thành viên CLB mà user là thành viên
                  ->orWhere(function($subQ) use ($userClubIds) {
                      $subQ->where('status', 'members_only')
                           ->whereIn('club_id', $userClubIds);
                  });
            })
            ->where('status', '!=', 'deleted');

        // Filter by club
        if ($request->has('club_id') && $request->club_id) {
            $query->where('club_id', $request->club_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(10);
        $clubs = Club::where('status', 'active')->get();

        return view('student.posts.index', compact('posts', 'clubs', 'user'));
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

        $post = Post::with(['club', 'user', 'comments.user'])->findOrFail($id);
        
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

        return view('student.posts.show', compact('post', 'user'));
    }

    /**
     * Display club settings page
     */
    public function clubSettings($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::with(['field', 'leader', 'owner'])->findOrFail($clubId);

        // Chỉ leader mới có quyền truy cập cài đặt
        if ($user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB mới có quyền truy cập trang cài đặt.');
        }

        // Lấy danh sách fields để chọn
        $fields = \App\Models\Field::all();
        
        // Lấy danh sách thành viên để có thể chọn làm leader mới
        $clubMembers = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->where('position', '!=', 'leader')
            ->get();

        // Thống kê CLB
        $clubStats = [
            'members' => ClubMember::where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->count(),
            'events' => Event::where('club_id', $clubId)->count(),
            'posts' => Post::where('club_id', $clubId)->count(),
        ];

        return view('student.club-management.settings', [
            'user' => $user,
            'club' => $club,
            'clubId' => $clubId,
            'fields' => $fields,
            'clubMembers' => $clubMembers,
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

        // Chỉ leader mới có quyền cập nhật cài đặt
        if ($user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới có quyền cập nhật cài đặt.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_members' => 'required|integer|min:1|max:1000',
            'field_id' => 'nullable|exists:fields,id',
        ]);

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'max_members' => $request->max_members,
        ];

        if ($request->has('field_id') && $request->field_id) {
            $updateData['field_id'] = $request->field_id;
        }

        // Xử lý upload logo nếu có
        if ($request->hasFile('logo')) {
            try {
                $logo = $request->file('logo');
                $extension = $logo->getClientOriginalExtension();
                $logoName = time() . '_' . $clubId . '.' . $extension;
                $logoPath = 'uploads/clubs/logos/' . $logoName;
                
                $logoDir = public_path('uploads/clubs/logos');
                if (!file_exists($logoDir)) {
                    mkdir($logoDir, 0755, true);
                }
                
                $logo->move($logoDir, $logoName);
                
                // Xóa logo cũ nếu có
                if ($club->logo && file_exists(public_path($club->logo))) {
                    @unlink(public_path($club->logo));
                }
                
                $updateData['logo'] = $logoPath;
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Lỗi upload logo: ' . $e->getMessage());
            }
        }

        $club->update($updateData);

        return redirect()->back()->with('success', 'Đã cập nhật cài đặt CLB thành công.');
    }
}
