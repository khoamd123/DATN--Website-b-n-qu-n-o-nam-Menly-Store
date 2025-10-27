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

        // Load users với clubMembers có status = 'approved' hoặc 'active'
        $users = User::all();
        
        // Load clubMembers cho mỗi user (force refresh từ DB)
        foreach ($users as $user) {
            $userClubs = \App\Models\ClubMember::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'active'])
                ->with('club')
                ->get();
            $user->setRelation('clubMembers', $userClubs);
        }
        
        $clubs = Club::with(['leader', 'clubMembers' => function($query) {
            $query->where('status', 'approved');
        }])->get();
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
            'status' => 'approved',  // Sửa từ 'active' thành 'approved'
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

        // KIỂM TRA TRƯỚC: User đã là cán sự/chủ nhiệm ở CLB khác chưa?
        $existingLeaderOfficer = ClubMember::where('user_id', $userId)
            ->where('club_id', '!=', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->whereIn('position', ['leader', 'officer', 'chunhiem'])
            ->first();
            
        // Xác định position mong muốn từ số quyền
        $permissionCount = count($permissions);
        $permissionNames = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
        $hasOtherPermissions = !empty(array_diff($permissionNames, ['xem_bao_cao']));
        
        $desiredPosition = 'member'; // Mặc định
        if ($permissionCount >= 5) {
            $desiredPosition = 'leader';
        } elseif ($hasOtherPermissions && $permissionCount >= 2) {
            $desiredPosition = 'officer';
        }
        
        // Nếu user muốn làm leader/officer nhưng đã là leader/officer ở CLB khác
        if ($desiredPosition === 'leader' || $desiredPosition === 'officer') {
            if ($existingLeaderOfficer) {
                $existingClub = Club::find($existingLeaderOfficer->club_id);
                return response()->json([
                    'success' => false,
                    'message' => "Thất bại: Tài khoản này đã là cán sự/chủ nhiệm ở CLB '{$existingClub->name}'. Không thể cấp quyền cán sự/chủ nhiệm ở CLB này."
                ]);
            }
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

        // Kiểm tra xem user đã là thành viên CLB này chưa
        $clubMember = ClubMember::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->first();
        
        $isNewMember = false;
        
        if (!$clubMember) {
            // Nếu chưa là thành viên, tạo mới với position = 'member'
            $clubMember = ClubMember::create([
                'user_id' => $userId,
                'club_id' => $clubId,
                'position' => 'member',
                'status' => 'approved',  // Sửa từ 'active' thành 'approved'
                'joined_at' => now()
            ]);
            $isNewMember = true;
            \Log::info("Created new ClubMember: user_id={$userId}, club_id={$clubId}, position=member");
        }
        
        // Nếu là thành viên mới và chưa có quyền nào, tự động thêm quyền "xem_bao_cao"
        if ($isNewMember && empty($permissions)) {
            $viewReportPermission = Permission::where('name', 'xem_bao_cao')->first();
            if ($viewReportPermission) {
                \DB::table('user_permissions_club')->insert([
                    'user_id' => $userId,
                    'club_id' => $clubId,
                    'permission_id' => $viewReportPermission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permissions = [$viewReportPermission->id];
            }
        }
        
        // Tự động thay đổi vai trò dựa trên quyền
        $message = $this->updateRoleBasedOnPermissions($userId, $clubId, $permissions);

        $responseMessage = 'Đã cập nhật quyền và vai trò thành công';
        if ($message) {
            $responseMessage .= '. ' . $message;
        }

        return response()->json([
            'success' => true, 
            'message' => $responseMessage
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
        } elseif ($hasOtherPermissions && $permissionCount >= 2) {
            // Có 2-4 quyền và có quyền khác ngoài xem_bao_cao -> Officer (Cán sự)
            $newPosition = 'officer';
        } else {
            // Chỉ có xem_bao_cao -> Member
            $newPosition = 'member';
        }
        
        \Log::info("User {$userId} trong CLB {$clubId}: Permission count = {$permissionCount}, Has other permissions = " . ($hasOtherPermissions ? 'true' : 'false') . ", New position = {$newPosition}");
        
        // Kiểm tra giới hạn vai trò
        $message = $this->enforceRoleLimits($userId, $clubId, $newPosition);
        
        return $message;
    }

    /**
     * Áp dụng giới hạn vai trò: 1 Leader, tối đa 3 Officer
     * Giới hạn mới: Mỗi user chỉ được làm cán sự hoặc chủ nhiệm ở 1 CLB
     */
    private function enforceRoleLimits($userId, $clubId, $newPosition)
    {
        $clubMember = ClubMember::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->first();
            
        if (!$clubMember) {
            return null;
        }

        $oldPosition = $clubMember->position;
        $message = null;
        
        // KIỂM TRA: User đã là cán sự hoặc chủ nhiệm ở CLB khác chưa?
        if ($newPosition === 'leader' || $newPosition === 'officer') {
            $existingLeaderOfficer = ClubMember::where('user_id', $userId)
                ->where('club_id', '!=', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'officer', 'chunhiem'])
                ->first();
                
            if ($existingLeaderOfficer) {
                // User đã là cán sự/chủ nhiệm ở CLB khác
                $existingClub = Club::find($existingLeaderOfficer->club_id);
                $newPosition = 'member'; // Chỉ được làm thành viên
                $message = "Cảnh báo: Tài khoản này đã là cán sự/chủ nhiệm ở CLB '{$existingClub->name}'. Ở CLB này chỉ được làm thành viên.";
                \Log::info("User {$userId} đã là leader/officer ở CLB {$existingLeaderOfficer->club_id}, không thể làm {$newPosition} ở CLB {$clubId}");
            }
        }
        
        if ($newPosition === 'leader') {
            // Chỉ được có 1 Leader
            $existingLeader = ClubMember::where('club_id', $clubId)
                ->where('position', 'leader')
                ->where('user_id', '!=', $userId)
                ->first();
                
            if ($existingLeader) {
                // Chuyển Leader cũ thành Officer (sử dụng DB trực tiếp)
                \DB::table('club_members')
                    ->where('user_id', $existingLeader->user_id)
                    ->where('club_id', $clubId)
                    ->update(['position' => 'officer']);
                \Log::info("Chuyển Leader cũ {$existingLeader->user_id} thành Officer trong CLB {$clubId} [DIRECT DB UPDATE]");
            }
            
        } elseif ($newPosition === 'officer') {
            // Tối đa 3 Officer (trừ chính user này ra)
            $currentOfficerCount = ClubMember::where('club_id', $clubId)
                ->where('position', 'officer')
                ->where('user_id', '!=', $userId)
                ->count();
                
            if ($currentOfficerCount >= 3) {
                // Nếu đã có 3 Officer (không tính user hiện tại), chuyển user này thành Member
                $newPosition = 'member';
                $message = "CLB này đã có đủ 3 cán sự. Tài khoản này sẽ là thành viên.";
                \Log::info("CLB {$clubId} đã có đủ 3 Officer, chuyển user {$userId} thành Member do giới hạn");
            }
        }
        
        \Log::info("Final position for user {$userId} in club {$clubId}: {$newPosition}");
        
        // Map position (leader, officer, member) to role_in_club (chunhiem, phonhiem, thanhvien)
        $roleInClub = 'thanhvien'; // Mặc định
        if ($newPosition === 'leader') $roleInClub = 'chunhiem';
        elseif ($newPosition === 'officer') $roleInClub = 'phonhiem';
        
        // Cập nhật role_in_club (cột thực tế trong DB)
        $updated = \DB::table('club_members')
            ->where('user_id', $userId)
            ->where('club_id', $clubId)
            ->update(['role_in_club' => $roleInClub]);
        
        \Log::info("DB update result: " . ($updated ? "SUCCESS (1 row affected)" : "FAILED (0 rows affected)"));
        
        // Nếu position mới là 'leader', cập nhật leader_id trong bảng clubs
        if ($newPosition === 'leader' && $updated) {
            \DB::table('clubs')
                ->where('id', $clubId)
                ->update(['leader_id' => $userId]);
            \Log::info("Updated leader_id for club {$clubId} to user {$userId}");
        } elseif ($oldPosition === 'leader' && $newPosition !== 'leader') {
            // Nếu Leader cũ bị chuyển sang role khác, xóa leader_id
            \DB::table('clubs')
                ->where('id', $clubId)
                ->where('leader_id', $userId)
                ->update(['leader_id' => null]);
            \Log::info("Removed leader_id for club {$clubId} (user {$userId} is no longer leader)");
        }
        
        // Log thay đổi
        \Log::info("User {$userId} trong CLB {$clubId}: {$oldPosition} -> {$newPosition} [DIRECT DB UPDATE]");
        
        return $message;
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

    /**
     * Lấy vị trí của user trong CLB
     */
    public function getUserPosition(Request $request)
    {
        $userId = $request->input('user_id');
        $clubId = $request->input('club_id');

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $position = $user->getPositionInClub($clubId);

        return response()->json([
            'success' => true,
            'position' => $position ?: 'member'
        ]);
    }
}
