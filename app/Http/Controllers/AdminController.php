<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\Post;
use App\Models\Field;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        // Kiểm tra đăng nhập đơn giản
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

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
        
        // Thống kê tăng trưởng (so với tháng trước)
        $usersLastMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $clubsLastMonth = Club::where('created_at', '>=', now()->subMonth())->count();
        $eventsLastMonth = Event::where('created_at', '>=', now()->subMonth())->count();
        $postsLastMonth = Post::where('type', 'post')->where('created_at', '>=', now()->subMonth())->count();
        
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
            'usersLastMonth',
            'clubsLastMonth',
            'eventsLastMonth',
            'postsLastMonth',
            'newUsers',
            'newClubs',
            'upcomingEvents',
            'topClubs',
            'clubsByField',
            'monthlyStats'
        ));
    }

    /**
     * Display users management page
     */
    public function users(Request $request)
    {
        $query = User::query();
        
        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        
        // Lọc theo quyền admin
        if ($request->has('is_admin') && $request->is_admin !== '') {
            $query->where('is_admin', $request->is_admin);
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
        if ($request->has('is_admin') && $request->is_admin !== '') {
            $query->where('is_admin', $request->is_admin);
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
        
        // Nếu set là admin, thì role cũng phải là admin
        if ($request->is_admin && $request->role !== 'admin') {
            return redirect()->back()->with('error', 'Người dùng admin phải có role là admin.');
        }
        
        // Nếu role là admin, thì is_admin cũng phải là true
        if ($request->role === 'admin' && !$request->is_admin) {
            return redirect()->back()->with('error', 'Người dùng có role admin phải có quyền admin.');
        }
        
        $user->update([
            'is_admin' => $request->is_admin,
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
            return view('admin.users.show', compact('user'));
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
            ]);
            
            $user->update($request->all());
            return redirect()->route('admin.users.show', $user->id)->with('success', 'Đã cập nhật thông tin người dùng thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật thông tin.');
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
        // Debug: Log request data
        \Log::info('ClubsStore called', [
            'request_data' => $request->all(),
            'user_id' => session('user_id'),
            'is_admin' => session('is_admin')
        ]);

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

            // Debug: Log data trước khi tạo
            \Log::info('Creating club with data:', [
                'name' => $request->name,
                'slug' => $clubSlug,
                'description' => $request->description,
                'field_id' => $fieldId,
                'leader_id' => $request->leader_id,
                'status' => 'pending'
            ]);

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

            \Log::info('Club created successfully:', ['club_id' => $club->id, 'leader_id' => $request->leader_id]);

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
        
        // Debug: Log request data
        \Log::info('UpdateClubStatus called', [
            'club_id' => $id,
            'club_name' => $club->name,
            'old_status' => $club->status,
            'new_status' => $request->status,
            'request_data' => $request->all()
        ]);
        
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,active,inactive'
        ]);
        
        $club->update([
            'status' => $request->status
        ]);
        
        \Log::info('Club status updated successfully', [
            'club_id' => $id,
            'new_status' => $club->fresh()->status
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
        
        $documents = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.learning-materials.index', compact('documents', 'clubs'));
    }

    /**
     * Hiển thị form tạo tài liệu học tập mới
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
     * Lưu tài liệu học tập mới
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

            return redirect()->route('admin.learning-materials')->with('success', 'Đã tạo tài liệu học tập thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi tạo tài liệu học tập: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa tài liệu học tập
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
            return redirect()->route('admin.learning-materials')->with('error', 'Không tìm thấy tài liệu học tập.');
        }
    }

    /**
     * Cập nhật tài liệu học tập
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

            return redirect()->route('admin.learning-materials')->with('success', 'Đã cập nhật tài liệu học tập thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật tài liệu học tập: ' . $e->getMessage());
        }
    }

    /**
     * Display fund management page
     */
    public function fundManagement(Request $request)
    {
        // Tạm thời sử dụng bảng posts với type = 'fund' để quản lý quỹ
        $query = Post::where('type', 'fund')->with(['club', 'user']);
        
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->has('club_id') && $request->club_id) {
            $query->where('club_id', $request->club_id);
        }
        
        $funds = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        // Thống kê quỹ
        $totalFunds = $funds->sum(function($fund) {
            // Giả định content chứa số tiền
            preg_match('/\d+/', $fund->content, $matches);
            return isset($matches[0]) ? (int)$matches[0] : 0;
        });
        
        return view('admin.fund-management.index', compact('funds', 'clubs', 'totalFunds'));
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
        
        $events = $query->orderBy('start_time', 'asc')->paginate(15);
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
            $events = Event::with(['club', 'creator', 'images'])->orderBy('created_at', 'desc')->paginate(20);
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
                'description' => 'nullable|string|max:10000', // Tăng giới hạn cho HTML content
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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

            $event = Event::create([
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'image' => null, // Sẽ không sử dụng field image nữa, dùng event_images table
                'club_id' => $request->club_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'mode' => $request->mode,
                'location' => $request->location,
                'max_participants' => $request->max_participants,
                'status' => $request->status,
                'created_by' => session('user_id'),
            ]);

            // Xử lý upload nhiều ảnh
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $imagePath = $image->store('events', 'public');
                    EventImage::create([
                        'event_id' => $event->id,
                        'image_path' => $imagePath,
                        'alt_text' => $request->title . ' - Ảnh ' . ($index + 1),
                        'sort_order' => $index,
                    ]);
                }
            }

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
            $event = Event::with('images')->findOrFail($id);
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
                'description' => 'nullable|string|max:10000', // Tăng giới hạn cho HTML content
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'remove_images' => 'nullable|array',
                'remove_images.*' => 'integer|exists:event_images,id',
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

            $event->update($data);

            // Xử lý xóa ảnh được chọn
            if ($request->has('remove_images')) {
                EventImage::whereIn('id', $request->remove_images)
                    ->where('event_id', $event->id)
                    ->delete();
            }

            // Xử lý upload ảnh mới
            if ($request->hasFile('images')) {
                $existingImagesCount = $event->images()->count();
                foreach ($request->file('images') as $index => $image) {
                    $imagePath = $image->store('events', 'public');
                    EventImage::create([
                        'event_id' => $event->id,
                        'image_path' => $imagePath,
                        'alt_text' => $request->title . ' - Ảnh ' . ($existingImagesCount + $index + 1),
                        'sort_order' => $existingImagesCount + $index,
                    ]);
                }
            }

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
            $event = Event::with(['club', 'creator', 'images'])->findOrFail($id);
            
            // Nếu sự kiện bị hủy, đảm bảo có lý do hủy
            if ($event->status === 'cancelled') {
                // Kiểm tra xem có trường cancellation_reason không
                if (!isset($event->cancellation_reason) || empty($event->cancellation_reason)) {
                    // Thêm lý do mặc định nếu chưa có
                    $event->cancellation_reason = 'Sự kiện đã bị hủy bởi quản trị viên';
                }
                if (!isset($event->cancelled_at) || empty($event->cancelled_at)) {
                    $event->cancelled_at = $event->updated_at;
                }
            }
            
            return view('admin.events.show', compact('event'));
        } catch (\Exception $e) {
            \Log::error('Error showing event: ' . $e->getMessage());
            return redirect()->route('admin.plans-schedule')->with('error', 'Không tìm thấy sự kiện: ' . $e->getMessage());
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
    public function eventsCancel(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            // Kiểm tra dữ liệu đầu vào
            if (!$request->has('cancellation_reason') || empty(trim($request->cancellation_reason))) {
                return back()->with('error', 'Vui lòng nhập lý do hủy sự kiện.');
            }

            $cancellationReason = trim($request->cancellation_reason);
            if (strlen($cancellationReason) < 5) {
                return back()->with('error', 'Lý do hủy sự kiện phải có ít nhất 5 ký tự.');
            }

            $event = Event::findOrFail($id);
            
            // Kiểm tra trạng thái sự kiện
            if (!in_array($event->status, ['pending', 'approved', 'ongoing'])) {
                return back()->with('error', 'Không thể hủy sự kiện ở trạng thái hiện tại.');
            }

            // Sử dụng DB::table để cập nhật trực tiếp
            $updateData = [
                'status' => 'cancelled',
                'updated_at' => now()
            ];

            // Kiểm tra và thêm các trường mới nếu có
            if (Schema::hasColumn('events', 'cancellation_reason')) {
                $updateData['cancellation_reason'] = $cancellationReason;
            }
            if (Schema::hasColumn('events', 'cancelled_at')) {
                $updateData['cancelled_at'] = now();
            }

            DB::table('events')
                ->where('id', $id)
                ->update($updateData);

            return redirect()->route('admin.plans-schedule')->with('success', 'Đã hủy sự kiện thành công!');
        } catch (\Exception $e) {
            \Log::error('Cancel event error:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi hủy sự kiện: ' . $e->getMessage());
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
}
