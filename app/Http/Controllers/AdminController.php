<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Club;
use App\Models\Fund;
use App\Models\FundItem;
use App\Models\Event;
use App\Models\Post;;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        // Thống kê tổng quan
        $totalUsers = User::count();
        $totalClubs = Club::count();
        $totalEvents = Event::count();
        $totalPosts = Post::count();
        
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

        return view('admin.dashboard', compact(
            'totalUsers', 
            'totalClubs', 
            'totalEvents', 
            'totalPosts',
            'newUsers',
            'newClubs',
            'upcomingEvents'
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
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Update user admin status
     */
    public function updateUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'is_admin' => 'required|boolean'
        ]);
        
        $user->update([
            'is_admin' => $request->is_admin
        ]);
        
        return redirect()->back()->with('success', 'Cập nhật quyền người dùng thành công!');
    }

    /**
     * Display clubs management page
     */
    public function clubs(Request $request)
    {
        $query = Club::with(['field', 'owner']);
        
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
        
        $documents = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.learning-materials.index', compact('documents', 'clubs'));
    }

    /**
     * Display fund management page
     */
    public function fundManagement(Request $request)
    {
        $query = Fund::with(['club', 'user']);
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
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
        
        // Tính tổng quỹ: thu cộng, chi trừ
        $totalFunds = Fund::where(function($q) use ($request) {
            if ($request->filled('search')) {
                $q->where('title','like',"%{$request->search}%")
                ->orWhere('content','like',"%{$request->search}%");
            }
            if ($request->filled('club_id')) {
                $q->where('club_id', $request->club_id);
            }
        })->get()->reduce(function($carry, $f) {
            return $carry + (($f->transaction_type === 'thu') ? (float)$f->amount : - (float)$f->amount);
        }, 0);
        
        return view('admin.fund-management.index', compact('funds', 'clubs', 'totalFunds'));
    }

    public function storeFund(Request $request)
        {
            $request->validate([
            'title' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:thu,chi',
            'club_id' => 'required|exists:clubs,id',
        ]);

            $items = $request->input('items', [['amount' => $request->input('items.0.amount',0),'description' => $request->input('title')]]);
            $total = array_sum(array_map(fn($it)=>(float)($it['amount'] ?? 0), $items));

            $fund = Fund::create([
                'title' => $request->title,
                'amount' => $total,
                'transaction_type' => $request->transaction_type,
                'club_id' => $request->club_id,
                'user_id' => auth()->id(),
                'content' => $request->content,
                'status' => 'pending',
            ]);

            foreach ($request->input('items', []) as $it) {
                FundItem::create([
                    'fund_id' => $fund->id,
                    'description' => $it['description'] ?? $fund->title,
                    'amount' => $it['amount'] ?? 0,
                ]);
            }

            return redirect()->route('admin.fund-management')->with('success', 'Thêm giao dịch quỹ thành công!');
        }

    public function approveFund(Request $request, Fund $fund)
    {
        // server-side kiểm tra số mục được duyệt không vượt quá 6
        $approvedIds = $request->input('approved_items', []);
        if (count($approvedIds) > 6) {
            return back()->withErrors(['approved_items' => 'Chỉ được duyệt tối đa 6 mục.']);
        }

        $request->validate([
            'voucher' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'approved_items' => 'array',
            'rejected_items' => 'array',
            'rejection_reasons' => 'array',
            'approval_note' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('voucher')) {
            $path = $request->file('voucher')->store('vouchers','public');
            $fund->voucher_path = $path;
        }

        $approvedSum = 0;
        $approvedItems = $request->input('approved_items', []);
        $rejectedItems = $request->input('rejected_items', []);
        $rejectionReasons = $request->input('rejection_reasons', []);

        foreach ($fund->items as $item) {
            if (in_array($item->id, $approvedItems)) {
                $item->status = 'approved';
                $approvedSum += (float) $item->amount;
                $item->rejection_reason = null;
            } elseif (in_array($item->id, $rejectedItems)) {
                $item->status = 'rejected';
                $item->rejection_reason = $rejectionReasons[$item->id] ?? null;
            } else {
                $item->status = 'pending';
                $item->rejection_reason = null;
            }
            $item->save();
        }

        $fund->approved_by = auth()->id();
        $fund->approved_at = now();
        $fund->approved_amount = $approvedSum;
        $fund->approval_note = $request->approval_note;
        $fund->status = 'approved';
        $fund->save();

        return redirect()->route('admin.fund-management')->with('success','Yêu cầu đã được xử lý.');
    }

    public function showFund(Fund $fund)
    {
        $fund->load(['items','club','user','items']);
        return view('admin.fund-management.show', compact('fund'));
    }

    public function fundJson(Fund $fund)
    {
        $fund->load(['items','club','user']);
        return response()->json($fund);
    }

    /**
     * Show the form for editing the specified fund.
     */
    public function editFund(Fund $fund)
    {
        $clubs = Club::where('status', 'active')->get();
        $fund->load('items'); // Tải các mục để hiển thị
        return view('admin.fund-management.edit', compact('fund', 'clubs'));
    }

    /**
     * Update the specified fund in storage.
     */
    public function updateFund(Request $request, Fund $fund)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'transaction_type' => 'required|in:thu,chi',
            'club_id' => 'required|exists:clubs,id',
            'content' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.amount' => 'required|numeric|min:0.01',
        ]);

        // Lấy thông tin mục duy nhất từ request
        $itemData = array_values($request->input('items', []))[0];
        $totalAmount = (float)($itemData['amount'] ?? 0);

        // Cập nhật thông tin chính của quỹ
        $fund->update([
            'title' => $request->title,
            'transaction_type' => $request->transaction_type,
            'club_id' => $request->club_id,
            'content' => $request->content,
            'amount' => $totalAmount, // Cập nhật tổng tiền
        ]);

        // Xóa tất cả các mục cũ để đảm bảo chỉ có một mục mới được lưu
        $fund->items()->delete();

        // Tạo lại mục duy nhất với thông tin mới
        FundItem::create([
            'fund_id' => $fund->id,
            'description' => $request->title, // Sử dụng tiêu đề của quỹ làm mô tả cho mục
            'amount' => $totalAmount,
            'status' => 'pending', // Reset trạng thái về pending khi chỉnh sửa
        ]);

        return redirect()->route('admin.fund-management.show', $fund->id)->with('success', 'Cập nhật giao dịch quỹ thành công!');
    }

    /**
     * Delete a fund and its items.
     */
    public function destroyFund(Fund $fund)
    {
        // Xóa các mục liên quan trước để đảm bảo toàn vẹn dữ liệu
        $fund->items()->delete();
        // Xóa bản ghi quỹ
        $fund->delete();

        return redirect()->route('admin.fund-management')->with('success', 'Xóa giao dịch quỹ thành công!');
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
        
        $events = $query->orderBy('start_time', 'asc')->paginate(20);
        $clubs = Club::where('status', 'active')->get();
        
        return view('admin.plans-schedule.index', compact('events', 'clubs'));
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
