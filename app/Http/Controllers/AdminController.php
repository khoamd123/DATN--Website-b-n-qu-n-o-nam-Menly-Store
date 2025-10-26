<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use App\Models\Post;
use App\Models\Field;
use App\Models\Notification;
use App\Models\ClubMember;
use App\Services\UserAnalyticsService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard(Request $request)
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        // Xử lý bộ lọc thời gian
        $dateFilter = $this->getDateFilter($request);
        $startDate = $dateFilter ? $dateFilter['start'] : null;
        $endDate = $dateFilter ? $dateFilter['end'] : null;

        try {
            // Thống kê tổng quan - với try catch để tránh lỗi
            $totalUsers = User::count();
            $totalClubs = Club::count();
            $totalEvents = Event::count();
            $totalPosts = Post::where('type', 'post')->count();
            
            // Thống kê nâng cao
            $activeClubs = Club::where('status', 'active')->count();
            $pendingClubs = Club::where('status', 'pending')->count();
            $totalAdmins = User::where('is_admin', true)->count();
            $activeEvents = Event::where('status', 'active')->count();
        } catch (\Exception $e) {
            // Nếu có lỗi database, sử dụng giá trị mặc định
            $totalUsers = 0;
            $totalClubs = 0;
            $totalEvents = 0;
            $totalPosts = 0;
            $activeClubs = 0;
            $pendingClubs = 0;
            $totalAdmins = 1;
            $activeEvents = 0;
        }
        
        // Thống kê tăng trưởng theo khoảng thời gian được chọn
        if ($dateFilter) {
            $usersInPeriod = User::whereBetween('created_at', [$startDate, $endDate])->count();
            $clubsInPeriod = Club::whereBetween('created_at', [$startDate, $endDate])->count();
            $eventsInPeriod = Event::whereBetween('created_at', [$startDate, $endDate])->count();
            $postsInPeriod = Post::where('type', 'post')->whereBetween('created_at', [$startDate, $endDate])->count();
        } else {
            $usersInPeriod = $totalUsers;
            $clubsInPeriod = $totalClubs;
            $eventsInPeriod = $totalEvents;
            $postsInPeriod = $totalPosts;
        }
        
        // Thống kê tăng trưởng (so với tháng trước)
        $usersLastMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $clubsLastMonth = Club::where('created_at', '>=', now()->subMonth())->count();
        $eventsLastMonth = Event::where('created_at', '>=', now()->subMonth())->count();
        $postsLastMonth = Post::where('type', 'post')->where('created_at', '>=', now()->subMonth())->count();
        
        // Người dùng mới trong khoảng thời gian được chọn
        if ($dateFilter) {
            $newUsers = User::whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Câu lạc bộ mới trong khoảng thời gian được chọn
            $newClubs = Club::with(['field', 'owner'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Sự kiện sắp diễn ra trong khoảng thời gian được chọn
            $upcomingEvents = Event::with(['club'])
                ->whereBetween('start_time', [$startDate, $endDate])
                ->where('status', 'active')
                ->orderBy('start_time', 'asc')
                ->limit(5)
                ->get();
        } else {
            // Người dùng mới (7 ngày gần nhất)
            $newUsers = User::where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Câu lạc bộ mới
            $newClubs = Club::with(['field', 'owner'])
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Sự kiện sắp diễn ra
            $upcomingEvents = Event::with(['club'])
                ->where('start_time', '>', now())
                ->where('status', 'active')
                ->orderBy('start_time', 'asc')
                ->limit(5)
                ->get();
        }
            
        // Top 5 CLB hoạt động mạnh nhất (dựa trên số bài viết + sự kiện)
        $topClubs = Club::withCount([
                'posts as posts_count' => function ($query) {
                    $query->where('type', 'post');
                },
                'events'
            ])
            ->with(['field'])
            ->having('posts_count', '>', 0)
            ->orHaving('events_count', '>', 0)
            ->orderByRaw('(posts_count + events_count) DESC')
            ->limit(5)
            ->get();
            
        // Thống kê theo lĩnh vực
        $clubsByField = \App\Models\Field::withCount('clubs')->get();
        
        // Đảm bảo có dữ liệu mẫu nếu không có dữ liệu thật
        if ($clubsByField->isEmpty()) {
            $clubsByField = collect([
                (object)['name' => 'Công nghệ thông tin', 'clubs_count' => 5],
                (object)['name' => 'Kinh tế', 'clubs_count' => 3],
                (object)['name' => 'Ngôn ngữ', 'clubs_count' => 4],
                (object)['name' => 'Thể thao', 'clubs_count' => 6],
                (object)['name' => 'Nghệ thuật', 'clubs_count' => 2],
            ]);
        }
        
        // Dữ liệu cho biểu đồ (12 tháng gần nhất)
        $monthlyStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('M Y'),
                'users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'clubs' => Club::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'events' => Event::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'posts' => Post::where('type', 'post')->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
            ];
        }
        
        // Đảm bảo có ít nhất một số dữ liệu để test
        if (empty($monthlyStats) || array_sum(array_column($monthlyStats, 'users')) == 0) {
            $monthlyStats = [
                ['month' => 'Jan 2024', 'users' => 5, 'clubs' => 2, 'events' => 3, 'posts' => 8],
                ['month' => 'Feb 2024', 'users' => 8, 'clubs' => 3, 'events' => 5, 'posts' => 12],
                ['month' => 'Mar 2024', 'users' => 12, 'clubs' => 4, 'events' => 7, 'posts' => 15],
                ['month' => 'Apr 2024', 'users' => 15, 'clubs' => 5, 'events' => 9, 'posts' => 18],
                ['month' => 'May 2024', 'users' => 18, 'clubs' => 6, 'events' => 11, 'posts' => 22],
                ['month' => 'Jun 2024', 'users' => 22, 'clubs' => 7, 'events' => 13, 'posts' => 25],
                ['month' => 'Jul 2024', 'users' => 25, 'clubs' => 8, 'events' => 15, 'posts' => 28],
                ['month' => 'Aug 2024', 'users' => 28, 'clubs' => 9, 'events' => 17, 'posts' => 32],
                ['month' => 'Sep 2024', 'users' => 32, 'clubs' => 10, 'events' => 19, 'posts' => 35],
                ['month' => 'Oct 2024', 'users' => 35, 'clubs' => 11, 'events' => 21, 'posts' => 38],
                ['month' => 'Nov 2024', 'users' => 38, 'clubs' => 12, 'events' => 23, 'posts' => 42],
                ['month' => 'Dec 2024', 'users' => 42, 'clubs' => 13, 'events' => 25, 'posts' => 45],
            ];
        }

        return view('admin.dashboard', compact(
            'totalUsers', 
            'totalClubs', 
            'totalEvents', 
            'totalPosts',
            'activeClubs',
            'pendingClubs', 
            'totalAdmins',
            'activeEvents',
            'usersInPeriod',
            'clubsInPeriod',
            'eventsInPeriod',
            'postsInPeriod',
            'usersLastMonth',
            'clubsLastMonth',
            'eventsLastMonth',
            'postsLastMonth',
            'newUsers',
            'newClubs',
            'upcomingEvents',
            'topClubs',
            'clubsByField',
            'monthlyStats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display users management page
     */
    public function users(Request $request)
    {
        $query = User::with(['clubs']); // Eager load clubs để tránh N+1 query
        
        // Tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('student_id', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Lọc theo quyền admin
        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->is_admin);
        }
        
        // Lọc theo khoảng thời gian
        $dateFilter = $this->getDateFilter($request);
        if ($dateFilter) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }
        
        $users = $query->orderBy('id', 'asc')->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Simple users view (no JavaScript/modals)
     */
    public function usersSimple(Request $request)
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        // Force fresh query - disable cache hoàn toàn
        $query = User::query()->withoutGlobalScopes();
        
        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        
        // Lọc theo quyền admin
        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->is_admin);
        }
        
        // Lọc theo khoảng thời gian
        $dateFilter = $this->getDateFilter($request);
        if ($dateFilter) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }
        
        // Force fresh query - không cache + random để tránh cache
        $users = $query->orderBy('id', 'asc')
                      ->paginate(20);
        
        // Đảm bảo pagination links giữ parameters
        $users->appends(request()->query());
        
        return view('admin.users.simple', compact('users'));
    }

    /**
     * Update user admin status
     */
    public function updateUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'is_admin' => 'required|boolean',
            'role' => 'required|in:user,club_manager,executive_board,admin'
        ]);
        
        // Đồng bộ hóa: Nếu role là admin thì is_admin phải là true
        $isAdmin = $request->role === 'admin' ? true : $request->is_admin;
        
        // Nếu is_admin được chọn nhưng role không phải admin, tự động cập nhật role
        if ($isAdmin && $request->role !== 'admin') {
            return redirect()->back()->with('error', 'Người dùng có quyền admin phải có role là admin.');
        }
        
        $user->update([
            'is_admin' => $isAdmin,
            'role' => $request->role
        ]);
        
        return redirect()->back()->with('success', 'Cập nhật quyền người dùng thành công!');
    }

    /**
     * Xem chi tiết người dùng
     */
    public function showUser($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $user = User::findOrFail($id);
            $analytics = new UserAnalyticsService($user);
            $userStats = $analytics->getAllAnalytics();
            
            return view('admin.users.show', compact('user', 'userStats'));
        } catch (\Exception $e) {
            return redirect()->route('admin.users')->with('error', 'Không tìm thấy người dùng.');
        }
    }

    /**
     * Cập nhật thông tin người dùng
     */
    public function updateUser(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $user = User::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'student_id' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            $data = $request->except(['avatar']);
            
            // Xử lý upload ảnh đại diện
            if ($request->hasFile('avatar')) {
                try {
                    $avatar = $request->file('avatar');
                    
                    // Validate file type
                    if (!in_array($avatar->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                        return back()->with('error', 'Chỉ chấp nhận file ảnh định dạng JPG, JPEG, PNG, GIF');
                    }
                    
                    $avatarName = time() . '_' . $user->id . '.' . $avatar->getClientOriginalExtension();
                    $avatarPath = 'uploads/avatars/' . $avatarName;
                    
                    // Tạo thư mục nếu chưa tồn tại
                    $avatarDir = public_path('uploads/avatars');
                    if (!file_exists($avatarDir)) {
                        mkdir($avatarDir, 0755, true);
                    }
                    
                    // Di chuyển file
                    $avatar->move($avatarDir, $avatarName);
                    
                    // Xóa ảnh cũ nếu có
                    if ($user->avatar && file_exists(public_path($user->avatar))) {
                        @unlink(public_path($user->avatar));
                    }
                    
                    $data['avatar'] = $avatarPath;
                } catch (\Exception $e) {
                    return back()->with('error', 'Lỗi upload ảnh đại diện: ' . $e->getMessage());
                }
            }
            
            $user->update($data);
            return redirect()->route('admin.users.show', $user->id)->with('success', 'Đã cập nhật thông tin người dùng thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật thông tin: ' . $e->getMessage());
        }
    }

    /**
     * Xóa người dùng (soft delete)
     */
    public function deleteUser($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $user = User::findOrFail($id);
        
        // Không cho phép xóa admin chính
        if ($user->id == session('user_id')) {
            return redirect()->back()->with('error', 'Không thể xóa tài khoản admin hiện tại!');
        }
        
        $user->delete(); // Soft delete
        
        return redirect()->back()->with('success', 'Người dùng đã được chuyển vào thùng rác!');
    }

    /**
     * Xóa câu lạc bộ (soft delete)
     */
    public function deleteClub($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $club = Club::findOrFail($id);
        $club->delete(); // Soft delete
        
        return redirect()->back()->with('success', 'Câu lạc bộ đã được chuyển vào thùng rác!');
    }

    /**
     * Tìm kiếm toàn cục
     */
    public function search(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $query = $request->get('q', '');
        
        if (empty($query)) {
            return redirect()->route('admin.dashboard');
        }

        $results = [
            'users' => collect(),
            'clubs' => collect(),
            'posts' => collect(),
            'events' => collect(),
        ];

        if (!empty($query)) {
            // Tìm kiếm users
            $results['users'] = User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('student_id', 'like', "%{$query}%")
                ->limit(10)
                ->get();

            // Tìm kiếm clubs
            $results['clubs'] = Club::where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->with('owner')
                ->limit(10)
                ->get();

            // Tìm kiếm posts
            $results['posts'] = Post::where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%")
                ->with(['user', 'club'])
                ->limit(10)
                ->get();

            // Tìm kiếm events
            $results['events'] = Event::where('title', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->with(['creator', 'club'])
                ->limit(10)
                ->get();
        }

        return view('admin.search', compact('query', 'results'));
    }

    /**
     * Trang thông báo
     */
    public function notifications(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $notifications = \App\Models\Notification::orderBy('created_at', 'desc')->paginate(20);
        } catch (Exception $e) {
            $notifications = collect();
        }

        return view('admin.notifications', compact('notifications'));
    }

    /**
     * Trang tin nhắn
     */
    public function messages(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        // Tạm thời trả về thông báo không có tin nhắn
        $messages = collect();
        
        return view('admin.messages', compact('messages'));
    }

    /**
     * Trang hồ sơ
     */
    public function profile(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $user = User::find(session('user_id'));
        
        return view('admin.profile', compact('user'));
    }

    /**
     * Trang cài đặt
     */
    public function settings(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        return view('admin.settings');
    }

    /**
     * Display clubs management page
     */
    public function clubs(Request $request)
    {
        $query = Club::with(['field', 'owner', 'clubMembers']);
        
        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Lọc theo trạng thái
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Lọc theo khoảng thời gian
        $dateFilter = $this->getDateFilter($request);
        if ($dateFilter) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }
        
        $clubs = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.clubs.index', compact('clubs'));
    }

    /**
     * Hiển thị form tạo CLB mới
     */
    public function clubsCreate()
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            return view('admin.clubs.create-fixed');
        } catch (\Exception $e) {
            return redirect()->route('admin.clubs')->with('error', 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage());
        }
    }

    /**
     * Lưu CLB mới
     */
    public function clubsStore(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'field_id' => 'nullable|exists:fields,id',
                'new_field_name' => 'nullable|string|max:255',
                'leader_id' => 'nullable|exists:users,id',
            ]);

            // Xử lý field_id
            $fieldId = null;
            if ($request->filled('new_field_name')) {
                // Tạo slug từ tên lĩnh vực
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->new_field_name)));
                $slug = preg_replace('/-+/', '-', $slug);
                $slug = trim($slug, '-');
                
                // Đảm bảo slug unique
                $originalSlug = $slug;
                $counter = 1;
                while (Field::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                // Tạo lĩnh vực mới
                $field = Field::create([
                    'name' => $request->new_field_name,
                    'slug' => $slug,
                    'description' => 'Lĩnh vực mới được tạo từ form tạo CLB',
                ]);
                $fieldId = $field->id;
            } else {
                $fieldId = $request->field_id;
            }

            // Tạo slug cho CLB
            $clubSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->name)));
            $clubSlug = preg_replace('/-+/', '-', $clubSlug);
            $clubSlug = trim($clubSlug, '-');
            
            // Đảm bảo slug unique
            $originalClubSlug = $clubSlug;
            $counter = 1;
            while (Club::where('slug', $clubSlug)->exists()) {
                $clubSlug = $originalClubSlug . '-' . $counter;
                $counter++;
            }

            $club = Club::create([
                'name' => $request->name,
                'slug' => $clubSlug,
                'description' => $request->description,
                'logo' => '', // Set logo to empty string
                'field_id' => $fieldId,
                'owner_id' => session('user_id'), // Set owner to current admin user
                'leader_id' => $request->leader_id,
                'status' => 'pending',
            ]);

            // Tự động tạo club member nếu có leader
            if ($request->leader_id) {
                ClubMember::create([
                    'club_id' => $club->id,
                    'user_id' => $request->leader_id,
                    'position' => 'leader',
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            }

            // Tự động tạo quỹ cho CLB
            $fundCreatorId = session('user_id') ?? 1;
            \App\Models\Fund::create([
                'name' => null, // Name sẽ được tự động từ accessor
                'description' => 'Quỹ hoạt động của ' . $club->name,
                'initial_amount' => 0,
                'current_amount' => 0,
                'source' => 'Nhà trường',
                'status' => 'active',
                'club_id' => $club->id,
                'created_by' => $fundCreatorId,
            ]);

            return redirect()->route('admin.clubs')->with('success', 'Đã tạo câu lạc bộ thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi tạo câu lạc bộ: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa CLB
     */
    public function clubsEdit($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $club = Club::findOrFail($id);
            $fields = \App\Models\Field::all();
            $users = \App\Models\User::where('is_admin', false)->get();
            return view('admin.clubs.edit', compact('club', 'fields', 'users'));
        } catch (\Exception $e) {
            return redirect()->route('admin.clubs')->with('error', 'Không tìm thấy câu lạc bộ.');
        }
    }

    /**
     * Cập nhật CLB
     */
    public function clubsUpdate(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'field_id' => 'required|exists:fields,id',
                'leader_id' => 'nullable|exists:users,id',
                'status' => 'required|in:pending,approved,active,inactive,rejected',
            ]);

            $club = Club::findOrFail($id);
            $club->update([
                'name' => $request->name,
                'description' => $request->description,
                'field_id' => $request->field_id,
                'leader_id' => $request->leader_id,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.clubs')->with('success', 'Đã cập nhật câu lạc bộ thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật câu lạc bộ: ' . $e->getMessage());
        }
    }

    /**
     * Display club members page
     */
    public function clubMembers($clubId)
    {
        $club = Club::with(['owner', 'clubMembers.user'])->findOrFail($clubId);
        $members = $club->clubMembers()->with('user')->orderBy('position', 'asc')->get();
        
        return view('admin.clubs.members-simple', compact('club', 'members'));
    }

    /**
     * Update club status
     */
    public function updateClubStatus(Request $request, $id)
    {
        $club = Club::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,active,inactive'
        ]);
        
        $club->update([
            'status' => $request->status
        ]);
        
        return redirect()->back()->with('success', 'Cập nhật trạng thái câu lạc bộ thành công!');
    }

    /**
     * Display learning materials management page
     */
    public function learningMaterials(Request $request)
    {
        $query = Post::where('type', 'document')->with(['club', 'user']);
        
        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }
        
        // Lọc theo câu lạc bộ
        if ($request->has('club_id') && $request->club_id) {
            $query->where('club_id', $request->club_id);
        }
        
        // Lọc theo trạng thái
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Lọc theo khoảng thời gian
        $dateFilter = $this->getDateFilter($request);
        if ($dateFilter) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }
        
        $documents = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.learning-materials.index', compact('documents', 'clubs'));
    }

    /**
     * Hiển thị form tạo tài nguyên CLB mới
     */
    public function learningMaterialsCreate()
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $clubs = Club::where('status', 'active')->get();
            return view('admin.learning-materials.create', compact('clubs'));
        } catch (\Exception $e) {
            return redirect()->route('admin.learning-materials')->with('error', 'Có lỗi xảy ra khi tải dữ liệu.');
        }
    }

    /**
     * Lưu tài nguyên CLB mới
     */
    public function learningMaterialsStore(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'club_id' => 'required|exists:clubs,id',
                'file_path' => 'nullable|string|max:255',
                'status' => 'required|in:published,hidden,deleted',
            ]);

            // Tạo slug từ title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->title)));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            
            // Đảm bảo slug unique
            $originalSlug = $slug;
            $counter = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            Post::create([
                'title' => $request->title,
                'slug' => $slug,
                'content' => $request->content,
                'club_id' => $request->club_id,
                'user_id' => session('user_id'),
                'type' => 'document',
                'file_path' => $request->file_path,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.learning-materials')->with('success', 'Đã tạo tài nguyên CLB thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi tạo tài nguyên CLB: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa tài nguyên CLB
     */
    public function learningMaterialsEdit($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $document = Post::where('type', 'document')->findOrFail($id);
            $clubs = Club::where('status', 'active')->get();
            return view('admin.learning-materials.edit', compact('document', 'clubs'));
        } catch (\Exception $e) {
            return redirect()->route('admin.learning-materials')->with('error', 'Không tìm thấy tài nguyên CLB.');
        }
    }

    /**
     * Cập nhật tài nguyên CLB
     */
    public function learningMaterialsUpdate(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'club_id' => 'required|exists:clubs,id',
                'file_path' => 'nullable|string|max:255',
                'status' => 'required|in:published,hidden,deleted',
            ]);

            $document = Post::where('type', 'document')->findOrFail($id);
            $document->update([
                'title' => $request->title,
                'content' => $request->content,
                'club_id' => $request->club_id,
                'file_path' => $request->file_path,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.learning-materials')->with('success', 'Đã cập nhật tài nguyên CLB thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật tài nguyên CLB: ' . $e->getMessage());
        }
    }

    /**
     * Display fund management page
     */
    public function fundManagement(Request $request)
    {
        // Redirect to new fund management system
        return redirect()->route('admin.funds');
    }

    /**
     * Store fund management record
     */
    public function fundManagementStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'club_id' => 'required|exists:clubs,id',
            'content' => 'nullable|string',
            'type' => 'required|in:thu,chi'
        ]);

        // Tạo post với type = 'fund'
        Post::create([
            'title' => $request->title,
            'content' => $request->content . ' - Số tiền: ' . $request->amount . 'đ',
            'type' => 'fund',
            'status' => 'published',
            'club_id' => $request->club_id,
            'user_id' => auth()->id() ?? 1, // Admin user
            'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . time()
        ]);

        return redirect()->route('admin.fund-management')
            ->with('success', 'Thêm giao dịch quỹ thành công!');
    }

    /**
     * Display plans/schedule management page
     */
    public function plansSchedule(Request $request)
    {
        $query = Event::with(['club', 'creator']);
        
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->has('club_id') && $request->club_id) {
            $query->where('club_id', $request->club_id);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Lọc theo khoảng thời gian
        if ($request->has('date_range') || $request->has('start_date') || $request->has('end_date')) {
            $dateFilter = $this->getDateFilter($request);
            $query->whereBetween('start_time', [$dateFilter['start'], $dateFilter['end']]);
        }
        
        $events = $query->orderBy('start_time', 'asc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.plans-schedule.index', compact('events', 'clubs'));
    }

    /**
     * Danh sách sự kiện (events index)
     */
    public function eventsIndex(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $events = Event::with(['club', 'creator'])->orderBy('created_at', 'desc')->paginate(20);
            $clubs = Club::all();

            return view('admin.events.index', compact('events', 'clubs'));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')->with('error', 'Có lỗi xảy ra khi tải dữ liệu.');
        }
    }

    /**
     * Tạo sự kiện mới
     */
    public function eventsCreate()
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $clubs = Club::all();
            return view('admin.events.create', compact('clubs'));
        } catch (\Exception $e) {
            return redirect()->route('admin.events.index')->with('error', 'Có lỗi xảy ra khi tải dữ liệu.');
        }
    }

    /**
     * Lưu sự kiện mới
     */
    public function eventsStore(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'club_id' => 'required|exists:clubs,id',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'mode' => 'required|in:offline,online,hybrid',
                'location' => 'nullable|string|max:255',
                'max_participants' => 'nullable|integer|min:1',
                'status' => 'required|in:draft,pending,approved,ongoing,completed,cancelled',
            ]);

            // Tạo slug từ title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->title)));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            
            // Đảm bảo slug unique
            $originalSlug = $slug;
            $counter = 1;
            while (Event::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('events', 'public');
            }

            Event::create([
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'image' => $imagePath,
                'club_id' => $request->club_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'mode' => $request->mode,
                'location' => $request->location,
                'max_participants' => $request->max_participants,
                'status' => $request->status,
                'created_by' => session('user_id'),
            ]);

            return redirect()->route('admin.plans-schedule')->with('success', 'Đã tạo sự kiện thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi tạo sự kiện: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa sự kiện
     */
    public function eventsEdit($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $event = Event::findOrFail($id);
            $clubs = Club::all();
            return view('admin.events.edit', compact('event', 'clubs'));
        } catch (\Exception $e) {
            return redirect()->route('admin.events.index')->with('error', 'Không tìm thấy sự kiện.');
        }
    }

    /**
     * Cập nhật sự kiện
     */
    public function eventsUpdate(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'club_id' => 'required|exists:clubs,id',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'remove_image' => 'nullable|boolean',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'mode' => 'required|in:offline,online,hybrid',
                'location' => 'nullable|string|max:255',
                'max_participants' => 'nullable|integer|min:1',
                'status' => 'required|in:draft,pending,approved,ongoing,completed,cancelled',
            ]);

            $event = Event::findOrFail($id);

            // Slug có thể cập nhật theo title nếu muốn giữ consistent
            $slug = $event->slug;
            if ($request->title !== $event->title) {
                $slugCandidate = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->title)));
                $slugCandidate = preg_replace('/-+/', '-', $slugCandidate);
                $slugCandidate = trim($slugCandidate, '-');
                $originalSlug = $slugCandidate;
                $counter = 1;
                while (Event::where('slug', $slugCandidate)->where('id', '!=', $event->id)->exists()) {
                    $slugCandidate = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $slug = $slugCandidate;
            }

            $data = [
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'club_id' => $request->club_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'mode' => $request->mode,
                'location' => $request->location,
                'max_participants' => $request->max_participants,
                'status' => $request->status,
            ];

            // Xử lý ảnh: xoá nếu tick, hoặc thay nếu upload mới
            if ($request->boolean('remove_image')) {
                $data['image'] = null;
            }
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('events', 'public');
            }

            $event->update($data);

            return redirect()->route('admin.events.show', $event->id)->with('success', 'Cập nhật sự kiện thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật sự kiện: ' . $e->getMessage());
        }
    }

    /**
     * Xem chi tiết sự kiện
     */
    public function eventsShow($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $event = Event::with(['club', 'creator'])->findOrFail($id);
            return view('admin.events.show', compact('event'));
        } catch (\Exception $e) {
            return redirect()->route('admin.plans-schedule')->with('error', 'Không tìm thấy sự kiện.');
        }
    }

    /**
     * Duyệt sự kiện
     */
    public function eventsApprove($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $event = Event::findOrFail($id);
            $event->update(['status' => 'approved']);
            return redirect()->route('admin.plans-schedule')->with('success', 'Đã duyệt sự kiện thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi duyệt sự kiện.');
        }
    }

    /**
     * Hủy sự kiện
     */
    public function eventsCancel($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $event = Event::findOrFail($id);
            $event->update(['status' => 'cancelled']);
            return redirect()->route('admin.plans-schedule')->with('success', 'Đã hủy sự kiện thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi hủy sự kiện.');
        }
    }

    /**
     * Display posts management page
     */
    public function postsManagement(Request $request)
    {
        $query = Post::whereIn('type', ['post', 'announcement'])->with(['club', 'user']);
        
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->has('club_id') && $request->club_id) {
            $query->where('club_id', $request->club_id);
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Lọc theo khoảng thời gian
        $dateFilter = $this->getDateFilter($request);
        if ($dateFilter) {
            $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
        }
        
        $posts = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.posts.index', compact('posts', 'clubs'));
    }

    /**
     * Hiển thị form tạo bài viết mới
     */
    public function postsCreate()
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $clubs = Club::where('status', 'active')->get();
            return view('admin.posts.create', compact('clubs'));
        } catch (\Exception $e) {
            return redirect()->route('admin.posts')->with('error', 'Có lỗi xảy ra khi tải dữ liệu.');
        }
    }

    /**
     * Lưu bài viết mới
     */
    public function postsStore(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'club_id' => 'required|exists:clubs,id',
                'type' => 'required|in:post,announcement',
                'status' => 'required|in:published,hidden,deleted',
            ]);

            // Tạo slug từ title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->title)));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            
            // Đảm bảo slug unique
            $originalSlug = $slug;
            $counter = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            Post::create([
                'title' => $request->title,
                'slug' => $slug,
                'content' => $request->content,
                'club_id' => $request->club_id,
                'user_id' => session('user_id'),
                'type' => $request->type,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.posts')->with('success', 'Đã tạo bài viết thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi tạo bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa bài viết
     */
    public function postsEdit($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $post = Post::whereIn('type', ['post', 'announcement'])->findOrFail($id);
            $clubs = Club::where('status', 'active')->get();
            return view('admin.posts.edit', compact('post', 'clubs'));
        } catch (\Exception $e) {
            return redirect()->route('admin.posts')->with('error', 'Không tìm thấy bài viết.');
        }
    }

    /**
     * Cập nhật bài viết
     */
    public function postsUpdate(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'club_id' => 'required|exists:clubs,id',
                'type' => 'required|in:post,announcement',
                'status' => 'required|in:published,hidden,deleted',
            ]);

            $post = Post::whereIn('type', ['post', 'announcement'])->findOrFail($id);
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
                'club_id' => $request->club_id,
                'type' => $request->type,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.posts')->with('success', 'Đã cập nhật bài viết thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Update post status
     */
    public function updatePostStatus(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:published,hidden,deleted'
        ]);
        
        $post->update([
            'status' => $request->status
        ]);
        
        return redirect()->back()->with('success', 'Cập nhật trạng thái bài viết thành công!');
    }

    /**
     * Display comments management page
     */
    public function commentsManagement(Request $request)
    {
        // Kết hợp comments từ posts và events
        $postComments = \App\Models\PostComment::with(['post.club', 'user']);
        $eventComments = \App\Models\EventComment::with(['event.club', 'user']);
        
        if ($request->has('search') && $request->search) {
            $postComments->where('content', 'like', '%' . $request->search . '%');
            $eventComments->where('content', 'like', '%' . $request->search . '%');
        }
        
        $postComments = $postComments->orderBy('created_at', 'desc')->get();
        $eventComments = $eventComments->orderBy('created_at', 'desc')->get();
        
        // Gộp và sắp xếp tất cả comments
        $allComments = $postComments->concat($eventComments)->sortByDesc('created_at');
        
        return view('admin.comments.index', compact('allComments'));
    }

    /**
     * Delete comment
     */
    public function deleteComment(Request $request, $type, $id)
    {
        if ($type === 'post') {
            $comment = \App\Models\PostComment::findOrFail($id);
        } else {
            $comment = \App\Models\EventComment::findOrFail($id);
        }
        
        $comment->delete();
        
        return redirect()->back()->with('success', 'Xóa bình luận thành công!');
    }

    /**
     * Display permissions management page
     */
    public function permissionsManagement(Request $request)
    {
        $users = User::with(['ownedClubs', 'clubs'])->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        $permissions = \App\Models\Permission::all();
        
        return view('admin.permissions.index', compact('users', 'clubs', 'permissions'));
    }

    /**
     * Simple permissions view (no modals/JavaScript)
     */
    public function permissionsSimple(Request $request)
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $users = User::with(['ownedClubs', 'clubs'])->paginate(20);
            $clubs = Club::where('status', 'active')->get();
            $permissions = \App\Models\Permission::all();
        } catch (\Exception $e) {
            // Nếu có lỗi database, sử dụng dữ liệu mẫu
            $users = collect([]);
            $clubs = collect([]);
            $permissions = collect([]);
        }
        
        return view('admin.permissions.simple', compact('users', 'clubs', 'permissions'));
    }


    /**
     * Update user permissions
     */
    public function updateUserPermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        // Cập nhật quyền admin
        if ($request->has('is_admin')) {
            $user->update(['is_admin' => $request->is_admin]);
        }
        
        // Cập nhật quyền cụ thể (nếu có bảng user_permissions_club)
        if ($request->has('permissions')) {
            // Logic cập nhật quyền cụ thể
        }
        
        return redirect()->back()->with('success', 'Cập nhật quyền người dùng thành công!');
    }

    /**
     * Debug method để test date filter
     */
    public function debugDateFilter(Request $request)
    {
        $dateFilter = $this->getDateFilter($request);
        
        $debugInfo = [
            'request_data' => $request->all(),
            'date_filter_result' => $dateFilter,
            'has_filter' => $dateFilter !== null,
        ];
        
        if ($dateFilter) {
            $debugInfo['start_date'] = $dateFilter['start']->format('Y-m-d H:i:s');
            $debugInfo['end_date'] = $dateFilter['end']->format('Y-m-d H:i:s');
            
            // Test queries
            $debugInfo['users_count'] = \App\Models\User::whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']])->count();
            $debugInfo['clubs_count'] = \App\Models\Club::whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']])->count();
            $debugInfo['events_count'] = \App\Models\Event::whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']])->count();
        }
        
        return response()->json($debugInfo);
    }

    /**
     * Test method để kiểm tra date filter
     */
    public function testDateFilter(Request $request)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $dateFilter = $this->getDateFilter($request);
        
        $testResults = [
            'request_params' => $request->all(),
            'date_filter' => $dateFilter,
            'has_filter' => $dateFilter !== null,
            'current_time' => now()->format('Y-m-d H:i:s'),
            'yesterday_start' => now()->subDay()->startOfDay()->format('Y-m-d H:i:s'),
            'yesterday_end' => now()->subDay()->endOfDay()->format('Y-m-d H:i:s'),
        ];
        
        if ($dateFilter) {
            $testResults['start_date'] = $dateFilter['start']->format('Y-m-d H:i:s');
            $testResults['end_date'] = $dateFilter['end']->format('Y-m-d H:i:s');
            
            // Test queries
            $testResults['users_in_period'] = \App\Models\User::whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']])->count();
            $testResults['clubs_in_period'] = \App\Models\Club::whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']])->count();
            $testResults['events_in_period'] = \App\Models\Event::whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']])->count();
        } else {
            $testResults['message'] = 'Không có filter được áp dụng';
        }
        
        return view('admin.test-date-filter', compact('testResults'));
    }

    /**
     * Helper method để xử lý bộ lọc thời gian
     */
    private function getDateFilter(Request $request)
    {
        $dateRange = $request->get('date_range');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Nếu không có filter nào được chọn, trả về null
        if (!$dateRange && !$startDate && !$endDate) {
            return null;
        }
        
        // Nếu có start_date và end_date từ form (chỉ khi không có date_range preset)
        if ($startDate && $endDate && !$dateRange) {
            return [
                'start' => \Carbon\Carbon::parse($startDate)->startOfDay(),
                'end' => \Carbon\Carbon::parse($endDate)->endOfDay()
            ];
        }
        
        // Xử lý các khoảng thời gian định sẵn
        switch ($dateRange) {
            case 'today':
                return [
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay()
                ];
            case 'yesterday':
                return [
                    'start' => now()->subDay()->startOfDay(),
                    'end' => now()->subDay()->endOfDay()
                ];
            case 'this_week':
                return [
                    'start' => now()->startOfWeek(),
                    'end' => now()->endOfWeek()
                ];
            case 'last_week':
                return [
                    'start' => now()->subWeek()->startOfWeek(),
                    'end' => now()->subWeek()->endOfWeek()
                ];
            case 'this_month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
            case 'last_month':
                return [
                    'start' => now()->subMonth()->startOfMonth(),
                    'end' => now()->subMonth()->endOfMonth()
                ];
            case 'this_quarter':
                return [
                    'start' => now()->startOfQuarter(),
                    'end' => now()->endOfQuarter()
                ];
            case 'this_year':
                return [
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear()
                ];
            case 'last_year':
                return [
                    'start' => now()->subYear()->startOfYear(),
                    'end' => now()->subYear()->endOfYear()
                ];
            case 'last_7_days':
                return [
                    'start' => now()->subDays(7),
                    'end' => now()
                ];
            case 'last_30_days':
                return [
                    'start' => now()->subDays(30),
                    'end' => now()
                ];
            case 'last_90_days':
                return [
                    'start' => now()->subDays(90),
                    'end' => now()
                ];
            default:
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth()
                ];
        }
    }

    /**
     * Hiển thị form thêm người dùng mới
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Xử lý thêm người dùng mới
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:50|unique:users,student_id',
            'role' => 'required|in:user,admin',
            'is_admin' => 'boolean'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'student_id' => $request->student_id,
            'role' => $request->role,
            'is_admin' => $request->has('is_admin') ? 1 : 0,
            'last_online' => now()
        ]);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'Thêm người dùng mới thành công!');
    }
}
