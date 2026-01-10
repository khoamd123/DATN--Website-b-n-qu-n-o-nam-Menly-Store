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

        // Load users với phân trang (15 users/trang) và bộ lọc
        $query = User::query();
        
        // Tìm kiếm theo tên hoặc email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Lọc theo role (admin/user)
        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->role === 'user') {
                $query->where('is_admin', false);
            }
        }
        
        // Lọc theo CLB
        if ($request->filled('club_id')) {
            $clubId = $request->club_id;
            $query->whereHas('clubMembers', function($q) use ($clubId) {
                $q->where('club_id', $clubId)
                  ->whereIn('status', ['approved', 'active']);
            });
        }
        
        $users = $query->paginate(10)->appends($request->query());
        
        // Load clubMembers cho mỗi user (force refresh từ DB), chỉ lấy CLB chưa bị xóa
        foreach ($users as $user) {
            $userClubs = \App\Models\ClubMember::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'active'])
                ->with(['club' => function($query) {
                    $query->whereNull('deleted_at');
                }])
                ->get();
            
            $user->setRelation('clubMembers', $userClubs);
        }
        
        // Chỉ load CLB chưa bị xóa
        $clubs = Club::whereNull('deleted_at')
            ->with(['leader', 'clubMembers' => function($query) {
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

        // Tự động gán quyền "xem_bao_cao" cho thành viên mới
        $viewReportPermission = Permission::where('name', 'xem_bao_cao')->first();
        if ($viewReportPermission) {
            // Kiểm tra xem đã có quyền này chưa
            $existingPermission = \DB::table('user_permissions_club')
                ->where('user_id', $userId)
                ->where('club_id', $clubId)
                ->where('permission_id', $viewReportPermission->id)
                ->first();
            
            if (!$existingPermission) {
                \DB::table('user_permissions_club')->insert([
                    'user_id' => $userId,
                    'club_id' => $clubId,
                    'permission_id' => $viewReportPermission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true, 
            'message' => "Đã thêm {$user->name} vào CLB {$club->name} thành công!"
        ]);
    }

    /**
     * Cập nhật quyền của user trong CLB - Logic mới: Cấp quyền theo vai trò được chọn
     */
    public function updateUserPermissions(Request $request)
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $userId = $request->input('user_id');
        $clubId = $request->input('club_id');
        $position = $request->input('position', 'member'); // Nhận position từ request

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $club = Club::find($clubId);
        if (!$club) {
            return response()->json(['success' => false, 'message' => 'Club not found']);
        }

        // Validate position
        if (!in_array($position, ['member', 'treasurer', 'vice_president', 'leader'])) {
            return response()->json(['success' => false, 'message' => 'Vai trò không hợp lệ']);
        }

        // Kiểm tra xem user đã là thành viên CLB này chưa
        $clubMember = ClubMember::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->first();
        
        $isNewMember = false;
        $oldPosition = $clubMember ? $clubMember->position : null;
        
        if (!$clubMember) {
            // Nếu chưa là thành viên, tạo mới
            $clubMember = ClubMember::create([
                'user_id' => $userId,
                'club_id' => $clubId,
                'position' => 'member',
                'status' => 'approved',
                'joined_at' => now()
            ]);
            $isNewMember = true;
            $oldPosition = 'member';
        }

        try {
            // Kiểm tra giới hạn và cấp quyền theo vai trò
            $message = $this->assignPermissionsByPosition($userId, $clubId, $position, $oldPosition);

            $responseMessage = 'Đã cập nhật vai trò và quyền thành công';
            if ($message) {
                $responseMessage .= '. ' . $message;
            }

            return response()->json([
                'success' => true, 
                'message' => $responseMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cấp quyền theo vai trò được chọn
     */
    private function assignPermissionsByPosition($userId, $clubId, $newPosition, $oldPosition = null)
    {
        $clubMember = ClubMember::where('user_id', $userId)
            ->where('club_id', $clubId)
            ->first();
            
        if (!$clubMember) {
            return null;
        }

        $message = null;
        
        // Kiểm tra giới hạn vai trò trước
        $checkResult = $this->enforceRoleLimits($userId, $clubId, $newPosition, $oldPosition);
        if (isset($checkResult['error'])) {
            throw new \Exception($checkResult['error']);
        }
        if (isset($checkResult['message'])) {
            $message = $checkResult['message'];
        }
        if (isset($checkResult['position'])) {
            $newPosition = $checkResult['position']; // Có thể bị thay đổi do giới hạn
        }

        // Xóa tất cả quyền cũ
        \DB::table('user_permissions_club')
            ->where('user_id', $userId)
            ->where('club_id', $clubId)
            ->delete();

        // Cấp quyền mặc định theo vai trò
        $permissionNames = [];
        
        switch ($newPosition) {
            case 'leader':
                // Trưởng CLB: Tất cả quyền
                $permissionNames = ['quan_ly_clb', 'quan_ly_thanh_vien', 'tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'];
                break;
                
            case 'vice_president':
                // Phó CLB: 4 quyền (không có quan_ly_clb)
                $permissionNames = ['quan_ly_thanh_vien', 'tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'];
                break;
                
            case 'treasurer':
                // Thủ quỹ: 2 quyền
                $permissionNames = ['quan_ly_quy', 'xem_bao_cao'];
                break;
                
            case 'member':
            default:
                // Thành viên: Chỉ xem báo cáo
                $permissionNames = ['xem_bao_cao'];
                break;
        }

        // Thêm quyền mới
        $permissions = Permission::whereIn('name', $permissionNames)->get();
        foreach ($permissions as $permission) {
            \DB::table('user_permissions_club')->insert([
                'user_id' => $userId,
                'club_id' => $clubId,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Cập nhật position
        \DB::table('club_members')
            ->where('user_id', $userId)
            ->where('club_id', $clubId)
            ->update(['position' => $newPosition]);

        // Nếu position mới là 'leader', cập nhật leader_id trong bảng clubs
        if ($newPosition === 'leader') {
            \DB::table('clubs')
                ->where('id', $clubId)
                ->update(['leader_id' => $userId]);
        } elseif ($oldPosition === 'leader' && $newPosition !== 'leader') {
            // Nếu Leader cũ bị chuyển sang role khác, xóa leader_id
            \DB::table('clubs')
                ->where('id', $clubId)
                ->where('leader_id', $userId)
                ->update(['leader_id' => null]);
        }

        \Log::info("User {$userId} trong CLB {$clubId}: {$oldPosition} -> {$newPosition}, Quyền: " . implode(', ', $permissionNames));
        
        return $message;
    }

    /**
     * Áp dụng giới hạn vai trò: 1 Leader, 2 Vice President, 1 Treasurer
     * Giới hạn: Mỗi user chỉ được làm thủ quỹ, phó CLB hoặc chủ nhiệm ở 1 CLB
     */
    private function enforceRoleLimits($userId, $clubId, $newPosition, $oldPosition = null)
    {
        $result = ['position' => $newPosition, 'message' => null];
        
        // KIỂM TRA: User đã là thủ quỹ, phó CLB hoặc chủ nhiệm ở CLB khác chưa?
        if (in_array($newPosition, ['leader', 'treasurer', 'vice_president'])) {
            $existingRole = ClubMember::where('user_id', $userId)
                ->where('club_id', '!=', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'treasurer', 'vice_president', 'chunhiem'])
                ->first();
                
            if ($existingRole) {
                $existingClub = Club::find($existingRole->club_id);
                $result['error'] = "Tài khoản này đã là thủ quỹ/phó CLB/chủ nhiệm ở CLB '{$existingClub->name}'. Ở CLB này chỉ được làm thành viên.";
                $result['position'] = 'member';
                return $result;
            }
        }
        
        if ($newPosition === 'leader') {
            // Chỉ được có 1 Leader
            $existingLeader = ClubMember::where('club_id', $clubId)
                ->where('position', 'leader')
                ->where('user_id', '!=', $userId)
                ->first();
                
            if ($existingLeader) {
                // Chuyển Leader cũ về thành viên
                \DB::table('club_members')
                    ->where('user_id', $existingLeader->user_id)
                    ->where('club_id', $clubId)
                    ->update(['position' => 'member']);
                
                // Xóa quyền của leader cũ, chỉ giữ xem_bao_cao
                $xemBaoCaoPerm = Permission::where('name', 'xem_bao_cao')->first();
                \DB::table('user_permissions_club')
                    ->where('user_id', $existingLeader->user_id)
                    ->where('club_id', $clubId)
                    ->delete();
                if ($xemBaoCaoPerm) {
                    \DB::table('user_permissions_club')->insert([
                        'user_id' => $existingLeader->user_id,
                        'club_id' => $clubId,
                        'permission_id' => $xemBaoCaoPerm->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                \Log::info("Chuyển Leader cũ {$existingLeader->user_id} về thành viên trong CLB {$clubId}");
            }
            
        } elseif ($newPosition === 'vice_president') {
            // Được có 2 Vice President
            $vicePresidentCount = ClubMember::where('club_id', $clubId)
                ->where('position', 'vice_president')
                ->where('user_id', '!=', $userId)
                ->count();
                
            if ($vicePresidentCount >= 2) {
                // Nếu đã có 2 phó CLB, không cho phép thêm
                $result['error'] = "CLB này đã có đủ 2 phó CLB. Vui lòng bỏ 1 phó CLB trước khi thêm mới.";
                $result['position'] = $oldPosition ?: 'member';
                return $result;
            }
            
        } elseif ($newPosition === 'treasurer') {
            // Chỉ được có 1 Treasurer
            $existingTreasurer = ClubMember::where('club_id', $clubId)
                ->where('position', 'treasurer')
                ->where('user_id', '!=', $userId)
                ->first();
                
            if ($existingTreasurer) {
                // Chuyển thủ quỹ cũ về thành viên
                \DB::table('club_members')
                    ->where('user_id', $existingTreasurer->user_id)
                    ->where('club_id', $clubId)
                    ->update(['position' => 'member']);
                
                // Xóa quyền của thủ quỹ cũ, chỉ giữ xem_bao_cao
                $xemBaoCaoPerm = Permission::where('name', 'xem_bao_cao')->first();
                \DB::table('user_permissions_club')
                    ->where('user_id', $existingTreasurer->user_id)
                    ->where('club_id', $clubId)
                    ->delete();
                if ($xemBaoCaoPerm) {
                    \DB::table('user_permissions_club')->insert([
                        'user_id' => $existingTreasurer->user_id,
                        'club_id' => $clubId,
                        'permission_id' => $xemBaoCaoPerm->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $result['message'] = "Đã chuyển thủ quỹ cũ về thành viên.";
                \Log::info("Chuyển thủ quỹ cũ {$existingTreasurer->user_id} về thành viên trong CLB {$clubId}");
            }
        }
        
        return $result;
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
