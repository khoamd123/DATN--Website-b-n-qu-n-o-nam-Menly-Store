<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationTarget;
use App\Models\NotificationRead;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\Post;
use App\Models\Field;
use App\Models\ClubMember;
use App\Services\UserAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Generate the next sequential student ID based on a two-letter prefix series
     * and a five-digit number starting from 10000. Sequence progresses by
     * incrementing the number; once it reaches 99999, move to the next prefix
     * lexicographically (e.g., AB -> AC -> AD ... -> AZ -> BA ...).
     */
    private function generateNextStudentId(string $startPrefix = 'AB'): string
    {
        // Find the latest existing student_id matching pattern XX99999 order by prefix+number
        $last = \App\Models\User::whereRaw("student_id REGEXP '^[A-Z]{2}[0-9]{5}$'")
            ->orderBy('student_id', 'desc')
            ->first(['student_id']);

        if (!$last) {
            return strtoupper($startPrefix) . '10000';
        }

        $code = $last->student_id;
        $prefix = substr($code, 0, 2);
        $num = intval(substr($code, 2));

        if ($num < 99999) {
            $num += 1;
            return $prefix . str_pad((string)$num, 5, '0', STR_PAD_LEFT);
        }

        // Roll number and increment prefix lexicographically
        $num = 10000;
        $a = ord($prefix[0]);
        $b = ord($prefix[1]);
        $b += 1;
        if ($b > ord('Z')) { $b = ord('A'); $a += 1; }
        if ($a > ord('Z')) { $a = ord($startPrefix[0]); $b = ord($startPrefix[1]); }
        $newPrefix = chr($a) . chr($b);

        // Ensure uniqueness: if exists, keep advancing
        while (\App\Models\User::where('student_id', $newPrefix . $num)->exists()) {
            $num += 1;
            if ($num > 99999) {
                $num = 10000;
                $a = ord($newPrefix[0]);
                $b = ord($newPrefix[1]) + 1;
                if ($b > ord('Z')) { $b = ord('A'); $a += 1; }
                if ($a > ord('Z')) { $a = ord($startPrefix[0]); $b = ord($startPrefix[1]); }
                $newPrefix = chr($a) . chr($b);
            }
        }

        return $newPrefix . str_pad((string)$num, 5, '0', STR_PAD_LEFT);
    }

    /**
     * API: return next student id for preview
     */
    public function nextStudentId(): \Illuminate\Http\JsonResponse
    {
        $next = $this->generateNextStudentId('AB');
        return response()->json(['student_id' => $next]);
    }
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
            
            // Sự kiện trong khoảng thời gian được chọn
            $upcomingEvents = Event::with(['club'])
                ->whereBetween('start_time', [$startDate, $endDate])
                ->orderBy('start_time', 'desc')
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
            
            // Sự kiện gần đây (cả sắp tới và đã diễn ra gần đây)
            $upcomingEvents = Event::with(['club'])
                ->where('start_time', '>=', now()->subDays(30)) // Lấy sự kiện từ 30 ngày trước đến tương lai
                ->orderBy('start_time', 'desc')
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
        
        // Dữ liệu cho biểu đồ - nếu có date filter thì lọc theo filter, nếu không thì 12 tháng gần nhất
        $monthlyStats = [];
        if ($dateFilter) {
            // Lọc theo khoảng thời gian được chọn
            $start = $startDate;
            $end = $endDate;
            $daysDiff = $start->diffInDays($end);
            
            // Nếu khoảng thời gian <= 31 ngày, chia theo ngày
            if ($daysDiff <= 31) {
                $current = clone $start;
                while ($current <= $end) {
                    $dayStart = $current->copy()->startOfDay();
                    $dayEnd = $current->copy()->endOfDay();
                    
                    // Đảm bảo không vượt quá khoảng thời gian được chọn
                    if ($dayStart < $start) $dayStart = $start;
                    if ($dayEnd > $end) $dayEnd = $end;
                    
                    $monthlyStats[] = [
                        'month' => $current->format('d/m/Y'),
                        'users' => User::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                        'clubs' => Club::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                        'events' => Event::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                        'posts' => Post::where('type', 'post')->whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    ];
                    
                    $current->addDay();
                }
            } else {
                // Nếu > 31 ngày, chia theo tháng
                $current = clone $start;
                while ($current <= $end) {
                    $monthStart = $current->copy()->startOfMonth();
                    $monthEnd = $current->copy()->endOfMonth();
                    
                    // Đảm bảo không vượt quá khoảng thời gian được chọn
                    if ($monthStart < $start) $monthStart = $start;
                    if ($monthEnd > $end) $monthEnd = $end;
                    
                    $monthlyStats[] = [
                        'month' => $current->format('M Y'),
                        'users' => User::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                        'clubs' => Club::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                        'events' => Event::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                        'posts' => Post::where('type', 'post')->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    ];
                    
                    $current->addMonth();
                }
            }
        } else {
            // Mặc định: 12 tháng gần nhất (từ tháng hiện tại trở về trước)
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
        }
        
        // KHÔNG fallback về dữ liệu mẫu - hiển thị đúng dữ liệu thực tế, kể cả khi = 0

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
        $query = User::with(['clubMembers.club']); // Eager load clubMembers với club
        
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
            $user = User::with(['clubMembers.club'])->findOrFail($id);
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
            
            // Xử lý student_id: nếu empty thì set về null
            if ($request->filled('student_id')) {
                $studentId = strtoupper(trim($request->student_id));
                $request->merge(['student_id' => $studentId ?: null]);
            } else {
                $request->merge(['student_id' => null]);
            }
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'student_id' => 'nullable|string|max:50|unique:users,student_id,' . $id . ',id,deleted_at,NULL',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'avatar' => 'nullable|file|max:10240', // Cho phép tất cả file types, max 10MB
                'remove_avatar' => 'nullable|boolean', // Cho phép xóa ảnh
            ]);
            
            $data = $request->except(['avatar', 'remove_avatar']);
            
            // Xử lý xóa ảnh đại diện
            if ($request->has('remove_avatar') && $request->remove_avatar) {
                // Xóa file ảnh cũ nếu có
                if ($user->avatar && file_exists(public_path($user->avatar))) {
                    @unlink(public_path($user->avatar));
                }
                $data['avatar'] = null; // Set về null để dùng ảnh mặc định
            }
            
            // Xử lý upload ảnh đại diện mới
            if ($request->hasFile('avatar')) {
                try {
                    $avatar = $request->file('avatar');
                    
                    // Lấy extension và tên file
                    $extension = $avatar->getClientOriginalExtension();
                    $avatarName = time() . '_' . $user->id . '.' . $extension;
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
     * Reset mật khẩu người dùng về mật khẩu mặc định
     */
    public function resetUserPassword($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $user = User::findOrFail($id);
            
            // Reset mật khẩu về "password"
            $user->password = Hash::make('password');
            $user->save();
            
            return redirect()->back()->with('success', 'Đã reset mật khẩu thành công! Mật khẩu mới là: password');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi reset mật khẩu: ' . $e->getMessage());
        }
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
        
        // Chỉ cho phép xóa CLB đã tạm dừng
        if ($club->status !== 'inactive') {
            return redirect()->back()->with('error', 'Chỉ có thể xóa câu lạc bộ đã tạm dừng!');
        }
        
        // Ẩn tất cả quỹ của CLB này
        \App\Models\Fund::where('club_id', $club->id)->update(['status' => 'inactive']);
        
        $club->delete(); // Soft delete
        
        // Gửi thông báo cho người dùng (owner và members)
        $notificationMessage = 'Câu lạc bộ ' . $club->name . ' của bạn đã được giải tán.';
        $senderId = auth()->id() ?? 1; // Admin user
        
        // Thu thập tất cả user_id cần gửi thông báo
        $userIds = [];
        if ($club->owner_id) {
            $userIds[] = $club->owner_id;
        }
        
        // Lấy các thành viên
        $members = ClubMember::where('club_id', $club->id)
            ->whereIn('status', ['active', 'approved'])
            ->get();
        
        foreach ($members as $member) {
            if ($member->user_id && $member->user_id != $club->owner_id && !in_array($member->user_id, $userIds)) {
                $userIds[] = $member->user_id;
            }
        }
        
        // Tạo notification và notification_targets cho từng user
        if (!empty($userIds)) {
            $notification = Notification::create([
                'sender_id' => $senderId,
                'title' => 'Câu lạc bộ đã được giải tán',
                'message' => $notificationMessage,
            ]);
            
            // Tạo notification_targets cho từng user
            foreach ($userIds as $userId) {
                NotificationTarget::create([
                    'notification_id' => $notification->id,
                    'target_type' => 'user',
                    'target_id' => $userId,
                ]);
            }
        }
        
        return redirect()->route('admin.clubs')->with('success', 'Câu lạc bộ đã được chuyển vào thùng rác!');
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
            $adminId = session('user_id');
            
            // Lấy thông báo dành cho admin này
            $query = \App\Models\Notification::whereHas('targets', function($q) use ($adminId) {
                $q->where('target_type', 'user')
                  ->where('target_id', $adminId);
            });
            
            // Bộ lọc: Trạng thái đọc/chưa đọc
            $filter = $request->get('filter', 'all');
            if ($filter === 'unread') {
                $query->whereDoesntHave('reads', function($q) use ($adminId) {
                    $q->where('user_id', $adminId)
                      ->where('is_read', true);
                });
            } elseif ($filter === 'read') {
                $query->whereHas('reads', function($q) use ($adminId) {
                    $q->where('user_id', $adminId)
                      ->where('is_read', true);
                });
            }
            
            // Bộ lọc: Theo người gửi
            if ($request->has('sender_id') && $request->sender_id) {
                $query->where('sender_id', $request->sender_id);
            }
            
            // Bộ lọc: Theo loại thông báo (tiêu đề)
            if ($request->has('type') && $request->type) {
                $query->where('title', $request->type);
            }
            
            // Bộ lọc: Tìm kiếm
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $senderIds = \App\Models\User::where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->pluck('id');
                
                $query->where(function($q) use ($search, $senderIds) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('message', 'like', '%' . $search . '%');
                    if ($senderIds->count() > 0) {
                        $q->orWhereIn('sender_id', $senderIds);
                    }
                });
            }
            
            $notifications = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
            
            // Thêm thuộc tính is_read cho mỗi notification
            $notifications->getCollection()->transform(function($notification) use ($adminId) {
                $read = \App\Models\NotificationRead::where('notification_id', $notification->id)
                    ->where('user_id', $adminId)
                    ->where('is_read', true)
                    ->first();
                $notification->is_read = $read ? true : false;
                return $notification;
            });
            
            // Clear session error "Không tìm thấy thông báo" nếu có thông báo
            if ($notifications->count() > 0 && session('error') && str_contains(session('error'), 'Không tìm thấy thông báo')) {
                session()->forget('error');
            }
            
            // Lấy danh sách người gửi để filter
            $senderIds = \App\Models\Notification::whereHas('targets', function($q) use ($adminId) {
                $q->where('target_type', 'user')
                  ->where('target_id', $adminId);
            })->whereNotNull('sender_id')
              ->distinct()
              ->pluck('sender_id');
            
            $senders = \App\Models\User::whereIn('id', $senderIds)->get();
            
            // Lấy danh sách loại thông báo
            $notificationTypes = \App\Models\Notification::whereHas('targets', function($q) use ($adminId) {
                $q->where('target_type', 'user')
                  ->where('target_id', $adminId);
            })->whereNotNull('title')
              ->distinct()
              ->pluck('title')
              ->filter()
              ->values();
            
        } catch (\Exception $e) {
            $notifications = collect();
            $senders = collect();
            $notificationTypes = collect();
        }

        return view('admin.notifications', compact('notifications', 'senders', 'notificationTypes'));
    }

    /**
     * Đánh dấu thông báo đã đọc
     */
    public function markNotificationRead(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $adminId = session('user_id');
            $notification = \App\Models\Notification::findOrFail($id);
            
            // Kiểm tra xem admin có quyền xem thông báo này không
            $hasAccess = \App\Models\NotificationTarget::where('notification_id', $id)
                ->where('target_type', 'user')
                ->where('target_id', $adminId)
                ->exists();
            
            if (!$hasAccess) {
                return redirect()->route('admin.notifications')->with('error', 'Bạn không có quyền xem thông báo này.');
            }
            
            // Đánh dấu đã đọc
            \App\Models\NotificationRead::updateOrCreate(
                [
                    'notification_id' => $id,
                    'user_id' => $adminId,
                ],
                [
                    'is_read' => true,
                ]
            );
            
            return redirect()->route('admin.notifications')->with('success', 'Đã đánh dấu thông báo đã đọc.');
        } catch (\Exception $e) {
            return redirect()->route('admin.notifications')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xóa thông báo
     */
    public function deleteNotification(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $adminId = session('user_id');
            $notification = \App\Models\Notification::findOrFail($id);
            
            // Kiểm tra xem admin có quyền xem thông báo này không
            $hasAccess = \App\Models\NotificationTarget::where('notification_id', $id)
                ->where('target_type', 'user')
                ->where('target_id', $adminId)
                ->exists();
            
            if (!$hasAccess) {
                return redirect()->route('admin.notifications')->with('error', 'Bạn không có quyền xóa thông báo này.');
            }
            
            // Xóa notification targets và notification reads liên quan
            \App\Models\NotificationTarget::where('notification_id', $id)->delete();
            \App\Models\NotificationRead::where('notification_id', $id)->delete();
            
            // Xóa thông báo
            $notification->delete();
            
            return redirect()->route('admin.notifications')->with('success', 'Đã xóa thông báo thành công.');
        } catch (\Exception $e) {
            return redirect()->route('admin.notifications')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết thông báo
     */
    public function showNotification($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $adminId = session('user_id');
            $notification = \App\Models\Notification::with(['sender', 'related'])->findOrFail($id);
            
            // Đánh dấu thông báo là đã đọc trong NotificationRead
            $notificationRead = \App\Models\NotificationRead::firstOrNew([
                'notification_id' => $notification->id,
                'user_id' => $adminId,
            ]);
            
            if (!$notificationRead->is_read) {
                $notificationRead->is_read = true;
                $notificationRead->save();
            }
            
            // Cũng cập nhật read_at trong notification nếu chưa có
            if (!$notification->read_at) {
                $notification->update(['read_at' => now()]);
            }

            // Điều hướng đến nội dung liên quan (ưu tiên sự kiện)
            $route = route('admin.notifications');
            $relatedType = strtolower($notification->related_type ?? '');
            $type = strtolower($notification->type ?? '');

            if (
                ($relatedType === 'app\\models\\event' || $relatedType === 'event' || $type === 'event')
                && $notification->related_id
            ) {
                $route = route('admin.events.show', $notification->related_id);
            } elseif (
                ($relatedType === 'app\\models\\post' || $relatedType === 'post' || $type === 'post')
                && $notification->related_id
            ) {
                $route = route('admin.posts.show', $notification->related_id);
            }

            return redirect($route);
        } catch (\Exception $e) {
            return redirect()->route('admin.notifications')->with('error', 'Không tìm thấy thông báo.');
        }
    }

    /**
     * Test tạo thông báo (tạm thời để debug)
     */
    public function testNotification()
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $user = User::find(session('user_id'));
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $notificationData = [
                'sender_id' => $user->id,
                'title' => 'Test thông báo',
                'message' => 'Đây là thông báo test để kiểm tra hệ thống.',
            ];
            
            // Chỉ thêm các field nếu cột tồn tại
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'type')) {
                $notificationData['type'] = 'event_created';
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_id')) {
                $notificationData['related_id'] = null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_type')) {
                $notificationData['related_type'] = null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'read_at')) {
                $notificationData['read_at'] = null;
            }
            
            $notification = \App\Models\Notification::create($notificationData);

            return response()->json([
                'success' => true,
                'message' => 'Thông báo test đã được tạo thành công!',
                'notification_id' => $notification->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllRead()
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $admin = User::with('clubs')->find(session('user_id'));
            if (!$admin) {
                return redirect()->route('simple.login')->with('error', 'Không tìm thấy tài khoản admin.');
            }

            $userClubIds = $admin->clubs->pluck('id')->toArray();

            // Lấy tất cả thông báo mà admin được target (user, all, club)
            $notifications = \App\Models\Notification::whereHas('targets', function($query) use ($admin, $userClubIds) {
                    $query->where(function($q) use ($admin, $userClubIds) {
                        $q->where(function($subQ) use ($admin) {
                            $subQ->where('target_type', 'user')
                                 ->where('target_id', $admin->id);
                        });
                        $q->orWhere(function($subQ) {
                            $subQ->where('target_type', 'all');
                        });
                        if (!empty($userClubIds)) {
                            $q->orWhere(function($subQ) use ($userClubIds) {
                                $subQ->where('target_type', 'club')
                                     ->whereIn('target_id', $userClubIds);
                            });
                        }
                    });
                })
                ->whereNull('deleted_at')
                ->get();

            foreach ($notifications as $notification) {
                NotificationRead::updateOrCreate(
                    [
                        'notification_id' => $notification->id,
                        'user_id' => $admin->id,
                    ],
                    ['is_read' => true]
                );
            }
            
            return redirect()->route('admin.notifications')->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
        } catch (\Exception $e) {
            \Log::error('Admin markAllRead error: ' . $e->getMessage());
            return redirect()->route('admin.notifications')->with('error', 'Có lỗi xảy ra khi đánh dấu thông báo.');
        }
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
        $query = Club::with(['field', 'owner', 'clubMembers' => function($query) {
            $query->whereIn('status', ['approved', 'active']);
        }, 'clubMembers.user']);
        
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
        
        // Lấy dữ liệu trực tiếp từ DB để tránh cache
        foreach ($clubs as $club) {
            // Query trực tiếp từ DB - GROUP BY để loại bỏ duplicate user_id
            // CHỈ LẤY MEMBERS CHƯA BỊ XÓA (deleted_at IS NULL)
            $approvedMembers = DB::table('club_members')
                ->where('club_id', $club->id)
                ->whereIn('status', ['approved', 'active'])
                ->whereNotNull('user_id')
                ->whereNull('deleted_at') // CHỈ LẤY MEMBERS CHƯA BỊ XÓA
                ->select('user_id', 'position', 'status')
                ->groupBy('user_id', 'position', 'status')
                ->get();
            
            // Lọc unique user_id (phòng trường hợp có duplicate)
            $uniqueUserIds = $approvedMembers->pluck('user_id')->unique();
            
            $leaders = $approvedMembers->filter(function($m) use ($uniqueUserIds) {
                return $uniqueUserIds->contains($m->user_id) && 
                       ($m->position === 'leader' || $m->position === 'chunhiem');
            });
            
            $treasurers = $approvedMembers->filter(function($m) use ($uniqueUserIds) {
                return $uniqueUserIds->contains($m->user_id) && $m->position === 'treasurer';
            });
            $vicePresidents = $approvedMembers->filter(function($m) use ($uniqueUserIds) {
                return $uniqueUserIds->contains($m->user_id) && $m->position === 'vice_president';
            });
            
            // Gán vào club object để dùng trong view
            $club->approved_members_count = $uniqueUserIds->count();
            $club->treasurers_count = $treasurers->unique('user_id')->count();
            $club->vice_presidents_count = $vicePresidents->unique('user_id')->count();
            $club->leaders_count = $leaders->unique('user_id')->count();
        }
        
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
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
            ]);

            // Validate: one user can be leader/treasurer of only one club
            if ($request->leader_id) {
                // Kiểm tra xem user đã là leader ở CLB khác chưa
                $alreadyLeading = Club::where('leader_id', $request->leader_id)->whereNull('deleted_at')->exists();
                if ($alreadyLeading) {
                    return back()->with('error', 'Người này đã là Trưởng của một CLB khác.')->withInput();
                }
                
                // Kiểm tra xem user đã là treasurer/leader trong bảng club_members ở CLB khác chưa
                $existingLeaderOfficer = \App\Models\ClubMember::where('user_id', $request->leader_id)
                    ->whereIn('status', ['approved', 'active'])
                    ->whereIn('position', ['leader', 'treasurer', 'vice_president'])
                    ->whereHas('club', function($query) {
                        $query->whereNull('deleted_at');
                    })
                    ->first();
                    
                if ($existingLeaderOfficer) {
                    $existingClub = Club::find($existingLeaderOfficer->club_id);
                    return back()->with('error', "Người này đã là thủ quỹ/phó CLB/trưởng ở CLB '{$existingClub->name}'. Một người chỉ được làm thủ quỹ/phó CLB/trưởng ở 1 CLB.")->withInput();
                }
            }

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

            // Xử lý upload logo
            $logoPath = null;
            if ($request->hasFile('logo')) {
                try {
                    $logo = $request->file('logo');
                    $logoName = time() . '_' . $clubSlug . '.' . $logo->getClientOriginalExtension();
                    $logoPath = 'uploads/clubs/logos/' . $logoName;
                    
                    // Tạo thư mục nếu chưa tồn tại
                    $logoDir = public_path('uploads/clubs/logos');
                    if (!file_exists($logoDir)) {
                        mkdir($logoDir, 0755, true);
                    }
                    
                    // Di chuyển file
                    $logo->move($logoDir, $logoName);
                } catch (\Exception $e) {
                    return back()->with('error', 'Lỗi upload logo: ' . $e->getMessage())->withInput();
                }
            }

            $club = Club::create([
                'name' => $request->name,
                'slug' => $clubSlug,
                'description' => $request->description,
                'logo' => $logoPath,
                'field_id' => $fieldId,
                'owner_id' => session('user_id'), // Set owner to current admin user
                'leader_id' => $request->leader_id,
                'status' => 'pending',
            ]);

            // Tự động tạo club member nếu có leader - đảm bảo position = leader, không phải member
            if ($request->leader_id) {
                // Kiểm tra xem đã có member record chưa
                $existingMember = ClubMember::where('club_id', $club->id)
                    ->where('user_id', $request->leader_id)
                    ->first();
                
                if ($existingMember) {
                    // Cập nhật position thành leader
                    $existingMember->update([
                        'position' => 'leader',
                        'status' => 'active',
                    ]);
                } else {
                    // Tạo mới với position = leader
                    ClubMember::create([
                        'club_id' => $club->id,
                        'user_id' => $request->leader_id,
                        'position' => 'leader', // Đảm bảo là leader, không phải member
                        'status' => 'active',
                        'joined_at' => now(),
                    ]);
                }
                
                // Tự động cấp tất cả quyền cho leader
                $allPermissions = \App\Models\Permission::all();
                foreach ($allPermissions as $permission) {
                    \DB::table('user_permissions_club')->insert([
                        'user_id' => $request->leader_id,
                        'club_id' => $club->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Tự động tạo quỹ cho CLB
            $fundCreatorId = session('user_id') ?? 1;
            \App\Models\Fund::create([
                'name' => 'Quỹ ' . $club->name,
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

            // Validate: one user can be leader/treasurer of only one club
            if ($request->leader_id) {
                // Kiểm tra xem user đã là leader ở CLB khác chưa
                $alreadyLeading = Club::where('leader_id', $request->leader_id)
                    ->where('id', '!=', $club->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($alreadyLeading) {
                    return back()->with('error', 'Người này đã là Trưởng của một CLB khác.');
                }
                
                // Kiểm tra xem user đã là treasurer/leader trong bảng club_members ở CLB khác chưa
                $existingLeaderOfficer = \App\Models\ClubMember::where('user_id', $request->leader_id)
                    ->whereIn('status', ['approved', 'active'])
                    ->whereIn('position', ['leader', 'treasurer', 'vice_president'])
                    ->where('club_id', '!=', $club->id)
                    ->whereHas('club', function($query) {
                        $query->whereNull('deleted_at');
                    })
                    ->first();
                    
                if ($existingLeaderOfficer) {
                    $existingClub = Club::find($existingLeaderOfficer->club_id);
                    return back()->with('error', "Người này đã là cán sự/trưởng ở CLB '{$existingClub->name}'. Một người chỉ được làm cán sự/trưởng ở 1 CLB.");
                }
            }

            // Lưu leader_id cũ để xử lý sau
            $oldLeaderId = $club->leader_id;
            
            $club->update([
                'name' => $request->name,
                'description' => $request->description,
                'field_id' => $request->field_id,
                'leader_id' => $request->leader_id,
                'status' => $request->status,
            ]);

            // Xử lý thay đổi leader
            if ($oldLeaderId != $request->leader_id) {
                // Nếu có leader cũ, chuyển về thành viên và xóa quyền
                if ($oldLeaderId) {
                    \DB::table('club_members')
                        ->where('user_id', $oldLeaderId)
                        ->where('club_id', $club->id)
                        ->update(['position' => 'member']);
                    
                    \DB::table('user_permissions_club')
                        ->where('user_id', $oldLeaderId)
                        ->where('club_id', $club->id)
                        ->delete();
                }
                
                // Nếu có leader mới, cấp quyền và set position
                if ($request->leader_id) {
                    // Tạo hoặc cập nhật club_member
                    $existingMember = ClubMember::where('user_id', $request->leader_id)
                        ->where('club_id', $club->id)
                        ->first();
                    
                    if ($existingMember) {
                        $existingMember->update([
                            'position' => 'leader',
                            'status' => 'active',
                        ]);
                    } else {
                        ClubMember::create([
                            'club_id' => $club->id,
                            'user_id' => $request->leader_id,
                            'position' => 'leader',
                            'status' => 'active',
                            'joined_at' => now(),
                        ]);
                    }
                    
                    // Xóa quyền cũ và cấp lại tất cả quyền
                    \DB::table('user_permissions_club')
                        ->where('user_id', $request->leader_id)
                        ->where('club_id', $club->id)
                        ->delete();
                    
                    $allPermissions = \App\Models\Permission::all();
                    foreach ($allPermissions as $permission) {
                        \DB::table('user_permissions_club')->insert([
                            'user_id' => $request->leader_id,
                            'club_id' => $club->id,
                            'permission_id' => $permission->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

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
        
        // Lọc bỏ các member có user_id không hợp lệ hoặc user đã bị xóa
        // CHỈ LẤY MEMBERS CÓ STATUS = 'approved' HOẶC 'active' (đồng bộ với index)
        $members = $club->clubMembers()
            ->with('user')
            ->whereHas('user') // Chỉ lấy members có user tồn tại
            ->whereIn('status', ['approved', 'active']) // CHỈ LẤY STATUS ĐÃ DUYỆT
            ->orderBy('position', 'asc')
            ->get();
        
        return view('admin.clubs.members-simple', compact('club', 'members'));
    }

    /**
     * Update club status
     */
    public function updateClubStatus(Request $request, $id)
    {
        $club = Club::with(['owner', 'leader'])->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,active,inactive',
            'rejection_reason' => 'nullable|string|max:1000'
        ]);
        
        $updateData = [
            'status' => $request->status
        ];
        
        // Lưu trạng thái cũ để so sánh
        $oldStatus = $club->status;
        $newStatus = $request->status;
        
        // Nếu từ chối, lưu lý do từ chối
        if ($request->status === 'rejected' && $request->filled('rejection_reason')) {
            // Kiểm tra xem bảng có cột rejection_reason không
            if (Schema::hasColumn('clubs', 'rejection_reason')) {
                $updateData['rejection_reason'] = $request->rejection_reason;
            }
        }
        
        // Nếu duyệt, tự động set status = active (thay vì approved)
        if ($request->status === 'approved') {
            $updateData['status'] = 'active';
            $newStatus = 'active';
        }
        
        $club->update($updateData);

        // Thông báo cho Trưởng CLB khi duyệt
        if (($updateData['status'] ?? null) === 'active') {
            try {
                // Tìm leader: ưu tiên leader_id, nếu chưa có lấy member position leader, nếu vẫn chưa có dùng owner_id
                $leaderId = $club->leader_id;
                if (!$leaderId) {
                    $leaderMember = ClubMember::where('club_id', $club->id)
                        ->whereIn('status', ['approved', 'active'])
                        ->where('position', 'leader')
                        ->first();
                    if ($leaderMember) {
                        $leaderId = $leaderMember->user_id;
                    }
                }
                if (!$leaderId && $club->owner_id) {
                    $leaderId = $club->owner_id;
                }

                if ($leaderId) {
                    $notification = Notification::create([
                        'sender_id'    => session('user_id') ?? null,
                        'type'         => 'club_status',
                        'title'        => 'CLB đã được duyệt',
                        'message'      => "Câu lạc bộ \"{$club->name}\" đã được quản trị viên duyệt và kích hoạt.",
                        'related_id'   => $club->id,
                        'related_type' => 'Club',
                    ]);

                    NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type'     => 'user',
                        'target_id'       => $leaderId,
                    ]);

                    NotificationRead::create([
                        'notification_id' => $notification->id,
                        'user_id'         => $leaderId,
                        'is_read'         => false,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Notify leader on club approval failed: ' . $e->getMessage());
            }
        }
        
        // Gửi thông báo cho người tạo CLB (owner hoặc leader)
        $adminId = session('user_id') ?? 1;
        $userId = $club->owner_id ?? $club->leader_id;
        
        if ($userId && ($oldStatus !== $newStatus)) {
            try {
                $notificationData = [
                    'sender_id' => $adminId,
                ];
                
                if ($newStatus === 'active' && $oldStatus === 'pending') {
                    // CLB được duyệt
                    $notificationData['title'] = 'Yêu cầu tạo CLB đã được duyệt';
                    $notificationData['message'] = "Chúc mừng! Yêu cầu tạo CLB \"{$club->name}\" của bạn đã được duyệt. CLB của bạn giờ đã có thể hoạt động chính thức.";
                } elseif ($newStatus === 'rejected' && $oldStatus === 'pending') {
                    // CLB bị từ chối
                    $rejectionReason = $request->input('rejection_reason', '');
                    $message = "Rất tiếc, yêu cầu tạo CLB \"{$club->name}\" của bạn đã bị từ chối.";
                    if (!empty($rejectionReason)) {
                        $message .= " Lý do: {$rejectionReason}";
                    } else {
                        $message .= " Vui lòng liên hệ với ban quản trị để biết thêm chi tiết.";
                    }
                    
                    $notificationData['title'] = 'Yêu cầu tạo CLB đã bị từ chối';
                    $notificationData['message'] = $message;
                } else {
                    // Trạng thái khác (inactive, etc.) - chỉ thông báo khi thay đổi từ pending
                    if ($oldStatus === 'pending') {
                        $statusText = [
                            'active' => 'Đang hoạt động',
                            'inactive' => 'Tạm ngưng',
                            'rejected' => 'Đã từ chối'
                        ];
                        
                        $notificationData['title'] = 'Trạng thái CLB đã được cập nhật';
                        $notificationData['message'] = "Trạng thái CLB \"{$club->name}\" của bạn đã được cập nhật thành: " . ($statusText[$newStatus] ?? $newStatus);
                    } else {
                        // Không gửi thông báo cho các thay đổi trạng thái khác (không phải từ pending)
                        return redirect()->back()->with('success', 'Cập nhật trạng thái câu lạc bộ thành công!');
                    }
                }
                
                // Thêm các trường optional nếu bảng có
                if (Schema::hasColumn('notifications', 'type')) {
                    $notificationData['type'] = 'club';
                }
                if (Schema::hasColumn('notifications', 'related_id')) {
                    $notificationData['related_id'] = $club->id;
                }
                if (Schema::hasColumn('notifications', 'related_type')) {
                    $notificationData['related_type'] = 'Club';
                }
                
                $notification = Notification::create($notificationData);
                
                if ($notification) {
                    NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type' => 'user',
                        'target_id' => $userId,
                    ]);
                    
                    NotificationRead::create([
                        'notification_id' => $notification->id,
                        'user_id' => $userId,
                        'is_read' => false,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Lỗi khi gửi thông báo cho người tạo CLB: ' . $e->getMessage());
                // Không fail toàn bộ request nếu thông báo lỗi
            }
        }
        
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
        
        $events = $query->orderBy('created_at', 'desc')->paginate(20);

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
                'description' => 'nullable|string|max:10000',
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'mode' => 'required|in:offline,online,hybrid',
                'location' => 'nullable|string|max:255',
                'max_participants' => 'nullable|integer|min:1',
                'status' => 'required|in:draft,pending,approved,ongoing,completed,cancelled',
                'registration_deadline' => 'nullable|date|before_or_equal:start_time',
                'main_organizer' => 'nullable|string|max:255',
                'organizing_team' => 'nullable|string|max:5000',
                'co_organizers' => 'nullable|string|max:2000',
                'contact_phone' => 'nullable|string|max:20',
                'contact_email' => 'nullable|email|max:255',
                'proposal_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'poster_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'permit_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'guest_types' => 'nullable|array',
                'guest_types.*' => 'in:lecturer,student,sponsor,other',
                'guest_other_info' => 'nullable|string|max:5000',
            ], [
                'images.*.image' => 'File ảnh không hợp lệ. Vui lòng chọn file ảnh (JPG, JPEG, PNG, WEBP).',
                'images.*.mimes' => 'Định dạng ảnh không được hỗ trợ. Chỉ chấp nhận: JPG, JPEG, PNG, WEBP.',
                'images.*.max' => 'Kích thước ảnh không được vượt quá 5MB. Vui lòng chọn ảnh nhỏ hơn.',
                'registration_deadline.before_or_equal' => 'Hạn chót đăng ký phải trước hoặc bằng thời gian bắt đầu sự kiện.',
                'contact_email.email' => 'Email không hợp lệ.',
                'proposal_file.mimes' => 'File kế hoạch chỉ chấp nhận định dạng: PDF, DOC, DOCX.',
                'proposal_file.max' => 'File kế hoạch không được vượt quá 10MB.',
                'poster_file.mimes' => 'File poster chỉ chấp nhận định dạng: PDF, JPG, JPEG, PNG.',
                'poster_file.max' => 'File poster không được vượt quá 10MB.',
                'permit_file.mimes' => 'File giấy phép chỉ chấp nhận định dạng: PDF, DOC, DOCX, JPG, JPEG, PNG.',
                'permit_file.max' => 'File giấy phép không được vượt quá 10MB.',
            ]);

            // Validate guest_other_info khi có chọn "other"
            if (is_array($request->guest_types) && in_array('other', $request->guest_types)) {
                if (empty(trim($request->guest_other_info ?? ''))) {
                    return back()->withErrors(['guest_other_info' => 'Vui lòng nhập thông tin khách mời khi chọn "Khác..."'])->withInput();
                }
            }

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

            // Xử lý upload files
            $proposalFilePath = null;
            $posterFilePath = null;
            $permitFilePath = null;
            
            if ($request->hasFile('proposal_file')) {
                $proposalFilePath = $request->file('proposal_file')->store('events/files', 'public');
            }
            
            if ($request->hasFile('poster_file')) {
                $posterFilePath = $request->file('poster_file')->store('events/posters', 'public');
            }
            
            if ($request->hasFile('permit_file')) {
                $permitFilePath = $request->file('permit_file')->store('events/permits', 'public');
            }
            
            // Xử lý contact_info
            $contactInfo = null;
            if ($request->contact_phone || $request->contact_email) {
                $contactInfo = json_encode([
                    'phone' => $request->contact_phone,
                    'email' => $request->contact_email,
                ]);
            }
            
            // Xử lý guests data
            $guestsData = null;
            $guestTypes = $request->guest_types ?? [];
            $otherInfo = $request->guest_other_info ?? null;
            
            if (!empty($guestTypes) || !empty(trim($otherInfo ?? ''))) {
                $guestsData = json_encode([
                    'types' => $guestTypes,
                    'other_info' => (is_array($guestTypes) && in_array('other', $guestTypes) && !empty(trim($otherInfo ?? ''))) ? trim($otherInfo) : null,
                ]);
            }
            
            // Tự động thêm các cột nếu chưa tồn tại
            $columns = DB::select("SHOW COLUMNS FROM events");
            $columnNames = array_column($columns, 'Field');
            
            // Danh sách cột cần thiết
            $requiredColumns = [
                'registration_deadline' => 'DATETIME NULL',
                'main_organizer' => 'VARCHAR(255) NULL',
                'organizing_team' => 'TEXT NULL',
                'co_organizers' => 'TEXT NULL',
                'contact_info' => 'TEXT NULL',
                'proposal_file' => 'VARCHAR(500) NULL',
                'poster_file' => 'VARCHAR(500) NULL',
                'permit_file' => 'VARCHAR(500) NULL',
                'guests' => 'TEXT NULL',
            ];
            
            // Tự động thêm các cột còn thiếu
            foreach ($requiredColumns as $colName => $colType) {
                if (!in_array($colName, $columnNames)) {
                    try {
                        DB::statement("ALTER TABLE events ADD COLUMN {$colName} {$colType}");
                        $columnNames[] = $colName; // Cập nhật danh sách
                        \Log::info("EventsStore - Auto added column: {$colName}");
                    } catch (\Exception $e) {
                        \Log::warning("EventsStore - Failed to add column {$colName}: " . $e->getMessage());
                    }
                }
            }
            
            // Cập nhật lại danh sách cột sau khi thêm
            $columns = DB::select("SHOW COLUMNS FROM events");
            $columnNames = array_column($columns, 'Field');
            
            // Log để debug
            \Log::info('EventsStore - Columns check', [
                'columns_exists' => $columnNames,
            ]);
            
            $eventData = [
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'image' => null,
                'club_id' => $request->club_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'mode' => $request->mode,
                'location' => $request->location,
                'max_participants' => $request->max_participants,
                'status' => $request->status,
                'created_by' => session('user_id'),
            ];
            
            // Thêm tất cả các field mới vào eventData
            $addedFields = [];
            if (in_array('registration_deadline', $columnNames)) {
                $eventData['registration_deadline'] = $request->registration_deadline;
                $addedFields[] = 'registration_deadline';
            }
            if (in_array('main_organizer', $columnNames)) {
                $eventData['main_organizer'] = $request->main_organizer;
                $addedFields[] = 'main_organizer';
            }
            if (in_array('organizing_team', $columnNames)) {
                $eventData['organizing_team'] = $request->organizing_team;
                $addedFields[] = 'organizing_team';
            }
            if (in_array('co_organizers', $columnNames)) {
                $eventData['co_organizers'] = $request->co_organizers;
                $addedFields[] = 'co_organizers';
            }
            if (in_array('contact_info', $columnNames)) {
                $eventData['contact_info'] = $contactInfo;
                $addedFields[] = 'contact_info';
            }
            if (in_array('proposal_file', $columnNames)) {
                $eventData['proposal_file'] = $proposalFilePath;
                $addedFields[] = 'proposal_file';
            }
            if (in_array('poster_file', $columnNames)) {
                $eventData['poster_file'] = $posterFilePath;
                $addedFields[] = 'poster_file';
            }
            if (in_array('permit_file', $columnNames)) {
                $eventData['permit_file'] = $permitFilePath;
                $addedFields[] = 'permit_file';
            }
            if (in_array('guests', $columnNames)) {
                $eventData['guests'] = $guestsData;
                $addedFields[] = 'guests';
            }
            
            // Log dữ liệu sẽ được lưu
            \Log::info('EventsStore - Data to save', [
                'added_fields' => $addedFields,
                'main_organizer' => $request->main_organizer ?? 'null',
                'organizing_team' => $request->organizing_team ? 'has_data' : 'null',
                'co_organizers' => $request->co_organizers ? 'has_data' : 'null',
                'contact_info' => $contactInfo,
                'proposal_file' => $proposalFilePath,
                'poster_file' => $posterFilePath,
                'permit_file' => $permitFilePath,
                'guests' => $guestsData,
            ]);
            
            $event = Event::create($eventData);
            
            // Log sau khi tạo
            \Log::info('EventsStore - Event created', [
                'event_id' => $event->id,
                'main_organizer' => $event->main_organizer ?? 'null',
                'organizing_team' => $event->organizing_team ? 'has_data' : 'null',
                'co_organizers' => $event->co_organizers ? 'has_data' : 'null',
                'contact_info' => $event->contact_info,
                'proposal_file' => $event->proposal_file,
                'poster_file' => $event->poster_file,
                'permit_file' => $event->permit_file,
                'guests' => $event->guests,
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Create event error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Có lỗi xảy ra khi tạo sự kiện: ' . $e->getMessage())->withInput();
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
            
            // Ngăn chặn chỉnh sửa nếu sự kiện đã hoàn thành
            if ($event->status === 'completed') {
                return redirect()->route('admin.events.show', $event->id)
                    ->with('error', 'Không thể chỉnh sửa sự kiện đã hoàn thành.');
            }
            
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
            // Lấy event hiện tại để kiểm tra status
            $event = Event::findOrFail($id);
            
            // Ngăn chặn chỉnh sửa nếu sự kiện đã hoàn thành
            if ($event->status === 'completed') {
                return back()->with('error', 'Không thể chỉnh sửa sự kiện đã hoàn thành.');
            }
            
            $request->validate([
                'title' => 'required|string|max:255',
                'club_id' => 'required|exists:clubs,id',
                'description' => 'nullable|string|max:10000',
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', // Tăng lên 5MB
                'remove_images' => 'nullable|array',
                'remove_images.*' => 'integer|exists:event_images,id',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'mode' => 'required|in:offline,online,hybrid',
                'location' => 'nullable|string|max:255',
                'max_participants' => 'nullable|integer|min:1',
                'status' => 'required|in:draft,pending,approved,ongoing,completed,cancelled',
                'registration_deadline' => 'nullable|date|before_or_equal:start_time',
                'main_organizer' => 'nullable|string|max:255',
                'organizing_team' => 'nullable|string|max:5000',
                'co_organizers' => 'nullable|string|max:2000',
                'contact_phone' => 'nullable|string|max:20',
                'contact_email' => 'nullable|email|max:255',
                'proposal_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'poster_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'permit_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'guest_types' => 'nullable|array',
                'guest_types.*' => 'in:lecturer,student,sponsor,other',
                'guest_other_info' => 'nullable|string|max:5000',
            ], [
                'images.*.image' => 'File ảnh không hợp lệ. Vui lòng chọn file ảnh (JPG, JPEG, PNG, WEBP).',
                'images.*.mimes' => 'Định dạng ảnh không được hỗ trợ. Chỉ chấp nhận: JPG, JPEG, PNG, WEBP.',
                'images.*.max' => 'Kích thước ảnh không được vượt quá 5MB. Vui lòng chọn ảnh nhỏ hơn.',
            ]);

            // Validate guest_other_info khi có chọn "other"
            if (is_array($request->guest_types) && in_array('other', $request->guest_types)) {
                if (empty(trim($request->guest_other_info ?? ''))) {
                    return back()->withErrors(['guest_other_info' => 'Vui lòng nhập thông tin khách mời khi chọn "Khác..."'])->withInput();
                }
            }

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

            // Xử lý upload files (chỉ upload nếu có file mới)
            if ($request->hasFile('proposal_file')) {
                // Xóa file cũ nếu có
                if ($event->proposal_file) {
                    \Storage::disk('public')->delete($event->proposal_file);
                }
                $proposalFilePath = $request->file('proposal_file')->store('events/files', 'public');
            } else {
                $proposalFilePath = $event->proposal_file;
            }
            
            if ($request->hasFile('poster_file')) {
                if ($event->poster_file) {
                    \Storage::disk('public')->delete($event->poster_file);
                }
                $posterFilePath = $request->file('poster_file')->store('events/posters', 'public');
            } else {
                $posterFilePath = $event->poster_file;
            }
            
            if ($request->hasFile('permit_file')) {
                if ($event->permit_file) {
                    \Storage::disk('public')->delete($event->permit_file);
                }
                $permitFilePath = $request->file('permit_file')->store('events/permits', 'public');
            } else {
                $permitFilePath = $event->permit_file;
            }
            
            // Xử lý contact_info
            $contactInfo = null;
            if ($request->contact_phone || $request->contact_email) {
                $contactInfo = json_encode([
                    'phone' => $request->contact_phone,
                    'email' => $request->contact_email,
                ]);
            } elseif ($event->contact_info) {
                $contactInfo = $event->contact_info; // Giữ nguyên nếu không có thay đổi
            }
            
            // Xử lý guests data
            $guestsData = null;
            $guestTypes = $request->guest_types ?? [];
            $otherInfo = $request->guest_other_info ?? null;
            
            if (!empty($guestTypes) || !empty(trim($otherInfo ?? ''))) {
                $guestsData = json_encode([
                    'types' => $guestTypes,
                    'other_info' => (is_array($guestTypes) && in_array('other', $guestTypes) && !empty(trim($otherInfo ?? ''))) ? trim($otherInfo) : null,
                ]);
            } elseif ($event->guests) {
                $guestsData = $event->guests; // Giữ nguyên nếu không có thay đổi
            }
            
            // Tự động thêm các cột nếu chưa tồn tại
            $columns = DB::select("SHOW COLUMNS FROM events");
            $columnNames = array_column($columns, 'Field');
            
            // Danh sách cột cần thiết
            $requiredColumns = [
                'registration_deadline' => 'DATETIME NULL',
                'main_organizer' => 'VARCHAR(255) NULL',
                'organizing_team' => 'TEXT NULL',
                'co_organizers' => 'TEXT NULL',
                'contact_info' => 'TEXT NULL',
                'proposal_file' => 'VARCHAR(500) NULL',
                'poster_file' => 'VARCHAR(500) NULL',
                'permit_file' => 'VARCHAR(500) NULL',
                'guests' => 'TEXT NULL',
            ];
            
            // Tự động thêm các cột còn thiếu
            foreach ($requiredColumns as $colName => $colType) {
                if (!in_array($colName, $columnNames)) {
                    try {
                        DB::statement("ALTER TABLE events ADD COLUMN {$colName} {$colType}");
                        $columnNames[] = $colName; // Cập nhật danh sách
                        \Log::info("EventsUpdate - Auto added column: {$colName}");
                    } catch (\Exception $e) {
                        \Log::warning("EventsUpdate - Failed to add column {$colName}: " . $e->getMessage());
                    }
                }
            }
            
            // Cập nhật lại danh sách cột sau khi thêm
            $columns = DB::select("SHOW COLUMNS FROM events");
            $columnNames = array_column($columns, 'Field');
            
            // Log để debug
            \Log::info('EventsUpdate - Columns check', [
                'event_id' => $id,
                'columns_exists' => $columnNames,
            ]);
            
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
            
            // Thêm tất cả các field mới vào data
            $addedFields = [];
            if (in_array('registration_deadline', $columnNames)) {
                $data['registration_deadline'] = $request->registration_deadline;
                $addedFields[] = 'registration_deadline';
            }
            if (in_array('main_organizer', $columnNames)) {
                $data['main_organizer'] = $request->main_organizer;
                $addedFields[] = 'main_organizer';
            }
            if (in_array('organizing_team', $columnNames)) {
                $data['organizing_team'] = $request->organizing_team;
                $addedFields[] = 'organizing_team';
            }
            if (in_array('co_organizers', $columnNames)) {
                $data['co_organizers'] = $request->co_organizers;
                $addedFields[] = 'co_organizers';
            }
            if (in_array('contact_info', $columnNames)) {
                $data['contact_info'] = $contactInfo;
                $addedFields[] = 'contact_info';
            }
            if (in_array('proposal_file', $columnNames)) {
                $data['proposal_file'] = $proposalFilePath;
                $addedFields[] = 'proposal_file';
            }
            if (in_array('poster_file', $columnNames)) {
                $data['poster_file'] = $posterFilePath;
                $addedFields[] = 'poster_file';
            }
            if (in_array('permit_file', $columnNames)) {
                $data['permit_file'] = $permitFilePath;
                $addedFields[] = 'permit_file';
            }
            if (in_array('guests', $columnNames)) {
                $data['guests'] = $guestsData;
                $addedFields[] = 'guests';
            }
            
            // Log dữ liệu sẽ được cập nhật
            \Log::info('EventsUpdate - Data to update', [
                'event_id' => $id,
                'added_fields' => $addedFields,
                'main_organizer' => $request->main_organizer ?? 'null',
                'organizing_team' => $request->organizing_team ? 'has_data' : 'null',
                'co_organizers' => $request->co_organizers ? 'has_data' : 'null',
                'contact_info' => $contactInfo,
                'proposal_file' => $proposalFilePath,
                'poster_file' => $posterFilePath,
                'permit_file' => $permitFilePath,
                'guests' => $guestsData,
            ]);

            // Cập nhật event - admin có thể tự do thay đổi status
            // Sử dụng DB::table để tránh trigger boot method tự động cập nhật status
            DB::table('events')->where('id', $event->id)->update($data);
            
            // Refresh event để lấy dữ liệu mới nhất (không trigger boot method)
            // Sử dụng withoutEvents để tránh boot method tự động cập nhật status
            $event = Event::withoutEvents(function() use ($event) {
                return Event::find($event->id);
            });
            \Log::info('EventsUpdate - Event updated', [
                'event_id' => $event->id,
                'main_organizer' => $event->main_organizer ?? 'null',
                'organizing_team' => $event->organizing_team ? 'has_data' : 'null',
                'co_organizers' => $event->co_organizers ? 'has_data' : 'null',
                'contact_info' => $event->contact_info,
                'proposal_file' => $event->proposal_file,
                'poster_file' => $event->poster_file,
                'permit_file' => $event->permit_file,
                'guests' => $event->guests,
            ]);

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
            
            // Lấy danh sách người đăng ký (admin luôn xem được)
            $registrations = \App\Models\EventRegistration::with('user')
                ->where('event_id', $id)
                ->whereIn('status', ['registered', 'pending', 'approved'])
                ->orderBy('joined_at', 'desc')
                ->get();
            
            return view('admin.events.show', compact('event', 'registrations'));
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
            
            // Kiểm tra xem cột status có tồn tại không
            $columnNames = \Illuminate\Support\Facades\Schema::getColumnListing('events');
            if (!in_array('status', $columnNames)) {
                // Nếu chưa có cột status, thêm vào
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE events ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
                } catch (\Exception $e) {
                    \Log::error('Failed to add status column: ' . $e->getMessage());
                }
            }
            
            $event->status = 'approved';
            $event->save();

            $this->notifyEventCreator($event, 'Sự kiện đã được duyệt', "Sự kiện \"{$event->title}\" đã được duyệt bởi ban quản trị.");

            $this->notifyEventCreator($event, 'Sự kiện đã được duyệt', "Sự kiện \"{$event->title}\" đã được duyệt bởi ban quản trị.");
            
            return redirect()->route('admin.events.show', $id)->with('success', 'Đã duyệt sự kiện thành công!');
        } catch (\Exception $e) {
            \Log::error('Error approving event: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Có lỗi xảy ra khi duyệt sự kiện: ' . $e->getMessage());
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
            
            // Kiểm tra trạng thái sự kiện - chỉ cho phép hủy khi chưa diễn ra
            if (!in_array($event->status, ['pending', 'approved'])) {
                if ($event->status === 'ongoing') {
                    return back()->with('error', 'Sự kiện đang diễn ra, không thể hủy.');
                }
                if ($event->status === 'completed') {
                    return back()->with('error', 'Sự kiện đã hoàn thành, không thể hủy.');
                }
                return back()->with('error', 'Không thể hủy sự kiện ở trạng thái hiện tại.');
            }
            
            // Kiểm tra thời gian - không cho phép hủy nếu sự kiện đã bắt đầu hoặc đã kết thúc
            $now = now();
            if ($event->start_time && $event->start_time->isPast()) {
                return back()->with('error', 'Sự kiện đã bắt đầu, không thể hủy.');
            }
            if ($event->end_time && $event->end_time->isPast()) {
                return back()->with('error', 'Sự kiện đã kết thúc, không thể hủy.');
            }

            // Cập nhật bằng DB::table để tránh lỗi cột không tồn tại
            $updateData = [
                'status' => 'cancelled',
                'updated_at' => now()
            ];
            
            // Kiểm tra và thêm các trường mới nếu có
            try {
                $columns = DB::select("SHOW COLUMNS FROM events LIKE 'cancellation_reason'");
                if (count($columns) > 0) {
                    $updateData['cancellation_reason'] = $cancellationReason;
                }
                
                $columns = DB::select("SHOW COLUMNS FROM events LIKE 'cancelled_at'");
                if (count($columns) > 0) {
                    $updateData['cancelled_at'] = now();
                }
            } catch (\Exception $e) {
                \Log::warning('Columns check failed: ' . $e->getMessage());
            }
            
            DB::table('events')->where('id', $id)->update($updateData);

            $event->refresh();
            $this->notifyEventCreator($event, 'Sự kiện bị hủy', "Sự kiện \"{$event->title}\" đã bị hủy. Lý do: {$cancellationReason}");

            \Log::info('Event cancelled', [
                'event_id' => $id,
                'cancellation_reason' => $cancellationReason,
                'updated_at' => now()
            ]);

            return redirect()->route('admin.plans-schedule')->with('success', 'Đã hủy sự kiện thành công!');
        } catch (\Exception $e) {
            \Log::error('Cancel event error:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi hủy sự kiện: ' . $e->getMessage());
        }
    }

    private function notifyEventCreator($event, $title, $message)
    {
        if (!$event || !$event->created_by) {
            return;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            return;
        }

        $notificationData = [
            'sender_id' => session('user_id') ?? 0,
            'title' => $title,
            'message' => $message,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'type')) {
            $notificationData['type'] = 'event';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_id')) {
            $notificationData['related_id'] = $event->id;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_type')) {
            $notificationData['related_type'] = Event::class;
        }

        try {
            $notification = Notification::create(array_filter($notificationData, function ($value) {
                return $value !== null;
            }));
            if (!$notification) {
                return;
            }

            NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_type' => 'user',
                'target_id' => $event->created_by,
            ]);

            NotificationRead::create([
                'notification_id' => $notification->id,
                'user_id' => $event->created_by,
                'is_read' => 0,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to notify event creator: ' . $e->getMessage());
        }
    }

    /**
     * Xóa/Hủy sự kiện (từ route DELETE)
     */
    public function deleteEvent(Request $request, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            $event = Event::findOrFail($id);
            
            // Kiểm tra trạng thái sự kiện - chỉ cho phép hủy khi chưa diễn ra
            if (!in_array($event->status, ['pending', 'approved'])) {
                if ($event->status === 'ongoing') {
                    return back()->with('error', 'Sự kiện đang diễn ra, không thể hủy.');
                }
                if ($event->status === 'completed') {
                    return back()->with('error', 'Sự kiện đã hoàn thành, không thể hủy.');
                }
                return back()->with('error', 'Không thể hủy sự kiện ở trạng thái hiện tại.');
            }
            
            // Kiểm tra thời gian - không cho phép hủy nếu sự kiện đã bắt đầu hoặc đã kết thúc
            $now = now();
            if ($event->start_time && $event->start_time->isPast()) {
                return back()->with('error', 'Sự kiện đã bắt đầu, không thể hủy.');
            }
            if ($event->end_time && $event->end_time->isPast()) {
                return back()->with('error', 'Sự kiện đã kết thúc, không thể hủy.');
            }
            
            // Kiểm tra dữ liệu đầu vào
            $cancellationReason = $request->input('deletion_reason') ?? $request->input('cancellation_reason');
            
            if (empty(trim($cancellationReason ?? ''))) {
                return back()->with('error', 'Vui lòng nhập lý do hủy sự kiện.');
            }

            $cancellationReason = trim($cancellationReason);
            if (strlen($cancellationReason) < 5) {
                return back()->with('error', 'Lý do hủy sự kiện phải có ít nhất 5 ký tự.');
            }

            // Cập nhật bằng DB::table để tránh lỗi cột không tồn tại
            $updateData = [
                'status' => 'cancelled',
                'updated_at' => now()
            ];
            
            // Kiểm tra và thêm các trường mới nếu có
            try {
                $columns = DB::select("SHOW COLUMNS FROM events");
                $columnNames = array_column($columns, 'Field');
                
                if (in_array('cancellation_reason', $columnNames)) {
                    $updateData['cancellation_reason'] = $cancellationReason;
                }
                
                if (in_array('cancelled_at', $columnNames)) {
                    $updateData['cancelled_at'] = now();
                }
            } catch (\Exception $e) {
                \Log::warning('Columns check failed: ' . $e->getMessage());
            }
            
            DB::table('events')->where('id', $id)->update($updateData);

            // Log để debug
            \Log::info('Event deleted/cancelled', [
                'event_id' => $id,
                'cancellation_reason' => $cancellationReason,
            ]);

            return redirect()->route('admin.plans-schedule')->with('success', 'Đã hủy sự kiện thành công!');
        } catch (\Exception $e) {
            \Log::error('Delete event error:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi hủy sự kiện: ' . $e->getMessage());
        }
    }

    /**
     * Display posts management page
     */
    public function postsManagement(Request $request)
    {
        // Chỉ hiển thị bài viết chưa bị soft delete
        $query = Post::withoutTrashed()
            ->whereIn('type', ['post', 'announcement'])
            ->where('status', '!=', 'deleted') // Loại bỏ posts có status='deleted' (legacy)
            ->with(['club', 'user']);
        
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
     * Generate 5 sample posts with images for a specific club
     */
    public function generateSamplePostsForClub($clubId)
    {
        $club = Club::findOrFail($clubId);
        
        // Lấy danh sách ảnh sẵn có trong public/uploads/posts
        $imagesDir = public_path('uploads/posts');
        $imagePatterns = ['*.jpg', '*.jpeg', '*.png', '*.webp', '*.gif'];
        $images = [];
        foreach ($imagePatterns as $pattern) {
            $images = array_merge($images, glob($imagesDir . DIRECTORY_SEPARATOR . $pattern));
        }
        
        if (empty($images)) {
            return redirect()->back()->with('error', 'Không tìm thấy ảnh mẫu trong thư mục uploads/posts');
        }
        
        $titles = [
            'Hoạt động nổi bật của ' . $club->name,
            'Nhìn lại tuần qua cùng ' . $club->name,
            'Thông tin mới từ ' . $club->name,
            'Điểm tin ' . $club->name,
            'Sự kiện sắp tới của ' . $club->name,
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $title = $titles[$i % count($titles)];
            $slugBase = Str::slug($title);
            $slug = $slugBase;
            $k = 1;
            while (\App\Models\Post::where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . time() . '-' . $k;
                $k++;
            }
            
            // Chọn ảnh theo vòng
            $chosenImage = $images[$i % count($images)];
            $relativeImagePath = 'uploads/posts/' . basename($chosenImage);
            
            \App\Models\Post::create([
                'title' => $title,
                'slug' => $slug,
                'content' => '<p>Bài viết mẫu tự động tạo cho <strong>' . e($club->name) . '</strong>. Đây là nội dung mô tả ngắn để minh họa giao diện tin tức.</p>',
                'club_id' => $club->id,
                'user_id' => session('user_id') ?? 1,
                'type' => 'post',
                'status' => 'published',
                'image' => $relativeImagePath,
            ]);
        }
        
        return redirect()->back()->with('success', 'Đã tạo 5 bài viết mẫu cho ' . $club->name . '!');
    }
    
    /**
     * Generate 5 sample posts with images for all clubs
     */
    public function generateSamplePostsForAllClubs()
    {
        $clubs = Club::all();
        if ($clubs->isEmpty()) {
            return redirect()->back()->with('error', 'Không có câu lạc bộ nào để tạo bài viết.');
        }
        
        $imagesDir = public_path('uploads/posts');
        $imagePatterns = ['*.jpg', '*.jpeg', '*.png', '*.webp', '*.gif'];
        $images = [];
        foreach ($imagePatterns as $pattern) {
            $images = array_merge($images, glob($imagesDir . DIRECTORY_SEPARATOR . $pattern));
        }
        if (empty($images)) {
            return redirect()->back()->with('error', 'Không tìm thấy ảnh mẫu trong thư mục uploads/posts');
        }
        
        foreach ($clubs as $club) {
            for ($i = 0; $i < 5; $i++) {
                $title = 'Bài viết mẫu #' . ($i + 1) . ' - ' . $club->name;
                $slugBase = Str::slug($title);
                $slug = $slugBase;
                $k = 1;
                while (\App\Models\Post::where('slug', $slug)->exists()) {
                    $slug = $slugBase . '-' . time() . '-' . $k;
                    $k++;
                }
                
                $chosenImage = $images[($i + $club->id) % count($images)];
                $relativeImagePath = 'uploads/posts/' . basename($chosenImage);
                
                \App\Models\Post::create([
                    'title' => $title,
                    'slug' => $slug,
                    'content' => '<p>Nội dung mẫu cho bài viết của <strong>' . e($club->name) . '</strong>. Ảnh minh họa hiển thị đúng giao diện.</p>',
                    'club_id' => $club->id,
                    'user_id' => session('user_id') ?? 1,
                    'type' => 'post',
                    'status' => 'published',
                    'image' => $relativeImagePath,
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Đã tạo 5 bài viết mẫu cho tất cả câu lạc bộ!');
    }
    /**
     * Hiển thị danh sách bài viết đã xóa (trash)
     */
    public function postsTrash(Request $request)
    {
        $query = Post::onlyTrashed()
            ->whereIn('type', ['post', 'announcement'])
            ->with(['club', 'user']);
        
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
        
        $posts = $query->orderBy('deleted_at', 'desc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.posts.trash', compact('posts', 'clubs'));
    }

    /**
     * Hiển thị chi tiết bài viết
     */
    public function postsShow($id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            // Bao gồm cả bài viết đã xóa (trong thùng rác)
            $post = Post::withTrashed()
                ->whereIn('type', ['post', 'announcement'])
                ->with(['club', 'user', 'attachments', 'comments.user', 'comments.replies.user'])
                ->findOrFail($id);
            
            return view('admin.posts.show', compact('post'));
        } catch (\Exception $e) {
            \Log::error('Error showing post: ' . $e->getMessage());
            return redirect()->route('admin.posts')->with('error', 'Không tìm thấy bài viết: ' . $e->getMessage());
        }
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
                'status' => 'required|in:published,hidden,deleted,members_only',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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

            $post = Post::create([
                'title' => $request->title,
                'slug' => $slug,
                'content' => $request->content,
                'club_id' => $request->club_id,
                'user_id' => session('user_id'),
                'type' => $request->type,
                'status' => $request->status,
            ]);

            // Handle images (optional, multiple)
            if ($request->hasFile('images')) {
                $firstImagePath = null;
                foreach ($request->file('images') as $index => $image) {
                    $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                    $imagePath = 'uploads/posts/' . $imageName;
                    $image->move(public_path('uploads/posts'), $imageName);
                    \App\Models\PostAttachment::create([
                        'post_id' => $post->id,
                        'file_url' => $imagePath,
                        'file_type' => 'image'
                    ]);
                    if ($index === 0) {
                        $firstImagePath = $imagePath;
                    }
                }
                if ($firstImagePath) {
                    $post->update(['image' => $firstImagePath]);
                }
            }

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
            $post = Post::withTrashed()->with('attachments')->whereIn('type', ['post', 'announcement'])->findOrFail($id);
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
                'status' => 'required|in:published,hidden,deleted,members_only',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'remove_image' => 'nullable|boolean',
                'deleted_attachments' => 'nullable|string',
            ]);

            $post = Post::whereIn('type', ['post', 'announcement'])->findOrFail($id);
            
            // Load attachments để xử lý
            $post->load('attachments');
            
            $updateData = [
                'title' => $request->title,
                'content' => $request->content,
                'club_id' => $request->club_id,
                'type' => $request->type,
                'status' => $request->status,
            ];

            // Xử lý xóa từng attachment theo ID (nếu có)
            if ($request->has('deleted_attachments') && !empty($request->deleted_attachments)) {
                $deletedIds = explode(',', $request->deleted_attachments);
                $deletedIds = array_filter(array_map('trim', $deletedIds));
                
                if (!empty($deletedIds)) {
                    foreach ($deletedIds as $attachmentId) {
                        $attachment = \App\Models\PostAttachment::find($attachmentId);
                        if ($attachment && $attachment->post_id == $post->id) {
                            // Xóa file vật lý
                            if (file_exists(public_path($attachment->file_url))) {
                                @unlink(public_path($attachment->file_url));
                            }
                            // Xóa record trong database
                            $attachment->delete();
                        }
                    }
                    
                    // Cập nhật ảnh chính nếu ảnh đầu tiên bị xóa hoặc không còn attachment nào
                    $remainingAttachments = $post->fresh()->attachments;
                    if ($remainingAttachments->count() > 0) {
                        $updateData['image'] = $remainingAttachments->first()->file_url;
                    } else {
                        $updateData['image'] = null;
                    }
                }
            }

            // Xử lý xóa ảnh nếu người dùng yêu cầu (không upload ảnh mới và không có deleted_attachments)
            if ($request->has('remove_image') && $request->remove_image == '1' && !$request->hasFile('images') && empty($request->deleted_attachments)) {
                // Xóa file ảnh cũ trong bảng post_attachments
                foreach ($post->attachments as $attachment) {
                    if (file_exists(public_path($attachment->file_url))) {
                        @unlink(public_path($attachment->file_url));
                    }
                }
                // Xóa records cũ trong database
                $post->attachments()->delete();
                
                // Xóa file ảnh cũ trong cột image nếu có
                if ($post->image && file_exists(public_path($post->image))) {
                    @unlink(public_path($post->image));
                }
                
                $updateData['image'] = null;
            }

            // Xử lý upload ảnh nếu có (nhiều ảnh)
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                
                // Xử lý xóa các attachments được đánh dấu xóa trước (nếu có)
                if ($request->has('deleted_attachments') && !empty($request->deleted_attachments)) {
                    $deletedIds = explode(',', $request->deleted_attachments);
                    $deletedIds = array_filter(array_map('trim', $deletedIds));
                    
                    if (!empty($deletedIds)) {
                        foreach ($deletedIds as $attachmentId) {
                            $attachment = \App\Models\PostAttachment::find($attachmentId);
                            if ($attachment && $attachment->post_id == $post->id) {
                                // Xóa file vật lý
                                if (file_exists(public_path($attachment->file_url))) {
                                    @unlink(public_path($attachment->file_url));
                                }
                                // Xóa record trong database
                                $attachment->delete();
                            }
                        }
                    }
                }
                
                // Nếu có flag remove_image = 1 và upload ảnh mới, xóa tất cả attachments cũ và thay thế
                if ($request->has('remove_image') && $request->remove_image == '1') {
                    // Xóa file ảnh cũ trong bảng post_attachments
                    foreach ($post->fresh()->attachments as $attachment) {
                        if (file_exists(public_path($attachment->file_url))) {
                            @unlink(public_path($attachment->file_url));
                        }
                    }
                    // Xóa records cũ trong database
                    $post->attachments()->delete();
                    
                    // Xóa file ảnh cũ trong cột image nếu có
                    if ($post->image && file_exists(public_path($post->image))) {
                        @unlink(public_path($post->image));
                    }
                }
                
                // Upload từng ảnh và lưu vào bảng post_attachments
                $firstImagePath = null;
                foreach ($images as $index => $image) {
                    $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                    $imagePath = 'uploads/posts/' . $imageName;
                    
                    // Di chuyển file vào thư mục public
                    $image->move(public_path('uploads/posts'), $imageName);
                    
                    // Lưu vào bảng post_attachments
                    \App\Models\PostAttachment::create([
                        'post_id' => $post->id,
                        'file_url' => $imagePath,
                        'file_type' => 'image'
                    ]);
                    
                    // Lưu ảnh đầu tiên vào cột image (để tương thích)
                    if ($index === 0) {
                        $firstImagePath = $imagePath;
                    }
                }
                
                $updateData['image'] = $firstImagePath;
            }

            $post->update($updateData);

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
            'status' => 'required|in:published,hidden,deleted,members_only'
        ]);
        
        // Nếu status là "deleted", thực hiện soft delete thay vì update status
        if ($request->status === 'deleted') {
            $post->delete(); // Soft delete - chuyển vào thùng rác
            return redirect()->back()->with('success', 'Đã chuyển bài viết vào thùng rác!');
        }
        
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
     * Hiển thị chi tiết bình luận
     */
    public function commentsShow($type, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            // Lấy bình luận dựa trên type
            if ($type === 'post') {
                $comment = \App\Models\PostComment::with([
                    'user', 
                    'parent.user', 
                    'post.club',
                    'replies.user'
                ])->findOrFail($id);
                $commentable = $comment->post;
                $commentableType = 'Bài viết';
                $commentableRoute = 'admin.posts.show';
            } elseif ($type === 'event') {
                $comment = \App\Models\EventComment::with([
                    'user', 
                    'parent.user', 
                    'event.club',
                    'replies.user'
                ])->findOrFail($id);
                $commentable = $comment->event;
                $commentableType = 'Sự kiện';
                $commentableRoute = 'admin.events.show';
            } else {
                return redirect()->route('admin.comments')->with('error', 'Loại bình luận không hợp lệ.');
            }

            return view('admin.comments.show', compact('comment', 'commentable', 'commentableType', 'commentableRoute', 'type'));
        } catch (\Exception $e) {
            \Log::error('Error showing comment: ' . $e->getMessage());
            return redirect()->route('admin.comments')->with('error', 'Không tìm thấy bình luận: ' . $e->getMessage());
        }
    }

    /**
     * Delete comment
     */
    public function deleteComment(Request $request, $type, $id)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        try {
            if ($type === 'post') {
                $comment = \App\Models\PostComment::findOrFail($id);
            } else {
                $comment = \App\Models\EventComment::findOrFail($id);
            }
            
            $comment->delete();
            
            return redirect()->back()->with('success', 'Xóa bình luận thành công!');
        } catch (\Exception $e) {
            \Log::error('Error deleting comment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa bình luận: ' . $e->getMessage());
        }
    }

    /**
     * List club join requests with filters and pagination
     */
    public function joinRequestsIndex(Request $request)
    {
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $status = $request->get('status', 'pending');
        $query = \App\Models\ClubJoinRequest::with(['user', 'club', 'reviewer'])->orderByDesc('created_at');
        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }
        if ($request->filled('keyword')) {
            $kw = '%' . trim($request->keyword) . '%';
            $query->whereHas('user', function ($q) use ($kw) {
                $q->where('name', 'like', $kw)
                  ->orWhere('email', 'like', $kw);
            });
        }

        $requests = $query->paginate(20)->withQueryString();
        $clubs = \App\Models\Club::orderBy('name')->get(['id','name']);

        return view('admin.join-requests.index', compact('requests', 'status', 'clubs'));
    }

    /**
     * Approve a single join request
     */
    public function approveJoinRequest($id)
    {
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $req = \App\Models\ClubJoinRequest::with('club')->findOrFail($id);
        if ($req->isApproved()) {
            return back()->with('info', 'Đơn này đã được duyệt trước đó.');
        }
        
        $adminId = session('user_id') ?? 1;
        
        // Lưu thông tin trước khi approve
        $userId = $req->user_id;
        $clubId = $req->club_id;
        
        $req->approve($adminId);
        
        // Load lại relationships sau khi approve
        $req->load(['club', 'user']);
        
        // Gửi thông báo cho người dùng về việc đơn được duyệt
        try {
            $notificationData = [
                'sender_id' => $adminId,
                'title' => 'Đơn tham gia CLB đã được duyệt',
                'message' => "Đơn tham gia CLB \"{$req->club->name}\" của bạn đã được duyệt. Chúc mừng bạn đã trở thành thành viên của CLB!",
            ];
            
            // Thêm related_id và related_type nếu cột tồn tại
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'type')) {
                $notificationData['type'] = 'club';
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_id')) {
                $notificationData['related_id'] = $req->id;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_type')) {
                $notificationData['related_type'] = 'ClubJoinRequest';
            }
            
            $notification = \App\Models\Notification::create($notificationData);
            
            \App\Models\NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_type' => 'user',
                'target_id' => $userId,
            ]);
            
            \App\Models\NotificationRead::create([
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating notification for approved join request: ' . $e->getMessage());
        }
        
        return back()->with('success', 'Đã duyệt đơn tham gia.');
    }

    /**
     * Reject a single join request
     */
    public function rejectJoinRequest(Request $request, $id)
    {
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000'
        ]);

        $req = \App\Models\ClubJoinRequest::with(['user', 'club'])->findOrFail($id);
        if ($req->isRejected()) {
            return back()->with('info', 'Đơn này đã bị từ chối trước đó.');
        }
        
        $adminId = session('user_id') ?? 1;
        $rejectionReason = $request->input('rejection_reason');
        
        // Lưu thông tin trước khi reject
        $userId = $req->user_id;
        $clubId = $req->club_id;
        
        $req->reject($adminId, $rejectionReason);
        
        // Load lại relationships sau khi reject
        $req->load(['club', 'user']);
        
        // Gửi thông báo cho người dùng về việc đơn bị từ chối
        try {
            $message = "Rất tiếc, đơn tham gia CLB \"{$req->club->name}\" của bạn đã bị từ chối.";
            if ($rejectionReason) {
                $message .= " Lý do: {$rejectionReason}";
            } else {
                $message .= " Vui lòng liên hệ với ban quản trị để biết thêm chi tiết.";
            }
            
            $notification = \App\Models\Notification::create([
                'sender_id' => $adminId,
                'title' => 'Đơn tham gia CLB đã bị từ chối',
                'message' => $message,
            ]);
            
            \App\Models\NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_type' => 'user',
                'target_id' => $userId,
            ]);
            
            \App\Models\NotificationRead::create([
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating notification for rejected join request: ' . $e->getMessage());
        }
        
        // Gửi email thông báo
        try {
            \Mail::to($req->user->email)->send(new \App\Mail\ClubJoinRequestRejected($req, $rejectionReason));
        } catch (\Exception $e) {
            \Log::error('Failed to send rejection email: ' . $e->getMessage());
        }
        
        return back()->with('success', 'Đã từ chối đơn tham gia và gửi thông báo.');
    }

    /**
     * Bulk process join requests
     */
    public function bulkJoinRequests(Request $request)
    {
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:club_join_requests,id',
            'action' => 'required|in:approve,reject',
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $reviewer = session('user_id') ?? 1;

        $count = 0;
        foreach (\App\Models\ClubJoinRequest::with(['club', 'user'])->whereIn('id', $ids)->get() as $req) {
            if ($action === 'approve' && !$req->isApproved()) {
                // Lưu thông tin trước khi approve
                $userId = $req->user_id;
                
                $req->approve($reviewer);
                
                // Load lại relationships sau khi approve
                $req->load(['club', 'user']);
                
                // Gửi thông báo cho người dùng về việc đơn được duyệt
                try {
                    $notificationData = [
                        'sender_id' => $reviewer,
                        'title' => 'Đơn tham gia CLB đã được duyệt',
                        'message' => "Đơn tham gia CLB \"{$req->club->name}\" của bạn đã được duyệt. Chúc mừng bạn đã trở thành thành viên của CLB!",
                    ];
                    
                    // Thêm related_id và related_type nếu cột tồn tại
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'type')) {
                        $notificationData['type'] = 'club';
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_id')) {
                        $notificationData['related_id'] = $req->id;
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_type')) {
                        $notificationData['related_type'] = 'ClubJoinRequest';
                    }
                    
                    $notification = \App\Models\Notification::create($notificationData);
                    
                    \App\Models\NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type' => 'user',
                        'target_id' => $userId,
                    ]);
                    
                    \App\Models\NotificationRead::create([
                        'notification_id' => $notification->id,
                        'user_id' => $userId,
                        'is_read' => false,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error creating notification for approved join request (bulk): ' . $e->getMessage());
                }
                
                $count++;
            }
            if ($action === 'reject' && !$req->isRejected()) {
                // Lưu thông tin trước khi reject
                $userId = $req->user_id;
                
                $req->reject($reviewer);
                
                // Load lại relationships sau khi reject
                $req->load(['club', 'user']);
                
                // Gửi thông báo cho người dùng về việc đơn bị từ chối
                try {
                    $notification = \App\Models\Notification::create([
                        'sender_id' => $reviewer,
                        'title' => 'Đơn tham gia CLB đã bị từ chối',
                        'message' => "Rất tiếc, đơn tham gia CLB \"{$req->club->name}\" của bạn đã bị từ chối. Vui lòng liên hệ với ban quản trị để biết thêm chi tiết.",
                    ]);
                    
                    \App\Models\NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type' => 'user',
                        'target_id' => $userId,
                    ]);
                    
                    \App\Models\NotificationRead::create([
                        'notification_id' => $notification->id,
                        'user_id' => $userId,
                        'is_read' => false,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error creating notification for rejected join request (bulk): ' . $e->getMessage());
                }
                
                $count++;
            }
        }

        return back()->with('success', "Đã xử lý {$count} đơn theo hành động '{$action}'.");
    }
    /**
     * Display permissions management page
     */
    public function permissionsManagement(Request $request)
    {
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
            $query->whereHas('clubs', function($q) use ($clubId) {
                $q->where('clubs.id', $clubId);
            });
        }
        
        $users = $query->with(['ownedClubs', 'clubs'])->paginate(10)->appends($request->query());
        $clubs = Club::where('status', 'active')->get();
        $permissions = \App\Models\Permission::all();
        
        return view('admin.permissions.index', compact('users', 'clubs', 'permissions'));
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
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Nếu không có filter nào được chọn, trả về null
        if (!$startDate && !$endDate) {
            return null;
        }
        
        // Nếu chỉ có start_date, dùng start_date làm cả start và end
        if ($startDate && !$endDate) {
            $parsedStart = \Carbon\Carbon::parse($startDate)->startOfDay();
            return [
                'start' => $parsedStart,
                'end' => $parsedStart->copy()->endOfDay()
            ];
        }
        
        // Nếu chỉ có end_date, dùng end_date làm cả start và end
        if (!$startDate && $endDate) {
            $parsedEnd = \Carbon\Carbon::parse($endDate)->endOfDay();
            return [
                'start' => $parsedEnd->copy()->startOfDay(),
                'end' => $parsedEnd
            ];
        }
        
        // Nếu có cả start_date và end_date
        if ($startDate && $endDate) {
            $parsedStart = \Carbon\Carbon::parse($startDate)->startOfDay();
            $parsedEnd = \Carbon\Carbon::parse($endDate)->endOfDay();
            
            // Đảm bảo end_date không nhỏ hơn start_date
            if ($parsedEnd->lt($parsedStart)) {
                // Nếu end_date < start_date, đổi chỗ
                return [
                    'start' => $parsedEnd->copy()->startOfDay(),
                    'end' => $parsedStart->copy()->endOfDay()
                ];
            }
            
            return [
                'start' => $parsedStart,
                'end' => $parsedEnd
            ];
        }
        
        return null;
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
        // Student ID is now auto-extracted from email on the frontend
        // No longer using sequential AB10000 series
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

    public function showClub($club)
    {
        $club = Club::with([
            'field',
            'owner',
            'clubMembers.user' => function($query) {
                $query->select('id', 'name', 'email', 'avatar');
            },
            'posts',
            'events'
        ])->findOrFail($club);

        // Refresh lại relationship để đảm bảo dữ liệu mới nhất
        $club->load('clubMembers');

        // Debug: Log tất cả clubMembers
        \Log::info('Club Members Debug', [
            'club_id' => $club->id,
            'total_members' => $club->clubMembers->count(),
            'members_detail' => $club->clubMembers->map(function($m) {
                return [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'user_name' => $m->user ? $m->user->name : 'NULL',
                    'position' => $m->position,
                    'status' => $m->status
                ];
            })->toArray()
        ]);

        // Define status colors and labels for the view
        $statusColors = [
            'pending' => 'warning',
            'approved' => 'success',
            'active' => 'primary',
            'inactive' => 'secondary',
            'rejected' => 'danger'
        ];
        
        $statusLabels = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'active' => 'Đang hoạt động',
            'inactive' => 'Tạm dừng',
            'rejected' => 'Từ chối'
        ];

        // Get list of users who are NOT already members of this club
        $existingMemberIds = $club->clubMembers->pluck('user_id')->toArray();
        $addableUsers = User::whereNotIn('id', $existingMemberIds)
            ->where('is_admin', false)
            ->orderBy('name')
            ->get();

        // Phân trang danh sách thành viên đã duyệt (10 thành viên/trang)
        // Lấy record mới nhất cho mỗi user_id
        $approvedMembers = ClubMember::where('club_id', $club->id)
            ->whereIn('status', ['approved', 'active'])
            ->with('user:id,name,email,avatar')
            ->select('club_members.*')
            ->whereIn('id', function($query) use ($club) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('club_members')
                    ->where('club_id', $club->id)
                    ->whereIn('status', ['approved', 'active'])
                    ->groupBy('user_id');
            })
            ->orderByRaw("FIELD(position, 'leader', 'vice_president', 'treasurer', 'member') ASC")
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'members_page');

        // Lấy danh sách yêu cầu tham gia CLB đang chờ duyệt
        $pendingJoinRequests = \App\Models\ClubJoinRequest::where('club_id', $club->id)
            ->where('status', 'pending')
            ->with(['user:id,name,email,avatar', 'club:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.clubs.show', compact('club', 'statusColors', 'statusLabels', 'addableUsers', 'approvedMembers', 'pendingJoinRequests'));
    }

    /**
     * Show edit form for a club
     */
    public function editClub($club)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $club = Club::with(['field', 'owner'])->findOrFail($club);
        $fields = Field::all();
        $users = User::where('is_admin', false)->get();
        
        return view('admin.clubs.edit', compact('club', 'fields', 'users'));
    }

    /**
     * Update a club
     */
    public function updateClub(Request $request, $club)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $club = Club::findOrFail($club);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:clubs,slug,' . $club->id,
            'field_id' => 'nullable|exists:fields,id',
            'owner_id' => 'required|exists:users,id',
            'leader_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,approved,active,inactive,rejected',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
            'remove_logo' => 'nullable|boolean',
        ]);
        
        // Validate: one user can be leader/treasurer of only one club
        if ($request->leader_id) {
            // Kiểm tra xem user đã là leader ở CLB khác chưa
            $alreadyLeading = Club::where('leader_id', $request->leader_id)
                ->where('id', '!=', $club->id)
                ->whereNull('deleted_at')
                ->exists();
            if ($alreadyLeading) {
                return back()->with('error', 'Người này đã là Trưởng của một CLB khác.');
            }
            
            // Kiểm tra xem user đã là treasurer/leader trong bảng club_members ở CLB khác chưa
            $existingLeaderOfficer = \App\Models\ClubMember::where('user_id', $request->leader_id)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'treasurer', 'vice_president'])
                ->where('club_id', '!=', $club->id)
                ->whereHas('club', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->first();
                
            if ($existingLeaderOfficer) {
                $existingClub = Club::find($existingLeaderOfficer->club_id);
                return back()->with('error', "Người này đã là cán sự/trưởng ở CLB '{$existingClub->name}'. Một người chỉ được làm cán sự/trưởng ở 1 CLB.");
            }
        }
        
        // Lưu leader_id cũ để xử lý sau
        $oldLeaderId = $club->leader_id;
        
        // Xử lý upload/delete logo
        $updateData = $request->only([
            'name', 'slug', 'field_id', 'owner_id', 'leader_id', 'description', 'status'
        ]);
        
        // Xử lý xóa logo
        if ($request->has('remove_logo') && $request->remove_logo) {
            // Xóa file logo cũ nếu có
            if ($club->logo && file_exists(public_path($club->logo))) {
                @unlink(public_path($club->logo));
            }
            $updateData['logo'] = null;
        }
        
        // Xử lý upload logo mới
        if ($request->hasFile('logo')) {
            try {
                $logo = $request->file('logo');
                $logoName = time() . '_' . $club->slug . '.' . $logo->getClientOriginalExtension();
                $logoPath = 'uploads/clubs/logos/' . $logoName;
                
                // Tạo thư mục nếu chưa tồn tại
                $logoDir = public_path('uploads/clubs/logos');
                if (!file_exists($logoDir)) {
                    mkdir($logoDir, 0755, true);
                }
                
                // Xóa logo cũ nếu có
                if ($club->logo && file_exists(public_path($club->logo))) {
                    @unlink(public_path($club->logo));
                }
                
                // Di chuyển file
                $logo->move($logoDir, $logoName);
                $updateData['logo'] = $logoPath;
            } catch (\Exception $e) {
                return back()->with('error', 'Lỗi upload logo: ' . $e->getMessage())->withInput();
            }
        }
        
        $club->update($updateData);
        
        // Xử lý thay đổi leader - ĐẢM BẢO CHỈ CÓ 1 LEADER
        \Log::info("UpdateClub: oldLeaderId={$oldLeaderId}, newLeaderId={$request->leader_id}");
        
        // QUAN TRỌNG: Chuyển TẤT CẢ leader hiện tại trong CLB này thành member (trước khi set leader mới)
        // Điều này đảm bảo không có nhiều leader cùng lúc
        $allCurrentLeaders = \DB::table('club_members')
            ->where('club_id', $club->id)
            ->where('position', 'leader')
            ->whereIn('status', ['approved', 'active'])
            ->get();
        
        foreach ($allCurrentLeaders as $currentLeader) {
            // Chỉ chuyển nếu không phải leader mới (nếu có)
            if (!$request->leader_id || $currentLeader->user_id != $request->leader_id) {
                \Log::info("Converting existing leader {$currentLeader->user_id} to member in club {$club->id}");
                \DB::table('club_members')
                    ->where('id', $currentLeader->id)
                    ->update(['position' => 'member']);
                
                // Xóa quyền của leader cũ
                \DB::table('user_permissions_club')
                    ->where('user_id', $currentLeader->user_id)
                    ->where('club_id', $club->id)
                    ->delete();
            }
        }
        
        if ($oldLeaderId != $request->leader_id) {
            \Log::info("Leader changed! Processing update...");
            
            // Nếu có leader cũ, chuyển về thành viên và xóa quyền (đã xử lý ở trên, nhưng để chắc chắn)
            if ($oldLeaderId && (!$request->leader_id || $oldLeaderId != $request->leader_id)) {
                \Log::info("Removing old leader {$oldLeaderId} from club {$club->id}");
                \DB::table('club_members')
                    ->where('user_id', $oldLeaderId)
                    ->where('club_id', $club->id)
                    ->update(['position' => 'member']);
                
                \DB::table('user_permissions_club')
                    ->where('user_id', $oldLeaderId)
                    ->where('club_id', $club->id)
                    ->delete();
            }
            
            // Nếu có leader mới, cấp quyền và set position
            if ($request->leader_id) {
                \Log::info("Setting new leader {$request->leader_id} for club {$club->id}");
                
                // Kiểm tra xem user đã là thành viên chưa
                $existingMember = \DB::table('club_members')
                    ->where('user_id', $request->leader_id)
                    ->where('club_id', $club->id)
                    ->first();
                
                if ($existingMember) {
                    \Log::info("User {$request->leader_id} already member, updating position to leader");
                    // Cập nhật position và status
                    $updated = \DB::table('club_members')
                        ->where('user_id', $request->leader_id)
                        ->where('club_id', $club->id)
                        ->update([
                            'position' => 'leader',
                            'status' => 'active',
                        ]);
                    \Log::info("Update result: {$updated} row(s) affected");
                } else {
                    \Log::info("User {$request->leader_id} not member yet, creating new member as leader");
                    // Tạo mới
                    \DB::table('club_members')->insert([
                        'club_id' => $club->id,
                        'user_id' => $request->leader_id,
                        'position' => 'leader',
                        'status' => 'active',
                        'joined_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Xóa quyền cũ và cấp lại tất cả quyền
                \DB::table('user_permissions_club')
                    ->where('user_id', $request->leader_id)
                    ->where('club_id', $club->id)
                    ->delete();
                
                $allPermissions = \App\Models\Permission::all();
                foreach ($allPermissions as $permission) {
                    \DB::table('user_permissions_club')->insert([
                        'user_id' => $request->leader_id,
                        'club_id' => $club->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        return redirect()->route('admin.clubs.show', $club->id)
            ->with('success', 'Cập nhật thông tin câu lạc bộ thành công!');
    }

    /**
     * Remove member from club
     */
    public function removeMember($club, $member)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        \Log::info('removeMember called', ['club' => $club, 'member' => $member]);

        $clubMember = ClubMember::where('id', $member)
            ->where('club_id', $club)
            ->first();

        \Log::info('Member found', ['member' => $clubMember]);

        if (!$clubMember) {
            \Log::error('Member not found');
            return redirect()->back()->with('error', 'Không tìm thấy thành viên.');
        }

        // Không cho phép xóa trưởng CLB (chỉ check position)
        if ($clubMember->position === 'leader') {
            return redirect()->back()->with('error', 'Không thể xóa trưởng câu lạc bộ.');
        }

        // Force delete để xóa cứng (không dùng SoftDelete)
        $clubMember->forceDelete();

        \Log::info('Member deleted successfully');

        return redirect()->back()->with('success', 'Đã xóa thành viên thành công!');
    }

    /**
     * Cập nhật vai trò thành viên
     */
    public function updateMemberRole(Request $request, $club, $member)
    {
        // Kiểm tra đăng nhập admin
        if (!session('logged_in') || !session('is_admin')) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập với tài khoản admin.');
        }

        $request->validate([
            'position' => 'required|in:member,treasurer,vice_president,leader'
        ]);

        $clubMember = ClubMember::where('id', $member)
            ->where('club_id', $club)
            ->first();

        if (!$clubMember) {
            return redirect()->back()->with('error', 'Không tìm thấy thành viên.');
        }

        $clubModel = Club::find($club);
        if (!$clubModel) {
            return redirect()->back()->with('error', 'Không tìm thấy câu lạc bộ.');
        }

        $newPosition = $request->position;
        $oldPosition = $clubMember->position;
        $userId = $clubMember->user_id;

        // Kiểm tra giới hạn vai trò
        if ($newPosition === 'leader') {
            // Kiểm tra xem user đã là leader ở CLB khác chưa
            $alreadyLeading = Club::where('leader_id', $userId)
                ->where('id', '!=', $club)
                ->whereNull('deleted_at')
                ->exists();
            if ($alreadyLeading) {
                return redirect()->back()->with('error', 'Người này đã là Trưởng của một CLB khác.');
            }

            // Kiểm tra xem user đã là treasurer/leader ở CLB khác chưa
            $existingLeaderOfficer = ClubMember::where('user_id', $userId)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'treasurer', 'vice_president'])
                ->where('club_id', '!=', $club)
                ->whereHas('club', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->first();
                
            if ($existingLeaderOfficer) {
                $existingClub = Club::find($existingLeaderOfficer->club_id);
                return redirect()->back()->with('error', "Người này đã là thủ quỹ/phó CLB/trưởng ở CLB '{$existingClub->name}'. Một người chỉ được làm thủ quỹ/phó CLB/trưởng ở 1 CLB.");
            }

            // Chỉ được có 1 Leader
            $existingLeader = ClubMember::where('club_id', $club)
                ->where('position', 'leader')
                ->where('user_id', '!=', $userId)
                ->first();
                
            if ($existingLeader) {
                // Chuyển Leader cũ về thành viên
                DB::table('club_members')
                    ->where('user_id', $existingLeader->user_id)
                    ->where('club_id', $club)
                    ->update(['position' => 'member']);
                
                // Xóa quyền của leader cũ, chỉ giữ xem_bao_cao
                $oldLeaderId = $clubModel->leader_id;
                if ($oldLeaderId) {
                    DB::table('user_permissions_club')
                        ->where('user_id', $oldLeaderId)
                        ->where('club_id', $club)
                        ->delete();
                    
                    $xemBaoCaoPerm = \App\Models\Permission::where('name', 'xem_bao_cao')->first();
                    if ($xemBaoCaoPerm) {
                        DB::table('user_permissions_club')->insert([
                            'user_id' => $oldLeaderId,
                            'club_id' => $club,
                            'permission_id' => $xemBaoCaoPerm->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Cập nhật leader_id trong bảng clubs
            $clubModel->update(['leader_id' => $userId]);

            // Xóa quyền cũ và cấp lại tất cả quyền cho leader mới
            DB::table('user_permissions_club')
                ->where('user_id', $userId)
                ->where('club_id', $club)
                ->delete();
            
            $allPermissions = \App\Models\Permission::all();
            foreach ($allPermissions as $permission) {
                DB::table('user_permissions_club')->insert([
                    'user_id' => $userId,
                    'club_id' => $club,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

        } elseif ($newPosition === 'vice_president') {
            // Kiểm tra xem user đã là vice_president/leader ở CLB khác chưa
            $existingLeaderOfficer = ClubMember::where('user_id', $userId)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'treasurer', 'vice_president'])
                ->where('club_id', '!=', $club)
                ->whereHas('club', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->first();
                
            if ($existingLeaderOfficer) {
                $existingClub = Club::find($existingLeaderOfficer->club_id);
                return redirect()->back()->with('error', "Người này đã là thủ quỹ/phó CLB/trưởng ở CLB '{$existingClub->name}'. Một người chỉ được làm thủ quỹ/phó CLB/trưởng ở 1 CLB.");
            }

            // Được có 2 Vice President
            $vicePresidentCount = ClubMember::where('club_id', $club)
                ->where('position', 'vice_president')
                ->where('user_id', '!=', $userId)
                ->whereIn('status', ['approved', 'active'])
                ->count();
                
            if ($vicePresidentCount >= 2) {
                return redirect()->back()->with('error', 'CLB này đã có đủ 2 phó CLB. Vui lòng bỏ 1 phó CLB trước khi thêm mới.');
            }

            // Xóa quyền cũ và cấp quyền mặc định cho phó CLB
            DB::table('user_permissions_club')
                ->where('user_id', $userId)
                ->where('club_id', $club)
                ->delete();
            
            // Phó CLB có quyền: quan_ly_thanh_vien, tao_su_kien, dang_thong_bao, xem_bao_cao
            $vicePresidentPermissions = \App\Models\Permission::whereIn('name', ['quan_ly_thanh_vien', 'tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'])->get();
            foreach ($vicePresidentPermissions as $permission) {
                DB::table('user_permissions_club')->insert([
                    'user_id' => $userId,
                    'club_id' => $club,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
        } elseif ($newPosition === 'treasurer') {
            // Kiểm tra xem user đã là treasurer/leader ở CLB khác chưa
            $existingLeaderOfficer = ClubMember::where('user_id', $userId)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'treasurer', 'vice_president'])
                ->where('club_id', '!=', $club)
                ->whereHas('club', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->first();
                
            if ($existingLeaderOfficer) {
                $existingClub = Club::find($existingLeaderOfficer->club_id);
                return redirect()->back()->with('error', "Người này đã là thủ quỹ/phó CLB/trưởng ở CLB '{$existingClub->name}'. Một người chỉ được làm thủ quỹ/phó CLB/trưởng ở 1 CLB.");
            }

            // Chỉ được có 1 Treasurer
            $existingTreasurer = ClubMember::where('club_id', $club)
                ->where('position', 'treasurer')
                ->where('user_id', '!=', $userId)
                ->whereIn('status', ['approved', 'active'])
                ->first();
                
            if ($existingTreasurer) {
                // Chuyển thủ quỹ cũ về thành viên
                DB::table('club_members')
                    ->where('user_id', $existingTreasurer->user_id)
                    ->where('club_id', $club)
                    ->update(['position' => 'member']);
                
                // Xóa quyền của thủ quỹ cũ, chỉ giữ xem_bao_cao
                DB::table('user_permissions_club')
                    ->where('user_id', $existingTreasurer->user_id)
                    ->where('club_id', $club)
                    ->delete();
                
                $xemBaoCaoPerm = \App\Models\Permission::where('name', 'xem_bao_cao')->first();
                if ($xemBaoCaoPerm) {
                    DB::table('user_permissions_club')->insert([
                        'user_id' => $existingTreasurer->user_id,
                        'club_id' => $club,
                        'permission_id' => $xemBaoCaoPerm->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Xóa quyền cũ và cấp quyền mặc định cho thủ quỹ
            DB::table('user_permissions_club')
                ->where('user_id', $userId)
                ->where('club_id', $club)
                ->delete();
            
            // Thủ quỹ có quyền: quan_ly_quy, xem_bao_cao
            $treasurerPermissions = \App\Models\Permission::whereIn('name', ['quan_ly_quy', 'xem_bao_cao'])->get();
            foreach ($treasurerPermissions as $permission) {
                DB::table('user_permissions_club')->insert([
                    'user_id' => $userId,
                    'club_id' => $club,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
        } elseif ($newPosition === 'member') {
            // Xóa tất cả quyền cũ, chỉ giữ xem_bao_cao
            DB::table('user_permissions_club')
                ->where('user_id', $userId)
                ->where('club_id', $club)
                ->delete();
            
            $xemBaoCaoPerm = \App\Models\Permission::where('name', 'xem_bao_cao')->first();
            if ($xemBaoCaoPerm) {
                DB::table('user_permissions_club')->insert([
                    'user_id' => $userId,
                    'club_id' => $club,
                    'permission_id' => $xemBaoCaoPerm->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Nếu đang chuyển từ leader sang vai trò khác, xóa leader_id
        if ($oldPosition === 'leader' && $newPosition !== 'leader') {
            $clubModel->update(['leader_id' => null]);
            // Không cần xóa quyền ở đây vì đã được xóa và cấp lại ở các block if/elseif trên
        }

        // Cập nhật position - SỬ DỤNG DB::table để đảm bảo cập nhật trực tiếp vào database
        DB::table('club_members')
            ->where('id', $clubMember->id)
            ->update(['position' => $newPosition]);
        
        // Refresh lại model để đảm bảo dữ liệu mới nhất
        $clubMember->refresh();

        return redirect()->back()->with('success', 'Đã cập nhật vai trò thành viên thành công!');
    }
}
