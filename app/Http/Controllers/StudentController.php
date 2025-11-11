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

            $clubStats = [
                'members' => ['active' => $activeMembers, 'pending' => $pendingMembers],
                'events' => ['total' => $totalEvents, 'upcoming' => $upcomingEvents],
                'announcements' => ['total' => $totalAnnouncements, 'today' => $todayAnnouncements],
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

}
