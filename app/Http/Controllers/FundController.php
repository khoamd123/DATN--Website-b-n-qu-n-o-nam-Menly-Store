<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\FundTransaction;
use App\Models\FundRequest;
use App\Models\Club;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FundController extends Controller
{
    /**
     * Hiển thị danh sách quỹ
     */
    public function index(Request $request)
    {
        $query = Fund::with(['club', 'creator']);

        // Tìm kiếm theo CLB
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('club', function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%');
            })->orWhere('description', 'like', '%' . $searchTerm . '%');
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo CLB
        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        $funds = $query->orderBy('created_at', 'desc')->paginate(20);
        $clubs = Club::all();

        return view('admin.funds.index', compact('funds', 'clubs'));
    }

    /**
     * Hiển thị form tạo quỹ mới
     */
    public function create()
    {
        $clubs = Club::all();
        return view('admin.funds.create', compact('clubs'));
    }

    /**
     * Lưu quỹ mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'initial_amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'club_id' => 'nullable|exists:clubs,id',
        ]);

        // Lấy user_id từ session hoặc tìm admin đầu tiên
        $createdBy = session('user_id');
        if (!$createdBy) {
            $adminUser = \App\Models\User::where('is_admin', true)->first();
            $createdBy = $adminUser ? $adminUser->id : 1;
        }

        // Nếu có CLB nhưng không có tên quỹ, dùng tên mặc định
        $fundName = $request->name;
        if ($request->club_id && !$fundName) {
            $club = Club::find($request->club_id);
            $fundName = ''; // Để trống để tự động tạo tên từ CLB
        }

        // Xác định nguồn tiền từ request
        $source = null;
        if (($request->initial_amount ?? 0) > 0 && $request->filled('source')) {
            $source = $request->source; // Lấy từ dropdown
        }

        $fund = Fund::create([
            'name' => $fundName ?: '',
            'description' => $request->description,
            'initial_amount' => $request->initial_amount ?? 0,
            'current_amount' => $request->initial_amount ?? 0,
            'source' => $source,
            'club_id' => $request->club_id,
            'created_by' => $createdBy,
        ]);

        return redirect()->route('admin.funds')->with('success', 'Tạo quỹ thành công!');
    }

    /**
     * Hiển thị chi tiết quỹ
     */
    public function show(Request $request, Fund $fund)
    {
        $fund->load(['club', 'creator', 'transactions.creator', 'transactions.approver']);
        
        // Thống kê TRƯỚC KHI cập nhật để tránh loop
        $stats = [
            'total_income' => $fund->getTotalIncome(),
            'total_expense' => $fund->getTotalExpense(),
            'pending_transactions' => $fund->transactions()->where('status', 'pending')->count(),
            'recent_transactions' => $fund->transactions()->latest()->limit(10)->get(),
        ];
        
        // Chỉ cập nhật nếu có refresh parameter
        if ($request->has('refresh')) {
            $oldAmount = $fund->current_amount;
            $fund->updateCurrentAmount();
            $fund->refresh(); // Reload from database
            
            return view('admin.funds.show', compact('fund', 'stats'))
                ->with('success', 'Đã cập nhật số tiền hiện tại: ' . number_format($fund->current_amount) . ' VNĐ');
        }

        return view('admin.funds.show', compact('fund', 'stats'));
    }

    /**
     * Hiển thị form chỉnh sửa quỹ
     */
    public function edit(Fund $fund)
    {
        $clubs = Club::all();
        return view('admin.funds.edit', compact('fund', 'clubs'));
    }

    /**
     * Cập nhật quỹ
     */
    public function update(Request $request, Fund $fund)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,closed',
            'club_id' => 'nullable|exists:clubs,id',
        ]);

        $fund->update($request->all());

        return redirect()->route('admin.funds.show', $fund)->with('success', 'Cập nhật quỹ thành công!');
    }

    /**
     * Xóa quỹ
     */
    public function destroy(Fund $fund)
    {
        // Kiểm tra xem có giao dịch nào không
        if ($fund->transactions()->count() > 0) {
            return back()->with('error', 'Không thể xóa quỹ có giao dịch!');
        }

        $fund->delete();
        return redirect()->route('admin.funds')->with('success', 'Xóa quỹ thành công!');
    }
}
