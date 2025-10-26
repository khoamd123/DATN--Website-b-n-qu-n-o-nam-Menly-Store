<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\FundTransaction;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class FundTransactionController extends Controller
{
    /**
     * Hiển thị danh sách giao dịch của quỹ
     */
    public function index(Request $request, Fund $fund)
    {
        $query = $fund->transactions()->with(['creator', 'approver', 'event']);

        // Tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('category', 'like', '%' . $searchTerm . '%');
            });
        }

        // Lọc theo loại
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo danh mục
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Lọc theo ngày
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);
        $events = Event::where('status', 'active')->get();

        // Thống kê
        $stats = [
            'total_income' => $fund->getTotalIncome(),
            'total_expense' => $fund->getTotalExpense(),
            'pending_count' => $fund->transactions()->where('status', 'pending')->count(),
            'approved_count' => $fund->transactions()->where('status', 'approved')->count(),
            'rejected_count' => $fund->transactions()->where('status', 'rejected')->count(),
        ];

        return view('admin.funds.transactions.index', compact('fund', 'transactions', 'events', 'stats'));
    }

    /**
     * Hiển thị form tạo giao dịch mới
     */
    public function create(Fund $fund)
    {
        // Lấy sự kiện liên quan đến CLB (nếu quỹ có CLB)
        if ($fund->club_id) {
            $events = Event::with('club')
                          ->where(function($query) use ($fund) {
                              $query->where('club_id', $fund->club_id)
                                    ->orWhereNull('club_id'); // Hoặc sự kiện chung (không thuộc CLB nào)
                          })
                          ->get();
        } else {
            // Quỹ chung: hiển thị sự kiện chung hoặc tất cả
            $events = Event::with('club')
                          ->where(function($query) {
                              $query->whereNull('club_id'); // Chỉ sự kiện chung
                          })
                          ->get();
        }

        return view('admin.funds.transactions.create', compact('fund', 'events'));
    }

    /**
     * Lưu giao dịch mới
     */
    public function store(Request $request, Fund $fund)
    {
        try {
            // Loại bỏ file rỗng trước khi validation
            if ($request->hasFile('receipts')) {
                $receipts = $request->file('receipts');
                $validReceipts = [];
                foreach ($receipts as $receipt) {
                    if ($receipt && $receipt->isValid() && $receipt->getSize() > 0) {
                        $validReceipts[] = $receipt;
                    }
                }
                $request->merge(['receipts' => $validReceipts]);
            }
            
            $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $data = $request->except(['receipts']);
        $data['fund_id'] = $fund->id;
        $data['created_by'] = 1; // Tạm thời dùng user_id = 1

        // Xử lý upload nhiều chứng từ
        if ($request->hasFile('receipts')) {
            $receiptPaths = [];
            $receiptDir = public_path('uploads/receipts');
            
            if (!file_exists($receiptDir)) {
                mkdir($receiptDir, 0755, true);
            }
            
            $receipts = $request->file('receipts');
            if (is_array($receipts)) {
                foreach ($receipts as $receipt) {
                    if ($receipt && $receipt->isValid() && $receipt->getSize() > 0) {
                        $receiptName = time() . '_' . $fund->id . '_' . uniqid() . '_' . $receipt->getClientOriginalName();
                        $receiptPath = 'uploads/receipts/' . $receiptName;
                        
                        $receipt->move($receiptDir, $receiptName);
                        $receiptPaths[] = $receiptPath;
                    }
                }
            }
            
            if (!empty($receiptPaths)) {
                $data['receipt_paths'] = $receiptPaths;
            }
        }

        $transaction = FundTransaction::create($data);

        return redirect()->route('admin.funds.transactions', $fund->id)
            ->with('success', 'Tạo giao dịch thành công! Giao dịch đang chờ duyệt.');
        } catch (\Exception $e) {
            \Log::error('Error creating transaction: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết giao dịch
     */
    public function show(Fund $fund, FundTransaction $transaction)
    {
        $transaction->load(['creator', 'approver', 'event']);
        return view('admin.funds.transactions.show', compact('fund', 'transaction'));
    }

    /**
     * Hiển thị form chỉnh sửa giao dịch
     */
    public function edit(Fund $fund, FundTransaction $transaction)
    {
        // Chỉ cho phép chỉnh sửa giao dịch chưa được duyệt
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Không thể chỉnh sửa giao dịch đã được duyệt!');
        }

        // Lấy sự kiện liên quan đến CLB (nếu quỹ có CLB)
        if ($fund->club_id) {
            $events = Event::with('club')
                          ->where(function($query) use ($fund) {
                              $query->where('club_id', $fund->club_id)
                                    ->orWhereNull('club_id'); // Hoặc sự kiện chung (không thuộc CLB nào)
                          })
                          ->get();
        } else {
            // Quỹ chung: hiển thị sự kiện chung
            $events = Event::with('club')
                          ->where(function($query) {
                              $query->whereNull('club_id'); // Chỉ sự kiện chung
                          })
                          ->get();
        }

        return view('admin.funds.transactions.edit', compact('fund', 'transaction', 'events'));
    }

    /**
     * Cập nhật giao dịch
     */
    public function update(Request $request, Fund $fund, FundTransaction $transaction)
    {
        // Chỉ cho phép cập nhật giao dịch chưa được duyệt
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Không thể chỉnh sửa giao dịch đã được duyệt!');
        }

        $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'event_id' => 'nullable|exists:events,id',
            'receipts' => 'nullable',
            'receipts.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $data = $request->except(['receipts']);

        // Xử lý upload nhiều chứng từ mới
        if ($request->hasFile('receipts')) {
            // Xóa chứng từ cũ nếu có
            if ($transaction->receipt_paths) {
                foreach ($transaction->receipt_paths as $oldPath) {
                    if (file_exists(public_path($oldPath))) {
                        unlink(public_path($oldPath));
                    }
                }
            }

            $receiptPaths = [];
            $receiptDir = public_path('uploads/receipts');
            
            if (!file_exists($receiptDir)) {
                mkdir($receiptDir, 0755, true);
            }
            
            foreach ($request->file('receipts') as $receipt) {
                if ($receipt && $receipt->isValid()) {
                    $receiptName = time() . '_' . $fund->id . '_' . uniqid() . '_' . $receipt->getClientOriginalName();
                    $receiptPath = 'uploads/receipts/' . $receiptName;
                    
                    $receipt->move($receiptDir, $receiptName);
                    $receiptPaths[] = $receiptPath;
                }
            }
            
            if (!empty($receiptPaths)) {
                $data['receipt_paths'] = $receiptPaths;
            }
        }

        $transaction->update($data);

        return redirect()->route('admin.funds.transactions.show', [$fund->id, $transaction->id])
            ->with('success', 'Cập nhật giao dịch thành công!');
    }

    /**
     * Duyệt giao dịch
     */
    public function approve(Request $request, Fund $fund, FundTransaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Giao dịch này đã được xử lý!');
        }

        // Kiểm tra số dư quỹ trước khi duyệt giao dịch chi tiền
        if ($transaction->type === 'expense') {
            // Tính số dư hiện tại
            $fund->updateCurrentAmount();
            $currentBalance = $fund->current_amount;
            
            // Kiểm tra nếu chi tiền vượt quá số dư
            if ($currentBalance < $transaction->amount) {
                return back()->with('error', 
                    'Không thể duyệt giao dịch! Số dư quỹ hiện tại (' . number_format($currentBalance, 0) . ' VNĐ) ' .
                    'không đủ để thanh toán số tiền này (' . number_format($transaction->amount, 0) . ' VNĐ).'
                );
            }
        }

        $transaction->approve(session('user_id', 1));

        return redirect()->route('admin.funds.transactions', $fund->id)
            ->with('success', 'Duyệt giao dịch thành công!');
    }

    /**
     * Từ chối giao dịch
     */
    public function reject(Request $request, Fund $fund, FundTransaction $transaction)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Giao dịch này đã được xử lý!');
        }

        $transaction->reject(session('user_id', 1), $request->rejection_reason);

        return redirect()->route('admin.funds.transactions', $fund->id)
            ->with('success', 'Từ chối giao dịch thành công!');
    }

    /**
     * Hủy giao dịch (chỉ cho giao dịch đã duyệt)
     */
    public function cancel(Request $request, Fund $fund, FundTransaction $transaction)
    {
        if ($transaction->status !== 'approved') {
            return back()->with('error', 'Chỉ có thể hủy giao dịch đã được duyệt!');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:1000'
        ]);

        // Cập nhật trạng thái giao dịch gốc thành cancelled
        $transaction->update([
            'status' => 'cancelled',
            'rejection_reason' => $request->cancellation_reason
        ]);

        // Cập nhật số dư quỹ (giao dịch cancelled sẽ không được tính vào tổng)
        $fund->updateCurrentAmount();

        return redirect()->route('admin.funds.transactions', $fund->id)
            ->with('success', 'Hủy giao dịch thành công!');
    }

    /**
     * Xóa giao dịch (chỉ cho giao dịch chưa duyệt)
     */
    public function destroy(Fund $fund, FundTransaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Không thể xóa giao dịch đã được duyệt!');
        }

        // Xóa chứng từ nếu có
        if ($transaction->receipt_paths) {
            foreach ($transaction->receipt_paths as $path) {
                if (file_exists(public_path($path))) {
                    unlink(public_path($path));
                }
            }
        }

        $transaction->delete();

        return redirect()->route('admin.funds.transactions', $fund->id)
            ->with('success', 'Xóa giao dịch thành công!');
    }

    /**
     * Xuất hóa đơn PDF
     */
    public function exportInvoice(Fund $fund, FundTransaction $transaction)
    {
        // Load các quan hệ cần thiết
        $transaction->load(['creator', 'approver', 'event', 'fund.club']);
        
        // Tạo PDF
        $pdf = Pdf::loadView('admin.funds.transactions.invoice', [
            'transaction' => $transaction,
            'fund' => $fund
        ]);
        
        // Tên file
        $filename = 'hoa-don-giao-dich-' . $transaction->id . '-' . now()->format('YmdHis') . '.pdf';
        
        // Trả về file PDF để download
        return $pdf->download($filename);
    }

}