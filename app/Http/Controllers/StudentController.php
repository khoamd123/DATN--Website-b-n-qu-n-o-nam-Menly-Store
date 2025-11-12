<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Club;
use App\Models\ClubMember;
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
        $userClub = null; // The club the user manages
        $clubMember = null; // The user's membership record
        
        if ($user->clubs->count() > 0) {
            // Assuming a user manages only one club for this view
            $userClub = $user->clubs->first();
            $clubId = $userClub->id;
            
            // Fetch the full ClubMember object
            $clubMember = ClubMember::where('user_id', $user->id)->where('club_id', $clubId)->first();
            $userPosition = $clubMember ? $clubMember->position : null;
            $hasManagementRole = in_array($userPosition, ['leader', 'vice_president', 'officer']);
        }

        return view('student.club-management.index', compact('user', 'hasManagementRole', 'userPosition', 'userClub', 'clubMember'));
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
     * Hiển thị trang báo cáo và thống kê cho CLB.
     */
    public function clubReports(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')->with('error', 'Bạn không thuộc câu lạc bộ nào để xem báo cáo.');
        }

        // Giả sử sinh viên chỉ quản lý 1 CLB tại một thời điểm
        $club = $user->clubs->first();
        $club->load('members', 'events'); // Eager load relationships

        $fund = \App\Models\Fund::where('club_id', $club->id)->first();

        // Kiểm tra quyền xem báo cáo
        if (!$user->hasPermission('xem_bao_cao', $club->id)) {
             return redirect()->route('student.club-management.index')->with('error', 'Bạn không có quyền xem báo cáo.');
        }

        // Thu thập dữ liệu thống kê
        $totalMembers = $club->members->count();
        $newMembersThisMonth = $club->members()
            ->wherePivot('created_at', '>=', \Carbon\Carbon::now()->startOfMonth())
            ->count();
        
        $totalEvents = $club->events->count();
        $upcomingEvents = $club->events()->where('start_time', '>', \Carbon\Carbon::now())->count();
        $pastEvents = $totalEvents - $upcomingEvents;

        // Dữ liệu cho biểu đồ tăng trưởng thành viên (3 tháng gần nhất)
        $memberGrowthData = [];
        $memberGrowthLabels = [];
        for ($i = 2; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $memberGrowthLabels[] = 'Tháng ' . $date->month;
            $memberGrowthData[] = $club->members()
                ->wherePivot('created_at', '<=', $date->endOfMonth())
                ->count();
        }

        // Thống kê quỹ
        $fundStats = [
            'totalIncome' => 0,
            'totalExpense' => 0,
            'balance' => 0,
            'expenseByCategory' => [],
        ];
        if ($fund) {
            $fundStats['totalIncome'] = $fund->transactions()->where('status', 'approved')->where('type', 'income')->sum('amount');
            $fundStats['totalExpense'] = $fund->transactions()->where('status', 'approved')->where('type', 'expense')->sum('amount');
            $fundStats['balance'] = $fundStats['totalIncome'] - $fundStats['totalExpense'];
            $fundStats['expenseByCategory'] = $fund->transactions()
                ->where('status', 'approved')
                ->where('type', 'expense')
                ->groupBy('category')
                ->selectRaw('category, sum(amount) as total')
                ->pluck('total', 'category');
        }

        // Biểu đồ tròn cơ cấu thành viên
        $memberStructure = \App\Models\ClubMember::where('club_id', $club->id)
            ->where('status', 'active')
            ->groupBy('position')
            ->selectRaw('position, count(*) as count')
            ->pluck('count', 'position');

        // Biểu đồ cột sự kiện theo tháng (12 tháng gần nhất)
        $eventsByMonth = \App\Models\Event::where('club_id', $club->id)
            ->where('start_time', '>=', \Carbon\Carbon::now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(start_time, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('count', 'month');

        // Biểu đồ đường xu hướng hoạt động (6 tháng gần nhất)
        $activityTrendLabels = [];
        $activityTrendNewMembers = [];
        $activityTrendNewEvents = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $activityTrendLabels[] = 'Tháng ' . $date->month;
            $activityTrendNewMembers[] = $club->members()->wherePivot('created_at', '>=', $date->startOfMonth())->wherePivot('created_at', '<=', $date->endOfMonth())->count();
            $activityTrendNewEvents[] = $club->events()->where('created_at', '>=', $date->startOfMonth())->where('created_at', '<=', $date->endOfMonth())->count();
        }

        $stats = [
            'totalMembers' => $totalMembers,
            'newMembersThisMonth' => $newMembersThisMonth,
            'totalEvents' => $totalEvents,
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
            'memberGrowth' => [
                'labels' => $memberGrowthLabels,
                'data' => $memberGrowthData,
            ],
            'fund' => $fundStats,
            'memberStructure' => [
                'labels' => $memberStructure->keys(),
                'data' => $memberStructure->values(),
            ],
            'eventsByMonth' => [
                'labels' => $eventsByMonth->keys(),
                'data' => $eventsByMonth->values(),
            ],
            'activityTrend' => [
                'labels' => $activityTrendLabels,
                'newMembers' => $activityTrendNewMembers,
                'newEvents' => $activityTrendNewEvents,
            ],
        ];

        return view('student.club-management.reports', compact('user', 'club', 'stats'));
    }

    /**
     * Display a single club's details page.
     */
    public function showClub(Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club->load(['posts' => function ($query) {
            $query->where('status', 'published')->orderBy('created_at', 'desc')->limit(5);
        }, 'events' => function ($query) {
            $query->where('status', 'approved')->orderBy('start_time', 'asc');
        }, 'members']);

        $isMember = $user->clubs()->where('club_id', $club->id)->exists();

        return view('student.clubs.show', compact('user', 'club', 'isMember'));
    }

    /**
     * Allow a student to leave a club.
     */
    public function leaveClub(Request $request, Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $membership = ClubMember::where('user_id', $user->id)
            ->where('club_id', $club->id)
            ->first();

        if (!$membership) {
            return redirect()->back()->with('error', 'Bạn không phải là thành viên của câu lạc bộ này.');
        }

        // Prevent leaders from leaving directly
        if (in_array($membership->position, ['leader', 'owner'])) {
            return redirect()->back()->with('error', 'Trưởng/Chủ nhiệm CLB không thể rời đi. Vui lòng chuyển giao vai trò trước.');
        }

        $membership->delete();

        // Update session
        $clubRoles = session('club_roles', []);
        unset($clubRoles[$club->id]);
        session(['club_roles' => $clubRoles]);

        return redirect()->route('student.clubs.index')->with('success', 'Bạn đã rời khỏi câu lạc bộ ' . $club->name . ' thành công.');
    }

    /**
     * Display a single event's details page.
     */
    public function showEvent(Event $event)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $event->load('club', 'participants');

        // Check if user can view (e.g., if it's a members-only event)
        // This logic can be expanded based on requirements

        return view('student.events.show', compact('user', 'event'));
    }

    /**
     * Show the form for creating a new post in a club's forum.
     */
    public function createClubPost(Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Check if user is a member of the club
        $isMember = $user->clubs()->where('club_id', $club->id)->exists();

        if (!$isMember) {
            return redirect()->route('student.clubs.show', $club->id)->with('error', 'Chỉ thành viên mới có thể đăng bài.');
        }

        return view('student.posts.create', compact('user', 'club'));
    }
}
