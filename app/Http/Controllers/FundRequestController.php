<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\Event;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FundRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = FundRequest::with(['event', 'club', 'creator', 'approver']);

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('event', function($eventQuery) use ($search) {
                      $eventQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('club', function($clubQuery) use ($search) {
                      $clubQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo CLB
        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);
        $clubs = Club::all();
        
        // Lấy danh sách quỹ cho các CLB có sự kiện
        $funds = \App\Models\Fund::whereHas('club', function($q) {
            $q->where('status', 'active');
        })->with('club')->get();

        return view('admin.fund-requests.index', compact('requests', 'clubs', 'funds'));
    }

    public function create()
    {
        $events = Event::orderBy('start_time', 'desc')->get();
        $clubs = Club::all();
        
        return view('admin.fund-requests.create', compact('events', 'clubs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requested_amount' => 'required|numeric|min:0',
            'event_id' => 'required|exists:events,id',
            'club_id' => 'required|exists:clubs,id',
            'expense_items' => 'nullable|array',
            'expense_items.*.item' => 'required_with:expense_items|string|max:255',
            'expense_items.*.amount' => 'required_with:expense_items|numeric|min:0',
        ]);

        $data = $request->all();
        
        // Lấy user ID từ Auth (admin đăng nhập)
        $userId = Auth::id();
        if (!$userId) {
            // Nếu không có user đăng nhập, lấy từ session
            $userId = session('user_id');
        }
        if (!$userId) {
            return redirect()->back()
                ->with('error', 'Không thể xác định người tạo yêu cầu. Vui lòng đăng nhập lại.')
                ->withInput();
        }
        $data['created_by'] = $userId;
        $data['status'] = 'pending';

        // Xử lý tài liệu hỗ trợ
        if ($request->hasFile('supporting_documents')) {
            $documents = [];
            $uploadPath = public_path('storage/fund-requests');
            
            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            foreach ($request->file('supporting_documents') as $index => $document) {
                // Bỏ qua file rỗng hoặc không hợp lệ
                if (!$document || !$document->isValid() || $document->getSize() == 0) {
                    continue;
                }
                
                try {
                    $filename = time() . '_' . $index . '_' . $document->getClientOriginalName();
                    $document->move($uploadPath, $filename);
                    $documents[] = 'fund-requests/' . $filename;
                } catch (\Exception $e) {
                    // Tiếp tục với file khác thay vì dừng lại
                    continue;
                }
            }
            $data['supporting_documents'] = $documents;
        }

        try {
            $fundRequest = FundRequest::create($data);
            
            // Tạo thông báo cho admin
            $admins = \App\Models\User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                $notification = \App\Models\Notification::create([
                    'sender_id' => $userId,
                    'type' => 'fund_request',
                    'title' => 'Yêu cầu cấp kinh phí mới',
                    'message' => "Có yêu cầu cấp kinh phí mới: \"{$fundRequest->title}\" từ CLB " . ($fundRequest->club->name ?? '') . ". Số tiền: " . number_format($fundRequest->requested_amount, 0, ',', '.') . " VNĐ.",
                    'related_id' => $fundRequest->id,
                    'related_type' => 'FundRequest',
                ]);
                
                \App\Models\NotificationTarget::create([
                    'notification_id' => $notification->id,
                    'target_type' => 'user',
                    'target_id' => $admin->id,
                ]);
            }
            
            return redirect()->route('admin.fund-requests')
                ->with('success', 'Yêu cầu cấp kinh phí đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Lỗi tạo yêu cầu: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(FundRequest $fundRequest)
    {
        $fundRequest->load(['event', 'club', 'creator', 'approver']);
        return view('admin.fund-requests.show', compact('fundRequest'));
    }

    public function edit(FundRequest $fundRequest)
    {
        if ($fundRequest->status !== 'pending') {
            return redirect()->route('admin.fund-requests.show', $fundRequest->id)
                ->with('error', 'Chỉ có thể chỉnh sửa yêu cầu đang chờ duyệt!');
        }

        $events = Event::where('status', 'active')->get();
        $clubs = Club::all();
        return view('admin.fund-requests.edit', compact('fundRequest', 'events', 'clubs'));
    }

    public function update(Request $request, FundRequest $fundRequest)
    {
        if ($fundRequest->status !== 'pending') {
            return redirect()->route('admin.fund-requests.show', $fundRequest->id)
                ->with('error', 'Chỉ có thể chỉnh sửa yêu cầu đang chờ duyệt!');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requested_amount' => 'required|numeric|min:0',
            'event_id' => 'required|exists:events,id',
            'club_id' => 'required|exists:clubs,id',
            'expense_items' => 'nullable|array',
            'expense_items.*.item' => 'required_with:expense_items|string|max:255',
            'expense_items.*.amount' => 'required_with:expense_items|numeric|min:0',
        ]);

        $data = $request->all();

        // Xử lý tài liệu hỗ trợ mới
        if ($request->hasFile('supporting_documents')) {
            $documents = $fundRequest->supporting_documents ?? [];
            $uploadPath = public_path('storage/fund-requests');
            
            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            foreach ($request->file('supporting_documents') as $index => $document) {
                if ($document && $document->isValid() && $document->getSize() > 0) {
                    $filename = time() . '_' . $index . '_' . $document->getClientOriginalName();
                    $document->move($uploadPath, $filename);
                    $documents[] = 'fund-requests/' . $filename;
                }
            }
            $data['supporting_documents'] = $documents;
        }

        $fundRequest->update($data);

        return redirect()->route('admin.fund-requests.show', $fundRequest->id)
            ->with('success', 'Yêu cầu cấp kinh phí đã được cập nhật thành công!');
    }

    public function approve(Request $request, FundRequest $fundRequest)
    {
        if ($fundRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Yêu cầu này không thể duyệt!');
        }

        $request->validate([
            'approved_amount' => 'required|numeric|min:0|max:' . $fundRequest->requested_amount,
            'approval_notes' => 'nullable|string|max:1000'
        ]);

        // Get user ID from session or Auth
        $userId = session('user_id') ?? Auth::id();
        if (!$userId) {
            $adminUser = \App\Models\User::where('is_admin', 1)->first();
            $userId = $adminUser ? $adminUser->id : 1;
        }
        
        $fundRequest->approve(
            $userId,
            $request->approved_amount,
            $request->approval_notes
        );

        // Tạo thông báo cho người tạo yêu cầu
        try {
            $notification = \App\Models\Notification::create([
                'sender_id' => $userId,
                'type' => 'fund_request',
                'title' => 'Yêu cầu cấp kinh phí đã được duyệt',
                'message' => "Yêu cầu cấp kinh phí \"{$fundRequest->title}\" của bạn đã được duyệt. Số tiền được duyệt: " . number_format($request->approved_amount, 0, ',', '.') . " VNĐ." . ($request->approval_notes ? " Ghi chú: {$request->approval_notes}" : ''),
                'related_id' => $fundRequest->id,
                'related_type' => 'FundRequest',
            ]);
            
            \App\Models\NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_type' => 'user',
                'target_id' => $fundRequest->created_by,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating notification for approved fund request: ' . $e->getMessage());
        }

        // Tự động tạo giao dịch thu tiền vào quỹ của CLB
        if ($fundRequest->club_id && $request->approved_amount > 0) {
            try {
                // Tìm quỹ của CLB
                $club = \App\Models\Club::find($fundRequest->club_id);
                if ($club) {
                    $fund = \App\Models\Fund::where('club_id', $club->id)->first();
                    
                    // Nếu chưa có quỹ, tự động tạo quỹ mới cho CLB
                    if (!$fund) {
                        $fund = \App\Models\Fund::create([
                            'club_id' => $club->id,
                            'name' => 'Quỹ ' . $club->name,
                            'description' => 'Quỹ tự động được tạo từ hệ thống',
                            'initial_amount' => 0,
                            'current_amount' => 0,
                            'status' => 'active',
                            'source' => 'Nhà trường'
                        ]);
                    }
                    
                    if ($fund) {
                        // Tạo giao dịch thu tiền
                        \App\Models\FundTransaction::create([
                            'fund_id' => $fund->id,
                            'event_id' => $fundRequest->event_id,
                            'type' => 'income',
                            'title' => 'Cấp kinh phí từ nhà trường: ' . $fundRequest->title,
                            'description' => 'Yêu cầu cấp kinh phí thành công',
                            'amount' => $request->approved_amount,
                            'transaction_date' => now(),
                            'source' => 'Nhà trường',
                            'status' => 'approved',
                            'created_by' => $fundRequest->created_by, // Người tạo yêu cầu ban đầu
                            'approved_by' => $userId, // Người duyệt yêu cầu
                            'approved_at' => now()
                        ]);
                        
                        // Cập nhật số dư quỹ
                        $fund->updateCurrentAmount();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error creating fund transaction: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.fund-requests.show', $fundRequest->id)
            ->with('success', 'Yêu cầu cấp kinh phí đã được duyệt thành công!');
    }

    public function reject(Request $request, FundRequest $fundRequest)
    {
        if ($fundRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Yêu cầu này không thể từ chối!');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        // Get user ID from session or Auth
        $userId = session('user_id') ?? Auth::id();
        if (!$userId) {
            $adminUser = \App\Models\User::where('is_admin', 1)->first();
            $userId = $adminUser ? $adminUser->id : 1;
        }
        
        $fundRequest->reject($userId, $request->rejection_reason);

        // Tạo thông báo cho người tạo yêu cầu
        try {
            $notification = \App\Models\Notification::create([
                'sender_id' => $userId,
                'type' => 'fund_request',
                'title' => 'Yêu cầu cấp kinh phí đã bị từ chối',
                'message' => "Yêu cầu cấp kinh phí \"{$fundRequest->title}\" của bạn đã bị từ chối. Lý do: {$request->rejection_reason}",
                'related_id' => $fundRequest->id,
                'related_type' => 'FundRequest',
            ]);
            
            \App\Models\NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_type' => 'user',
                'target_id' => $fundRequest->created_by,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating notification for rejected fund request: ' . $e->getMessage());
        }

        return redirect()->route('admin.fund-requests.show', $fundRequest->id)
            ->with('success', 'Yêu cầu cấp kinh phí đã bị từ chối!');
    }

    public function destroy(FundRequest $fundRequest)
    {
        // Admin không được phép xóa yêu cầu cấp kinh phí
        return redirect()->back()->with('error', 'Bạn không có quyền xóa yêu cầu cấp kinh phí!');
    }

    public function batchApproval()
    {
        try {
            $pendingRequests = FundRequest::where('status', 'pending')
                ->with(['event', 'club', 'creator'])
                ->orderBy('created_at', 'asc')
                ->get();
            
            $totalRequestedAmount = $pendingRequests->sum('requested_amount');
            
            return view('admin.fund-requests.batch-approval', compact('pendingRequests', 'totalRequestedAmount'));
        } catch (\Exception $e) {
            Log::error('Batch approval error: ' . $e->getMessage());
            return redirect()->route('admin.fund-requests')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function processBatchApproval(Request $request)
    {
        $request->validate([
            'total_approved_amount' => 'required|numeric|min:0',
            'approval_notes' => 'nullable|string|max:1000',
            'requests' => 'required|array|min:1',
            'requests.*.id' => 'required|exists:fund_requests,id',
            'requests.*.approved_amount' => 'required|numeric|min:0'
        ]);

        $totalApproved = $request->total_approved_amount;
        $requests = $request->requests;
        
        // Tính tổng số tiền được phân bổ
        $allocatedTotal = collect($requests)->sum('approved_amount');
        
        if ($allocatedTotal > $totalApproved) {
            return redirect()->back()
                ->with('error', 'Tổng số tiền phân bổ (' . number_format($allocatedTotal) . ' VNĐ) không được vượt quá số tiền duyệt (' . number_format($totalApproved) . ' VNĐ)');
        }

        $approvedCount = 0;
        $rejectedCount = 0;

        foreach ($requests as $requestData) {
            $fundRequest = FundRequest::find($requestData['id']);
            $approvedAmount = $requestData['approved_amount'];
            
            if ($approvedAmount > 0) {
                // Duyệt (toàn bộ hoặc một phần)
                $status = $approvedAmount >= $fundRequest->requested_amount ? 'approved' : 'partially_approved';
                // Get user ID from session or Auth
                $userId = session('user_id') ?? Auth::id();
                if (!$userId) {
                    $adminUser = \App\Models\User::where('is_admin', 1)->first();
                    $userId = $adminUser ? $adminUser->id : 1;
                }
                
                $fundRequest->update([
                    'status' => $status,
                    'approved_amount' => $approvedAmount,
                    'approval_notes' => $request->approval_notes,
                    'approved_by' => $userId,
                    'approved_at' => now()
                ]);
                
                // Tự động tạo giao dịch thu tiền vào quỹ của CLB
                if ($fundRequest->club_id && $approvedAmount > 0) {
                    try {
                        $club = \App\Models\Club::find($fundRequest->club_id);
                        if ($club) {
                            $fund = \App\Models\Fund::where('club_id', $club->id)->first();
                            
                            // Nếu chưa có quỹ, tự động tạo quỹ mới cho CLB
                            if (!$fund) {
                                $fund = \App\Models\Fund::create([
                                    'club_id' => $club->id,
                                    'name' => 'Quỹ ' . $club->name,
                                    'description' => 'Quỹ tự động được tạo từ hệ thống',
                                    'initial_amount' => 0,
                                    'current_amount' => 0,
                                    'status' => 'active',
                                    'source' => 'Nhà trường'
                                ]);
                            }
                            
                            if ($fund) {
                                // Tạo giao dịch thu tiền
                                // created_by là người tạo yêu cầu ban đầu, không phải người duyệt
                                \App\Models\FundTransaction::create([
                                    'fund_id' => $fund->id,
                                    'event_id' => $fundRequest->event_id,
                                    'type' => 'income',
                                    'title' => 'Cấp kinh phí từ nhà trường: ' . $fundRequest->title,
                                    'description' => 'Yêu cầu cấp kinh phí thành công',
                                    'amount' => $approvedAmount,
                                    'transaction_date' => now(),
                                    'source' => 'Nhà trường',
                                    'status' => 'approved',
                                    'created_by' => $fundRequest->created_by, // Người tạo yêu cầu ban đầu
                                    'approved_by' => $userId, // Người duyệt yêu cầu
                                    'approved_at' => now()
                                ]);
                                
                                // Cập nhật số dư quỹ
                                $fund->updateCurrentAmount();
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Error creating fund transaction: ' . $e->getMessage());
                    }
                }
                
                // Tạo thông báo cho người tạo yêu cầu
                try {
                    $statusText = $status === 'approved' ? 'đã được duyệt' : 'đã được duyệt một phần';
                    $notification = \App\Models\Notification::create([
                        'sender_id' => $userId,
                        'type' => 'fund_request',
                        'title' => "Yêu cầu cấp kinh phí {$statusText}",
                        'message' => "Yêu cầu cấp kinh phí \"{$fundRequest->title}\" của bạn {$statusText}. Số tiền được duyệt: " . number_format($approvedAmount, 0, ',', '.') . " VNĐ." . ($request->approval_notes ? " Ghi chú: {$request->approval_notes}" : ''),
                        'related_id' => $fundRequest->id,
                        'related_type' => 'FundRequest',
                    ]);
                    
                    \App\Models\NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type' => 'user',
                        'target_id' => $fundRequest->created_by,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating notification for batch approved fund request: ' . $e->getMessage());
                }
                
                $approvedCount++;
            } else {
                // Từ chối
                // Get user ID from session or Auth (use same $userId from above if already set)
                if (!isset($userId)) {
                    $userId = session('user_id') ?? Auth::id();
                    if (!$userId) {
                        $adminUser = \App\Models\User::where('is_admin', 1)->first();
                        $userId = $adminUser ? $adminUser->id : 1;
                    }
                }
                
                $fundRequest->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Không được duyệt trong đợt duyệt hàng loạt này',
                    'approved_by' => $userId,
                    'approved_at' => now()
                ]);
                
                // Tạo thông báo cho người tạo yêu cầu
                try {
                    $notification = \App\Models\Notification::create([
                        'sender_id' => $userId,
                        'type' => 'fund_request',
                        'title' => 'Yêu cầu cấp kinh phí đã bị từ chối',
                        'message' => "Yêu cầu cấp kinh phí \"{$fundRequest->title}\" của bạn đã bị từ chối trong đợt duyệt hàng loạt. Lý do: Không được duyệt trong đợt duyệt hàng loạt này.",
                        'related_id' => $fundRequest->id,
                        'related_type' => 'FundRequest',
                    ]);
                    
                    \App\Models\NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type' => 'user',
                        'target_id' => $fundRequest->created_by,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating notification for batch rejected fund request: ' . $e->getMessage());
                }
                
                $rejectedCount++;
            }
        }

        $message = "Đã xử lý " . count($requests) . " yêu cầu: ";
        $message .= $approvedCount . " được duyệt, " . $rejectedCount . " bị từ chối";
        
        return redirect()->route('admin.fund-requests')
            ->with('success', $message);
    }
}
