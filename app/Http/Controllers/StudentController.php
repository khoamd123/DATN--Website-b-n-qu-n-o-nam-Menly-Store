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
use App\Models\FundRequest;
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

    public function clubs(Request $request) 
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $search = $request->input('search');

        // Lấy ID các CLB mà người dùng đã tham gia
        $myClubIds = $user->clubs->pluck('id')->toArray();

        // Luôn lấy đầy đủ danh sách CLB của tôi, không bị ảnh hưởng bởi tìm kiếm
        $myClubs = Club::whereIn('id', $myClubIds)
            ->where('status', 'active')
            ->withCount('members')
            ->orderBy('name')
            ->get();

        // Query các CLB khác (chưa tham gia)
        $otherClubsQuery = Club::where('status', 'active')
            ->whereNotIn('id', $myClubIds)
            ->withCount('members');

        // Áp dụng bộ lọc tìm kiếm cho các CLB khác
        if ($search) {
            $otherClubsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        // Lấy kết quả với phân trang
        $otherClubs = $otherClubsQuery->orderBy('name')->paginate(8);

        return view('student.clubs.index', compact('user', 'myClubs', 'otherClubs', 'search'));
    }

    public function ajaxSearchClubs(Request $request)
    {
        $user = $this->checkStudentAuth();
        // Nếu checkStudentAuth trả về một redirect, nghĩa là chưa đăng nhập, trả về lỗi
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response('Unauthorized.', 401);
        }

        $search = $request->input('search', '');
        $myClubIds = $user->clubs->pluck('id')->toArray();

        $otherClubs = Club::where('status', 'active')
            ->whereNotIn('id', $myClubIds)
            ->where('name', 'like', '%' . $search . '%')
            ->withCount('members')
            ->orderBy('name')
            ->paginate(8);

        return view('student.clubs._other_clubs_list', compact('otherClubs', 'search'));
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
            // Assuming a user manages only one club for this view
            $userClub = $user->clubs->first();
            $clubId = $userClub->id;
            
            // Fetch the full ClubMember object
            $clubMember = ClubMember::where('user_id', $user->id)->where('club_id', $clubId)->first();
            $userPosition = $clubMember ? $clubMember->position : null;
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

            // Fund requests stats
            $totalFundRequests = FundRequest::where('club_id', $clubId)->count();
            $pendingFundRequests = FundRequest::where('club_id', $clubId)->where('status', 'pending')->count();

            // Fund stats
            $fund = \App\Models\Fund::where('club_id', $clubId)->first();
            $fundId = $fund?->id ?? null;
            $totalIncome = 0;
            $totalExpense = 0;
            $balance = 0;

            if ($fundId) {
                $totalIncome = \App\Models\FundTransaction::where('fund_id', $fundId)
                    ->where('type', 'income')
                    ->where('status', 'approved')
                    ->sum('amount');
                $totalExpense = \App\Models\FundTransaction::where('fund_id', $fundId)
                    ->where('type', 'expense')
                    ->where('status', 'approved')
                    ->sum('amount');
                $balance = (int) ($fund->initial_amount ?? 0) + (int) $totalIncome - (int) $totalExpense;
            }

            $clubStats = [
                'members' => ['active' => $activeMembers, 'pending' => $pendingMembers],
                'events' => ['total' => $totalEvents, 'upcoming' => $upcomingEvents],
                'announcements' => ['total' => $totalAnnouncements, 'today' => $todayAnnouncements],
                'fundRequests' => ['total' => $totalFundRequests, 'pending' => $pendingFundRequests],
                'fund' => [
                    'balance' => $balance,
                    'income' => $totalIncome,
                    'expense' => $totalExpense,
                    'exists' => $fund !== null,
                ],
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
     * Update member permissions and position in a club
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
            'position' => 'nullable|in:member,officer,vice_president',
        ]);

        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->firstOrFail();

        if (!$clubMember->user || $clubMember->user_id === $user->id) {
            return redirect()->back()->with('error', 'Không hợp lệ.');
        }

        // Không cho phép thay đổi position của leader hoặc owner
        if (in_array($clubMember->position, ['leader', 'owner'])) {
            return redirect()->back()->with('error', 'Không thể thay đổi vai trò của Trưởng CLB hoặc Chủ nhiệm.');
        }

        $permissionNames = $request->input('permissions', []);
        
        // Nếu không có quyền nào được chọn, tự động gán quyền "xem báo cáo"
        if (empty($permissionNames)) {
            $permissionNames = ['xem_bao_cao'];
        }
        
        $permissionIds = Permission::whereIn('name', (array) $permissionNames)->pluck('id');

        try {
            DB::transaction(function () use ($clubMember, $clubId, $permissionIds, $request) {
                // Cập nhật permissions
                DB::table('user_permissions_club')
                    ->where('user_id', $clubMember->user_id)
                    ->where('club_id', $clubId)
                    ->delete();
                    
                // Đảm bảo luôn có ít nhất quyền "xem báo cáo"
                $xemBaoCaoId = Permission::where('name', 'xem_bao_cao')->first();
                if ($xemBaoCaoId) {
                    $permissionIdsArray = $permissionIds->toArray();
                    if (!in_array($xemBaoCaoId->id, $permissionIdsArray)) {
                        $permissionIdsArray[] = $xemBaoCaoId->id;
                    }
                    $permissionIds = collect($permissionIdsArray);
                }
                
                foreach ($permissionIds as $pid) {
                    DB::table('user_permissions_club')->insert([
                        'user_id' => $clubMember->user_id,
                        'club_id'  => $clubId,
                        'permission_id' => $pid,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Query lại từ database để đảm bảo tính toán position chính xác
                $permissionCount = DB::table('user_permissions_club')
                    ->where('user_id', $clubMember->user_id)
                    ->where('club_id', $clubId)
                    ->count();
                
                $permissionNames = DB::table('user_permissions_club')
                    ->where('user_id', $clubMember->user_id)
                    ->where('club_id', $clubId)
                    ->join('permissions', 'user_permissions_club.permission_id', '=', 'permissions.id')
                    ->pluck('permissions.name')
                    ->toArray();
                
                $hasOtherPermissions = !empty(array_diff($permissionNames, ['xem_bao_cao']));
                
                $calculatedPosition = 'member'; // Mặc định
                
                // Xác định vai trò mới dựa trên số quyền
                if ($permissionCount >= 5) {
                    // Có đủ 5 quyền -> Leader (Trưởng CLB)
                    $calculatedPosition = 'leader';
                } elseif ($permissionCount === 4 && $hasOtherPermissions) {
                    // Có 4 quyền và có quyền khác ngoài xem_bao_cao -> Vice President (Phó CLB)
                    $calculatedPosition = 'vice_president';
                } elseif ($hasOtherPermissions && $permissionCount >= 2) {
                    // Có 2-3 quyền và có quyền khác ngoài xem_bao_cao -> Officer (Cán sự)
                    $calculatedPosition = 'officer';
                } else {
                    // Chỉ có xem_bao_cao -> Member
                    $calculatedPosition = 'member';
                }
                
                // Kiểm tra giới hạn số lượng officer (tối đa 2)
                if ($calculatedPosition === 'officer') {
                    $officerCount = ClubMember::where('club_id', $clubId)
                        ->whereIn('status', ['approved', 'active'])
                        ->where('position', 'officer')
                        ->where('id', '!=', $clubMember->id)
                        ->count();
                    
                    if ($officerCount >= 2) {
                        // Nếu đã đủ officer, chuyển về member
                        $calculatedPosition = 'member';
                    }
                }
                
                // Kiểm tra giới hạn số lượng vice_president (chỉ 1)
                if ($calculatedPosition === 'vice_president') {
                    $vicePresidentCount = ClubMember::where('club_id', $clubId)
                        ->whereIn('status', ['approved', 'active'])
                        ->where('position', 'vice_president')
                        ->where('id', '!=', $clubMember->id)
                        ->count();
                    
                    if ($vicePresidentCount >= 1) {
                        // Nếu đã có vice president, chuyển về officer nếu có >= 2 quyền, nếu không thì member
                        $calculatedPosition = ($hasOtherPermissions && $permissionCount >= 2) ? 'officer' : 'member';
                    }
                }
                
                // Cập nhật position dựa trên quyền (ưu tiên position được tính toán từ quyền)
                // Nếu user chọn position trong form, vẫn cập nhật theo quyền để đảm bảo đồng bộ
                ClubMember::where('id', $clubMember->id)
                    ->update(['position' => $calculatedPosition]);
                
                // Log để debug
                \Log::info("Updated position for user {$clubMember->user_id} in club {$clubId}: {$clubMember->position} -> {$calculatedPosition} (permission count: {$permissionCount}, has other permissions: " . ($hasOtherPermissions ? 'true' : 'false') . ")");
            });

            return redirect()->back()->with('success', 'Đã cập nhật thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show form to create fund request
     */
    public function fundRequestCreate()
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
        $clubId = $club->id;
        $position = $user->getPositionInClub($clubId);

        // Chỉ leader mới được tạo yêu cầu
        if ($position !== 'leader') {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB mới có quyền tạo yêu cầu cấp kinh phí.');
        }

        // Lấy các sự kiện của CLB
        $events = Event::where('club_id', $clubId)
            ->orderBy('start_time', 'desc')
            ->get();

        return view('student.club-management.fund-request-create', [
            'user' => $user,
            'club' => $club,
            'events' => $events,
        ]);
    }

    /**
     * Store fund request
     */
    public function fundRequestStore(Request $request)
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
        $clubId = $club->id;
        $position = $user->getPositionInClub($clubId);

        if (!in_array($position, ['leader', 'vice_president', 'officer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền tạo yêu cầu cấp kinh phí.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requested_amount' => 'required|numeric|min:0',
            'event_id' => 'required|exists:events,id',
            'expense_items' => 'nullable|array',
            'expense_items.*.item' => 'required_with:expense_items|string|max:255',
            'expense_items.*.amount' => 'required_with:expense_items|numeric|min:0',
        ]);

        // Kiểm tra sự kiện thuộc về CLB của user
        $event = Event::findOrFail($request->event_id);
        if ($event->club_id != $clubId) {
            return redirect()->back()
                ->with('error', 'Sự kiện không thuộc về CLB của bạn.')
                ->withInput();
        }

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'requested_amount' => $request->requested_amount,
            'event_id' => $request->event_id,
            'club_id' => $clubId,
            'created_by' => $user->id,
            'status' => 'pending',
            'expense_items' => $request->expense_items ?? null,
        ];

        // Xử lý tài liệu hỗ trợ
        if ($request->hasFile('supporting_documents')) {
            $documents = [];
            $uploadPath = public_path('storage/fund-requests');
            
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            foreach ($request->file('supporting_documents') as $index => $document) {
                if (!$document || !$document->isValid() || $document->getSize() == 0) {
                    continue;
                }
                
                try {
                    $filename = time() . '_' . $index . '_' . $document->getClientOriginalName();
                    $document->move($uploadPath, $filename);
                    $documents[] = 'fund-requests/' . $filename;
                } catch (\Exception $e) {
                    continue;
                }
            }
            $data['supporting_documents'] = $documents;
        }

        try {
            FundRequest::create($data);
            return redirect()->route('student.club-management.fund-requests')
                ->with('success', 'Yêu cầu cấp kinh phí đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * List fund requests for student's club
     */
    public function fundRequests(Request $request)
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
        $clubId = $club->id;

        $query = FundRequest::with(['event', 'creator', 'approver', 'settler'])
            ->where('club_id', $clubId);

        // Filter by settlement status
        if ($request->filled('settlement') && $request->settlement === 'settled') {
            $query->where('settlement_status', 'settled');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('event', function($eventQuery) use ($search) {
                      $eventQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('student.club-management.fund-requests', [
            'user' => $user,
            'club' => $club,
            'requests' => $requests,
        ]);
    }

    /**
     * Show fund request detail
     */
    public function fundRequestShow($id)
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
        $fundRequest = FundRequest::with(['event', 'club', 'creator', 'approver', 'settler'])
            ->where('id', $id)
            ->where('club_id', $club->id)
            ->firstOrFail();

        return view('student.club-management.fund-request-show', [
            'user' => $user,
            'club' => $club,
            'fundRequest' => $fundRequest,
        ]);
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
            // Số dư = Số tiền ban đầu + Tổng thu - Tổng chi (giống logic admin)
            $balance = (int) ($fund->initial_amount ?? 0) + (int) $totalIncome - (int) $totalExpense;
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
        $position = $user->getPositionInClub($club->id);
        if (!$fund) {
            return view('student.club-management.fund-transactions', [
                'user' => $user,
                'club' => $club,
                'transactions' => collect(),
                'summary' => ['income' => 0, 'expense' => 0, 'balance' => 0],
                'filterType' => $request->input('type'),
                'position' => $position,
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
        // Số dư = Số tiền ban đầu + Tổng thu - Tổng chi (giống logic admin)
        $summary = [
            'income' => (int) $income,
            'expense' => (int) $expense,
            'balance' => (int) ($fund->initial_amount ?? 0) + (int) $income - (int) $expense,
        ];

        $position = $user->getPositionInClub($club->id);
        
        return view('student.club-management.fund-transactions', [
            'user' => $user,
            'club' => $club,
            'transactions' => $transactions,
            'summary' => $summary,
            'filterType' => $filterType,
            'position' => $position,
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
        $query = Post::with(['club', 'user', 'attachments'])
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
            'type' => 'nullable|in:post,announcement',
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
     * Show the form for creating a new post in a club's forum.
     */
    public function createClubPost(Club $club)
>>>>>>> origin/huy
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
<<<<<<< HEAD
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
            'type' => 'nullable|in:post,announcement',
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
     * Allow a student to send a join request to a club.
     */
    public function joinClub(Request $request, Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // 1. Kiểm tra xem user đã là thành viên chưa
        $isMember = $club->members()->where('user_id', $user->id)->exists();
        if ($isMember) {
            return redirect()->back()->with('error', 'Bạn đã là thành viên của câu lạc bộ này.');
        }

        // 2. Kiểm tra xem user đã có yêu cầu đang chờ xử lý chưa
        $hasPendingRequest = $club->joinRequests()->where('user_id', $user->id)->where('status', 'pending')->exists();
        if ($hasPendingRequest) {
            return redirect()->back()->with('info', 'Bạn đã gửi yêu cầu tham gia câu lạc bộ này rồi. Vui lòng chờ duyệt.');
        }

        // 3. Tạo yêu cầu tham gia mới
        ClubJoinRequest::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'message' => $request->input('message'), // Tùy chọn: có thể thêm ô lời nhắn
        ]);

        return redirect()->back()->with('success', 'Đã gửi yêu cầu tham gia thành công! Vui lòng chờ ban quản trị CLB duyệt.');
    }

    /**
     * Allow a student to cancel their join request.
     */
    public function cancelJoinRequest(Request $request, Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $joinRequest = ClubJoinRequest::where('user_id', $user->id)
            ->where('club_id', $club->id)
            ->where('status', 'pending')
            ->first();

        if (!$joinRequest) {
            return redirect()->back()->with('error', 'Không tìm thấy yêu cầu tham gia để hủy.');
        }

        $joinRequest->delete();

        return redirect()->back()->with('success', 'Đã hủy yêu cầu tham gia câu lạc bộ ' . $club->name . '.');
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
