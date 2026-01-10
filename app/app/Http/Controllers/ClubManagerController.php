<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use App\Models\User;
use App\Models\Event;
use App\Models\Post;
use App\Models\Permission;

class ClubManagerController extends Controller
{
    /**
     * Display club manager dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Lấy các CLB mà user quản lý
        $managedClubs = $user->managedClubs();
        
        // Thống kê cho từng CLB
        $clubStats = [];
        foreach ($managedClubs as $club) {
            $clubStats[] = [
                'club' => $club,
                'totalMembers' => $club->members()->count(),
                'totalEvents' => $club->events()->count(),
                'totalPosts' => $club->posts()->count(),
                'pendingEvents' => $club->events()->where('status', 'pending')->count(),
                'pendingPosts' => $club->posts()->where('status', 'pending')->count(),
            ];
        }

        // Sự kiện sắp diễn ra của các CLB quản lý
        $upcomingEvents = Event::whereIn('club_id', $managedClubs->pluck('id'))
            ->where('start_time', '>', now())
            ->where('status', 'active')
            ->with('club')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();

        // Bài viết mới của các CLB quản lý
        $recentPosts = Post::whereIn('club_id', $managedClubs->pluck('id'))
            ->with(['club', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('club-manager.dashboard', compact(
            'managedClubs', 
            'clubStats', 
            'upcomingEvents', 
            'recentPosts'
        ));
    }

    /**
     * Display clubs management page
     */
    public function clubs()
    {
        $user = auth()->user();
        $managedClubs = $user->managedClubs();
        
        return view('club-manager.clubs.index', compact('managedClubs'));
    }

    /**
     * Display specific club management page
     */
    public function showClub(Club $club)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền truy cập CLB
        if (!$user->isAdmin() && !$user->managedClubs()->contains('id', $club->id)) {
            return redirect()->route('club-manager.dashboard')
                ->with('error', 'Bạn không có quyền quản lý CLB này.');
        }

        // Thống kê CLB
        $stats = [
            'totalMembers' => $club->members()->count(),
            'totalEvents' => $club->events()->count(),
            'totalPosts' => $club->posts()->count(),
            'activeEvents' => $club->events()->where('status', 'active')->count(),
            'pendingEvents' => $club->events()->where('status', 'pending')->count(),
            'publishedPosts' => $club->posts()->where('status', 'published')->count(),
            'pendingPosts' => $club->posts()->where('status', 'pending')->count(),
        ];

        // Thành viên mới
        $newMembers = $club->members()
            ->with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Sự kiện sắp diễn ra
        $upcomingEvents = $club->events()
            ->where('start_time', '>', now())
            ->where('status', 'active')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();

        return view('club-manager.clubs.show', compact(
            'club', 
            'stats', 
            'newMembers', 
            'upcomingEvents'
        ));
    }

    /**
     * Display members management page for a club
     */
    public function members(Request $request, Club $club)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền (Admin, Club Manager, hoặc Executive Board)
        if (!$user->canManageClubMembers() && !$user->managedClubs()->contains('id', $club->id)) {
            return redirect()->route('club-manager.dashboard')
                ->with('error', 'Bạn không có quyền quản lý thành viên CLB này.');
        }

        $query = $club->members()->with('user');
        
        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('club-manager.clubs.members', compact('club', 'members'));
    }

    /**
     * Display permissions management page for a club
     */
    public function permissions(Club $club)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->managedClubs()->contains('id', $club->id)) {
            return redirect()->route('club-manager.dashboard')
                ->with('error', 'Bạn không có quyền quản lý phân quyền CLB này.');
        }

        // Lấy tất cả permissions
        $permissions = Permission::all();
        
        // Lấy thành viên CLB với permissions
        $members = $club->members()
            ->with(['user', 'permissions'])
            ->get();

        return view('club-manager.clubs.permissions', compact('club', 'permissions', 'members'));
    }

    /**
     * Update member permissions
     */
    public function updateMemberPermissions(Request $request, Club $club, User $member)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->managedClubs()->contains('id', $club->id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }

        // Xóa permissions cũ
        \App\Models\UserPermissionClub::where('user_id', $member->id)
            ->where('club_id', $club->id)
            ->delete();

        // Thêm permissions mới
        if ($request->has('permissions')) {
            foreach ($request->permissions as $permissionId) {
                \App\Models\UserPermissionClub::create([
                    'user_id' => $member->id,
                    'club_id' => $club->id,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Cập nhật quyền thành viên thành công!');
    }
}
