<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Club;
use App\Models\Permission;
use App\Models\ClubMember;

class PermissionController extends Controller
{
    /**
     * Hiển thị trang quản lý permissions
     */
    public function index(Request $request)
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $users = User::with('clubMembers.club')->get();
        $clubs = Club::with('leader', 'activeMembers')->get();
        $permissions = Permission::all();

        return view('admin.permissions.detailed', compact('users', 'clubs', 'permissions'));
    }

    /**
     * Thêm user vào CLB
     */
    public function addToClub(Request $request)
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $userId = $request->input('user_id');
        $clubId = $request->input('club_id');

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['success' => false, 'message' => 'Club not found']);
        }

        // Kiểm tra xem user đã là thành viên chưa
        $existingMember = ClubMember::where('user_id', $userId)
                                   ->where('club_id', $clubId)
                                   ->first();

        if ($existingMember) {
            return response()->json(['success' => false, 'message' => 'User đã là thành viên của CLB này']);
        }

        // Thêm user vào CLB với vai trò mặc định là 'member'
        ClubMember::create([
            'user_id' => $userId,
            'club_id' => $clubId,
            'position' => 'member',
            'status' => 'active',
            'joined_at' => now()
        ]);

        return response()->json([
            'success' => true, 
            'message' => "Đã thêm {$user->name} vào CLB {$club->name} thành công!"
        ]);
    }

    /**
     * Cập nhật quyền của user trong CLB
     */
    public function updateUserPermissions(Request $request)
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $userId = $request->input('user_id');
        $clubId = $request->input('club_id');
        $permissions = $request->input('permissions', []);

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['success' => false, 'message' => 'Club not found']);
        }

        // Xóa quyền cũ
        \DB::table('user_permissions_club')
            ->where('user_id', $userId)
            ->where('club_id', $clubId)
            ->delete();

        // Thêm quyền mới
        foreach ($permissions as $permissionId) {
            \DB::table('user_permissions_club')->insert([
                'user_id' => $userId,
                'club_id' => $clubId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Tự động thay đổi vai trò dựa trên quyền
        $this->updateRoleBasedOnPermissions($userId, $clubId, $permissions);

        return response()->json([
            'success' => true, 
            'message' => 'Đã cập nhật quyền và vai trò thành công'
        ]);
    }

    /**
     * Tự động thay đổi vai trò dựa trên số quyền
     */
    private function updateRoleBasedOnPermissions($userId, $clubId, $permissionIds)
    {
        // Lấy tên các quyền
        $permissionNames = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
        
        // Đếm số quyền
        $permissionCount = count($permissionNames);
        
        // Kiểm tra có quyền khác ngoài xem_bao_cao không
        $hasOtherPermissions = !empty(array_diff($permissionNames, ['xem_bao_cao']));
        
        $newPosition = 'member'; // Mặc định
        
        // Xác định vai trò mới
        if ($permissionCount >= 5) {
            // Có đủ 5 quyền -> Leader (Chủ CLB)
            $newPosition = 'leader';
        } elseif ($hasOtherPermissions) {
            // Có quyền khác ngoài xem_bao_cao -> Officer (Cán sự)
            $newPosition = 'officer';
        } else {
            // Chỉ có xem_bao_cao -> Member
            $newPosition = 'member';
        }
        
        // Kiểm tra giới hạn vai trò
        $this->enforceRoleLimits($userId, $clubId, $newPosition);
    }

    /**
     * Áp dụng giới hạn vai trò: 1 Leader, tối đa 3 Officer
     */
    private function enforceRoleLimits($userId, $clubId, $newPosition)
    {
        $clubMember = ClubMember::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->first();
            
        if (!$clubMember) {
            return;
        }

        $oldPosition = $clubMember->position;
        
        if ($newPosition === 'leader') {
            // Chỉ được có 1 Leader
            $existingLeader = ClubMember::where('club_id', $clubId)
                ->where('position', 'leader')
                ->where('user_id', '!=', $userId)
                ->first();
                
            if ($existingLeader) {
                // Chuyển Leader cũ thành Officer
                $existingLeader->update(['position' => 'officer']);
                \Log::info("Chuyển Leader cũ {$existingLeader->user_id} thành Officer trong CLB {$clubId}");
            }
            
        } elseif ($newPosition === 'officer') {
            // Tối đa 3 Officer
            $currentOfficerCount = ClubMember::where('club_id', $clubId)
                ->where('position', 'officer')
                ->count();
                
            if ($currentOfficerCount >= 3) {
                // Nếu đã có 3 Officer, chuyển user này thành Member
                $newPosition = 'member';
                \Log::info("CLB {$clubId} đã có đủ 3 Officer, chuyển user {$userId} thành Member");
            }
        }
        
        // Cập nhật position
        $clubMember->update(['position' => $newPosition]);
        
        // Log thay đổi
        \Log::info("User {$userId} trong CLB {$clubId}: {$oldPosition} -> {$newPosition}");
    }

    /**
     * Hiển thị trang demo tự động thay đổi vai trò
     */
    public function demo(Request $request)
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $users = User::with('clubMembers.club')->get();
        $permissions = Permission::all();

        return view('admin.demo-auto-role', compact('users', 'permissions'));
    }

    /**
     * Lấy thông tin quyền của user trong CLB
     */
    public function getUserPermissions(Request $request)
    {
        $userId = $request->input('user_id');
        $clubId = $request->input('club_id');

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $permissions = $user->getClubPermissions($clubId);
        $position = $user->getPositionInClub($clubId);

        return response()->json([
            'success' => true,
            'permissions' => $permissions,
            'position' => $position,
            'is_admin' => $user->isAdmin()
        ]);
    }
}
