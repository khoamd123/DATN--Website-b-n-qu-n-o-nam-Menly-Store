<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubJoinRequest;

class ClubTestController extends Controller
{
    /**
     * Hiển thị trang test phân quyền
     */
    public function testPage()
    {
        // Tạm thời không check login để test
        $user = \App\Models\User::find(session('user_id'));
        if (!$user) {
            // Nếu chưa đăng nhập, lấy user đầu tiên để test
            $user = \App\Models\User::first();
        }
        
        $clubs = Club::with(['leader', 'activeMembers'])->get();
        $clubRoles = session('club_roles', []);

        return view('club.test-simple', compact('user', 'clubs', 'clubRoles'));
    }

    /**
     * Test quyền leader - chỉ leader mới truy cập được
     */
    public function testLeader(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!session('logged_in')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập trước khi test.',
                'role' => null
            ], 401);
        }

        $userId = session('user_id');
        $user = \App\Models\User::find($userId);
        $clubId = $request->input('club_id');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin người dùng.',
                'role' => null
            ], 401);
        }

        // Kiểm tra quyền quan_ly_clb (chỉ leader có)
        if (!$user->hasPermission('quan_ly_clb', $clubId)) {
            $position = $user->getPositionInClub($clubId);
            $permissions = $user->getClubPermissions($clubId);
            
            return response()->json([
                'success' => false,
                'message' => 'Chỉ trưởng CLB mới có quyền quản lý CLB.',
                'role' => $position,
                'permissions' => $permissions
            ]);
        }

        $position = $user->getPositionInClub($clubId);
        $permissions = $user->getClubPermissions($clubId);

        return response()->json([
            'success' => true,
            'message' => 'Chào mừng! Bạn có quyền quản lý CLB.',
            'role' => $position,
            'permissions' => $permissions
        ]);
    }

    /**
     * Test quyền officer - leader, vice_president, officer
     */
    public function testOfficer(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!session('logged_in')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập trước khi test.',
                'role' => null
            ], 401);
        }

        $userId = session('user_id');
        $user = \App\Models\User::find($userId);
        $clubId = $request->input('club_id');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin người dùng.',
                'role' => null
            ], 401);
        }

        // Kiểm tra quyền tao_su_kien (officer trở lên có)
        if (!$user->hasPermission('tao_su_kien', $clubId)) {
            $position = $user->getPositionInClub($clubId);
            $permissions = $user->getClubPermissions($clubId);
            
            return response()->json([
                'success' => false,
                'message' => 'Chỉ cán sự trở lên mới có quyền tạo sự kiện.',
                'role' => $position,
                'permissions' => $permissions
            ]);
        }

        $position = $user->getPositionInClub($clubId);
        $permissions = $user->getClubPermissions($clubId);

        return response()->json([
            'success' => true,
            'message' => 'Chào mừng! Bạn có quyền tạo sự kiện.',
            'role' => $position,
            'permissions' => $permissions
        ]);
    }

    /**
     * Test quyền member - tất cả thành viên
     */
    public function testMember(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!session('logged_in')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập trước khi test.',
                'role' => null
            ], 401);
        }

        $userId = session('user_id');
        $user = \App\Models\User::find($userId);
        $clubId = $request->input('club_id');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin người dùng.',
                'role' => null
            ], 401);
        }

        // Kiểm tra quyền xem_bao_cao (member trở lên có)
        if (!$user->hasPermission('xem_bao_cao', $clubId)) {
            $position = $user->getPositionInClub($clubId);
            $permissions = $user->getClubPermissions($clubId);
            
            return response()->json([
                'success' => false,
                'message' => 'Bạn không phải là thành viên của CLB này hoặc không có quyền xem báo cáo.',
                'role' => $position,
                'permissions' => $permissions
            ]);
        }

        $position = $user->getPositionInClub($clubId);
        $permissions = $user->getClubPermissions($clubId);

        return response()->json([
            'success' => true,
            'message' => 'Chào mừng thành viên CLB! Bạn có quyền xem báo cáo.',
            'role' => $position,
            'permissions' => $permissions
        ]);
    }

    /**
     * Lấy thông tin CLB
     */
    public function getClubInfo($clubId)
    {
        $club = Club::with(['leader', 'activeMembers.user'])->find($clubId);
        
        if (!$club) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy CLB']);
        }

        return response()->json([
            'success' => true,
            'club' => $club,
            'members_count' => $club->activeMembers->count(),
            'leader_name' => $club->leader ? $club->leader->name : 'Chưa có'
        ]);
    }
}
