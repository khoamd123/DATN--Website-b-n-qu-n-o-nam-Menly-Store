<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Club;
use App\Models\Fund;
use App\Models\ClubMember; 
use App\Models\EventMemberEvaluation; 
use App\Models\UserPermissionsClub; 
use App\Models\Field; 
use App\Models\Event;
use App\Models\FundItem;
use App\Models\Post; 

use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
     * Show the form for creating a new club.
     */
    public function createClub()
    {
        $users = User::where('is_admin', false)->get(); 
        $fields = Field::all(); 
        
        return view('admin.clubs.create', compact('users', 'fields'));
    }

    /**
     * Display the specified club.
     */
    public function showClub($club)
    {
        $club = Club::with([
            'field',
            'owner',
            'clubMembers.user', // Eager load user for efficiency
            'posts',
            'events'
        ])->findOrFail($club);

        // Lấy ID của các thành viên đã có trong CLB
        $existingMemberIds = $club->clubMembers->pluck('user_id')->all();

        // Lấy danh sách người dùng chưa phải là thành viên của CLB này
        $addableUsers = User::whereNotIn('id', $existingMemberIds)
                            ->where('is_admin', false) // Tùy chọn: không thêm admin làm thành viên
                            ->orderBy('name')->get();

        return view('admin.clubs.show', compact('club', 'addableUsers'));
    }

    /**
     * Store a newly created club in storage.
     */
    public function storeClub(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clubs,name',
            'description' => 'nullable|string',
            'field_id' => 'required|exists:fields,id',
            'user_id' => 'required|exists:users,id', 
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logoPath = $request->file('logo')->store('club_logos', 'public');
        }

        Club::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'field_id' => $request->field_id,
            'owner_id' => $request->user_id, 
            'logo' => $logoPath ?? 'images/default_club_logo.png', // Cung cấp đường dẫn logo mặc định
            'status' => 'pending', 
        ]);

        return redirect()->route('admin.clubs')->with('success', 'Tạo câu lạc bộ mới thành công!');
    }

    /**
     * Remove the specified club from storage.
     */
    public function deleteClub(Request $request, $id)
    {
        $request->validate([
            'deletion_reason' => 'required|string|max:1000',
        ]);

        $club = Club::findOrFail($id);
        
        // Kiểm tra điều kiện: chỉ cho phép xóa nếu câu lạc bộ đang ở trạng thái 'inactive' (tạm dừng)
        if ($club->status !== 'inactive') {
            return redirect()->back()->with('error', 'Không thể xóa câu lạc bộ khi chưa tạm dừng. Vui lòng chuyển trạng thái sang "Tạm dừng" trước khi xóa.');
        }

        // Lưu lý do xóa trước khi xóa
        $club->update(['deletion_reason' => $request->deletion_reason]);

        // Xóa các bản ghi liên quan trước để tránh lỗi ràng buộc khóa ngoại
        
        // 1. Xóa thành viên câu lạc bộ
        // club_members sử dụng soft deletes, cần forceDelete() để xóa vĩnh viễn khỏi DB
        $club->clubMembers()->forceDelete();

        // 2. Xóa bài viết/tài liệu của câu lạc bộ
        // posts sử dụng soft deletes, cần forceDelete() để xóa vĩnh viễn khỏi DB
        $club->posts()->forceDelete();

        // 3. Xóa các đánh giá thành viên sự kiện liên quan đến câu lạc bộ này
        // event_member_evaluations không sử dụng soft deletes, delete() sẽ xóa vĩnh viễn
        EventMemberEvaluation::where('club_id', $club->id)->delete();

        // 4. Xóa sự kiện của câu lạc bộ
        // Giả định Event không sử dụng soft deletes, delete() sẽ xóa vĩnh viễn.
        // Nếu model Event có sử dụng SoftDeletes, bạn cần dùng $club->events()->forceDelete();
        $club->events()->delete();

        // 5. Xóa các giao dịch quỹ và các mục quỹ liên quan (Fund and FundItem)
        // Lấy tất cả các quỹ liên quan đến câu lạc bộ này
        $funds = Fund::where('club_id', $club->id)->get();
        foreach ($funds as $fund) {
            // Xóa tất cả FundItems liên quan đến quỹ này
            $fund->items()->delete(); 
            // Sau đó xóa quỹ
            $fund->delete(); 
        }

        // 6. Xóa quyền người dùng liên quan đến câu lạc bộ
        // user_permissions_club không sử dụng soft deletes, delete() sẽ xóa vĩnh viễn
        // Assuming UserPermissionsClub model exists and has a club_id foreign key
        UserPermissionsClub::where('club_id', $club->id)->delete();

        // Cuối cùng, xóa câu lạc bộ
        $club->delete();

        return redirect()->route('admin.clubs')->with('success', 'Xóa câu lạc bộ thành công!');
    }

    /**
     * Update club status
     */
    public function updateClubStatus(Request $request, $id)
    {
        $club = Club::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,active,inactive',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:1000',
        ]);
        
        $data = ['status' => $request->status];

        if ($request->status === 'rejected') {
            $data['rejection_reason'] = $request->rejection_reason;
        } else {
            // Xóa lý do từ chối cũ nếu trạng thái không phải là 'rejected'
            $data['rejection_reason'] = null;
        }

        $club->update($data);
        
        return redirect()->back()->with('success', 'Cập nhật trạng thái câu lạc bộ thành công!');
    }

    /**
     * Show the form for editing the specified club.
     *
     * @param  \App\Models\Club  $club
     * @return \Illuminate\View\View
     */
    public function editClub(Club $club)
    {
        $users = User::where('is_admin', false)->get(); // Get all non-admin users for leader selection
        $fields = Field::all(); // Get all fields for field selection
        
        return view('admin.clubs.edit', compact('club', 'users', 'fields'));
    }
    /**
     * Update the specified club in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Club  $club
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateClub(Request $request, Club $club)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clubs,name,' . $club->id,
            'description' => 'nullable|string',
            'field_id' => 'required|exists:fields,id',
            'leader_id' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,approved,active,inactive,rejected',
        ]);

        $oldLeaderId = $club->leader_id;

        $club->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name), // Update slug if name changes
            'description' => $request->description,
            'field_id' => $request->field_id,
            'leader_id' => $request->leader_id,
            'status' => $request->status,
        ]);

        // --- Synchronize leader_id with club_members table ---

        // If a new leader is selected
        if ($request->leader_id) {
            // Ensure the new leader is a 'chunhiem' in club_members
            ClubMember::updateOrCreate(
                ['club_id' => $club->id, 'user_id' => $request->leader_id],
                ['role_in_club' => 'chunhiem', 'status' => 'approved', 'joined_at' => now()]
            );
        }

        // If the leader has changed or been removed
        if ($oldLeaderId && $oldLeaderId !== $request->leader_id) {
            // Find the old leader's ClubMember record
            $oldLeaderMember = ClubMember::where('club_id', $club->id)
                                         ->where('user_id', $oldLeaderId)
                                         ->first();
            if ($oldLeaderMember && $oldLeaderMember->role_in_club === 'chunhiem') {
                // Demote the old leader to a regular member if they are not the new leader
                $oldLeaderMember->update(['role_in_club' => 'thanhvien']);
            }
        }

        return redirect()->route('admin.clubs.show', $club->id)
                         ->with('success', 'Cập nhật câu lạc bộ thành công!');
    }

    /**
     * Add a new member to a club.
     */
    public function addMember(Request $request, Club $club)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_in_club' => 'required|string|in:chunhiem,thanhvien', // Adjust roles as needed
        ]);

        // Kiểm tra xem người dùng đã là thành viên chưa
        $isMember = ClubMember::where('club_id', $club->id)
                              ->where('user_id', $request->user_id)
                              ->exists();

        if ($isMember) {
            return back()->with('error', 'Người dùng này đã là thành viên của câu lạc bộ.');
        }

        // Thêm thành viên mới với trạng thái đã duyệt
        $club->clubMembers()->create($request->all() + ['status' => 'approved', 'joined_at' => now()]);

        return redirect()->route('admin.clubs.show', $club->id)->with('success', 'Đã thêm thành viên mới vào câu lạc bộ thành công!');
    }

    /**
     * Approve a pending club member.
     */
    public function approveMember(Request $request, Club $club, ClubMember $member)
    {
        if ($member->club_id !== $club->id) {
            return back()->with('error', 'Thành viên không thuộc câu lạc bộ này.');
        }

        $member->update(['status' => 'approved', 'joined_at' => now()]);

        return redirect()->route('admin.clubs.show', $club->id)->with('success', 'Đã duyệt thành viên ' . $member->user->name . '.');
    }

    /**
     * Reject a pending club member.
     */
    public function rejectMember(Request $request, Club $club, ClubMember $member)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($member->club_id !== $club->id || $member->status !== 'pending') {
            return back()->with('error', 'Không thể từ chối thành viên này.');
        }

        // Cập nhật trạng thái và lý do từ chối thay vì xóa
        $member->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.clubs.show', $club->id)->with('success', 'Đã từ chối yêu cầu tham gia của ' . $member->user->name . '.');
    }

    /**
     * Remove a member from a club.
     */
    public function removeMember(Request $request, Club $club, ClubMember $member)
    {
        $request->validate(['deletion_reason' => 'required|string|max:500']);

        if ($member->club_id !== $club->id) {
            return back()->with('error', 'Thành viên không thuộc câu lạc bộ này.');
        }

        if ($member->user_id === $club->owner_id) {
            return back()->with('error', 'Không thể xóa chủ sở hữu câu lạc bộ.');
        }

        $member->forceDelete(); // Or $member->delete() if you use soft deletes on club_members

        return redirect()->route('admin.clubs.show', $club->id)->with('success', 'Đã xóa thành viên ' . $member->user->name . ' khỏi câu lạc bộ.');
    }

    /**
     * Handle bulk actions for members (approve/reject).
     */
    public function bulkUpdateMembers(Request $request, Club $club)
    {
        // This is a placeholder. You'll need to implement the logic based on your `handleBulkAction` JavaScript function.
        // For example:
        // $memberIds = $request->input('member_ids', []);
        // $action = $request->input('action');
        return back()->with('info', 'Chức năng xử lý hàng loạt đang được phát triển.');
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
