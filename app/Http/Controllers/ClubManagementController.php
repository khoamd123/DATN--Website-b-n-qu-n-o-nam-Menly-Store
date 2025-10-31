<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use App\Models\User;
use App\Models\ClubMember;
use Illuminate\Support\Facades\Hash;

class ClubManagementController extends Controller
{
    /**
     * Display clubs list for admin
     */
    public function index()
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $clubs = Club::with(['owner', 'members'])->paginate(20);
        return view('admin.clubs.index', compact('clubs'));
    }

    /**
     * Show create club form for admin
     */
    public function create()
    {
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $users = User::where('is_admin', false)->get();
        return view('admin.clubs.create', compact('users'));
    }

    /**
     * Store new club for admin
     */
    public function store(Request $request)
    {
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'executive_board' => 'array',
            'executive_board.*' => 'exists:users,id'
        ]);

        // Tạo CLB
        $club = Club::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => $request->owner_id,
            'status' => 'active'
        ]);

        // Thêm chủ CLB
        ClubMember::create([
            'user_id' => $request->owner_id,
            'club_id' => $club->id,
            'role' => 'owner'
        ]);

        // Thêm ban cán sự
        if ($request->executive_board) {
            foreach ($request->executive_board as $userId) {
                ClubMember::create([
                    'user_id' => $userId,
                    'club_id' => $club->id,
                    'role' => 'executive_board'
                ]);
            }
        }

        return redirect()->route('admin.clubs.index')->with('success', 'Câu lạc bộ đã được tạo thành công!');
    }

    /**
     * Display club members management for club owner/executive
     */
    public function manageMembers($clubId)
    {
        // Kiểm tra quyền truy cập CLB
        $clubRoles = session('club_roles', []);
        $userRole = $clubRoles[$clubId] ?? null;

        if (!session('is_admin') && !in_array($userRole, ['owner', 'executive_board'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền quản lý thành viên CLB này.');
        }

        $club = Club::with(['owner', 'members.user'])->findOrFail($clubId);
        $availableUsers = User::where('is_admin', false)
            ->whereDoesntHave('clubMembers', function($query) use ($clubId) {
                $query->where('club_id', $clubId);
            })
            ->get();

        return view('club.members.manage', compact('club', 'availableUsers'));
    }

    /**
     * Add member to club
     */
    public function addMember(Request $request, $clubId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'position' => 'required|in:member,officer,leader'
        ]);

        // Kiểm tra quyền (chỉ admin mới được thêm)
        if (!session('is_admin')) {
            return redirect()->back()->with('error', 'Bạn không có quyền thêm thành viên.');
        }

        // Kiểm tra user đã là thành viên chưa
        $existingMember = ClubMember::where('user_id', $request->user_id)
            ->where('club_id', $clubId)
            ->first();

        if ($existingMember) {
            return redirect()->back()->with('error', 'Người dùng đã là thành viên của CLB này.');
        }

        // Nếu thêm làm leader/officer, kiểm tra giới hạn
        if (in_array($request->position, ['leader', 'officer'])) {
            $alreadyLeaderOfficer = ClubMember::where('user_id', $request->user_id)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'officer'])
                ->whereHas('club', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->first();
                
            if ($alreadyLeaderOfficer) {
                $club = Club::find($alreadyLeaderOfficer->club_id);
                return redirect()->back()->with('error', "Người này đã là cán sự/trưởng ở CLB '{$club->name}'. Một người chỉ được làm cán sự/trưởng ở 1 CLB.");
            }
        }

        // Thêm thành viên
        ClubMember::create([
            'user_id' => $request->user_id,
            'club_id' => $clubId,
            'position' => $request->position,
            'status' => 'active',
            'joined_at' => now(),
        ]);
        
        // Nếu thêm làm leader, cập nhật clubs.leader_id và cấp quyền
        if ($request->position === 'leader') {
            \DB::table('clubs')->where('id', $clubId)->update(['leader_id' => $request->user_id]);
            
            // Cấp tất cả quyền
            $allPermissions = \App\Models\Permission::all();
            foreach ($allPermissions as $permission) {
                \DB::table('user_permissions_club')->insert([
                    'user_id' => $request->user_id,
                    'club_id' => $clubId,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Đã thêm thành viên thành công!');
    }

    /**
     * Remove member from club
     */
    public function removeMember(Request $request, $clubId, $userId)
    {
        // Kiểm tra quyền
        $clubRoles = session('club_roles', []);
        $userRole = $clubRoles[$clubId] ?? null;

        if (!session('is_admin') && !in_array($userRole, ['owner', 'executive_board'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa thành viên.');
        }

        $member = ClubMember::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->first();

        if (!$member) {
            return redirect()->back()->with('error', 'Không tìm thấy thành viên.');
        }

        // Không cho phép xóa chủ CLB
        if ($member->role === 'owner') {
            return redirect()->back()->with('error', 'Không thể xóa chủ câu lạc bộ.');
        }

        $member->delete();

        return redirect()->back()->with('success', 'Đã xóa thành viên thành công!');
    }

    /**
     * Create student account (for admin)
     */
    public function createStudentAccount(Request $request)
    {
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'student_id' => 'required|string|unique:users,student_id',
            'password' => 'required|string|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'student_id' => $request->student_id,
            'password' => Hash::make($request->password),
            'is_admin' => false
        ]);

        return redirect()->back()->with('success', 'Tài khoản sinh viên đã được tạo thành công!');
    }
}

