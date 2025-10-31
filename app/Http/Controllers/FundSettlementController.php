<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\FundTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FundSettlementController extends Controller
{
    /**
     * Hiển thị danh sách yêu cầu cần quyết toán
     */
    public function index(Request $request)
    {
        // Yêu cầu cần quyết toán
        $pendingQuery = FundRequest::with(['event', 'club', 'creator', 'approver', 'settler'])
            ->whereIn('status', ['approved', 'partially_approved'])
            ->where('settlement_status', 'settlement_pending');

        // Yêu cầu đã quyết toán
        $settledQuery = FundRequest::with(['event', 'club', 'creator', 'approver', 'settler'])
            ->where('settlement_status', 'settled');

        // Tìm kiếm cho cả hai
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFunction = function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('event', function($eventQuery) use ($search) {
                      $eventQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('club', function($clubQuery) use ($search) {
                      $clubQuery->where('name', 'like', "%{$search}%");
                  });
            };
            
            $pendingQuery->where($searchFunction);
            $settledQuery->where($searchFunction);
        }

        // Lọc theo CLB cho cả hai
        if ($request->filled('club_id')) {
            $pendingQuery->where('club_id', $request->club_id);
            $settledQuery->where('club_id', $request->club_id);
        }

        $requests = $pendingQuery->orderBy('approved_at', 'asc')->paginate(15);
        $settledRequests = $settledQuery->orderBy('settlement_date', 'desc')->paginate(15);
        $clubs = \App\Models\Club::all();

        return view('admin.fund-settlements.index', compact('requests', 'settledRequests', 'clubs'));
    }

    /**
     * Hiển thị form quyết toán
     */
    public function create(FundRequest $fundRequest)
    {
        if (!$fundRequest->needsSettlement()) {
            return redirect()->route('admin.fund-settlements')
                ->with('error', 'Yêu cầu này không cần quyết toán!');
        }

        $fundRequest->load(['event', 'club', 'creator', 'approver']);
        return view('admin.fund-settlements.create', compact('fundRequest'));
    }

    /**
     * Xử lý quyết toán
     */
    public function store(Request $request, FundRequest $fundRequest)
    {
        if (!$fundRequest->needsSettlement()) {
            return redirect()->back()->with('error', 'Yêu cầu này không cần quyết toán!');
        }

        $request->validate([
            'actual_amount' => 'required|numeric|min:0',
            'settlement_notes' => 'nullable|string|max:1000',
            'settlement_documents' => 'nullable|array|max:10',
            'settlement_documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max per file
        ]);

        // Kiểm tra nếu số tiền thực tế > số tiền được duyệt (có thể vượt quá)
        $actualAmount = $request->actual_amount;
        $approvedAmount = $fundRequest->approved_amount;
        
        // Cho phép vượt quá nhưng yêu cầu bắt buộc phải có hóa đơn khi vượt quá
        if ($actualAmount > $approvedAmount) {
            if (empty($request->settlement_documents)) {
                return redirect()->back()
                    ->with('error', 'Số tiền thực tế vượt quá số tiền được duyệt. Vui lòng upload hóa đơn/chứng từ để xác minh!')
                    ->withInput();
            }
        }

        try {
            $settlementDocuments = [];

            // Xử lý upload hóa đơn/chứng từ
            if ($request->hasFile('settlement_documents')) {
                $uploadPath = public_path('storage/fund-settlements');
                
                // Tạo thư mục nếu chưa tồn tại
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                foreach ($request->file('settlement_documents') as $index => $document) {
                    if ($document && $document->isValid() && $document->getSize() > 0) {
                        $filename = time() . '_' . $index . '_' . $document->getClientOriginalName();
                        $document->move($uploadPath, $filename);
                        $settlementDocuments[] = 'fund-settlements/' . $filename;
                    }
                }
            }

            // Kiểm tra yêu cầu hóa đơn bắt buộc
            $requiresInvoice = $this->requiresInvoice($fundRequest);
            if ($requiresInvoice && empty($settlementDocuments)) {
                return redirect()->back()
                    ->with('error', 'Yêu cầu này cần hóa đơn/chứng từ để quyết toán!')
                    ->withInput();
            }

            // Lấy user ID từ session
            $userId = session('user_id');
            if (!$userId) {
                return redirect()->back()
                    ->with('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.')
                    ->withInput();
            }

            // Thực hiện quyết toán
            $fundRequest->settle(
                $userId,
                $request->actual_amount,
                $request->settlement_notes,
                $settlementDocuments
            );

            // Tính toán tiền thừa/thiếu và cập nhật quỹ
            $this->updateFundAfterSettlement($fundRequest);

            return redirect()->route('admin.fund-settlements')
                ->with('success', 'Quyết toán thành công!');

        } catch (\Exception $e) {
            Log::error('Settlement error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi quyết toán: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hiển thị chi tiết quyết toán
     */
    public function show(FundRequest $fundRequest)
    {
        if (!$fundRequest->isSettled()) {
            return redirect()->route('admin.fund-settlements')
                ->with('error', 'Yêu cầu này chưa được quyết toán!');
        }

        $fundRequest->load(['event', 'club', 'creator', 'approver', 'settler']);
        return view('admin.fund-settlements.show', compact('fundRequest'));
    }

    /**
     * Kiểm tra yêu cầu có cần hóa đơn bắt buộc không
     */
    private function requiresInvoice(FundRequest $fundRequest)
    {
        // Logic kiểm tra: nếu là ngân sách chính thức thì cần hóa đơn
        // Có thể thêm logic phức tạp hơn dựa trên source, amount, etc.
        return $fundRequest->approved_amount >= 1000000; // >= 1 triệu VNĐ
    }

    /**
     * Cập nhật quỹ sau khi quyết toán
     * Xử lý trường hợp tiền thừa/thiếu
     */
    private function updateFundAfterSettlement(FundRequest $fundRequest)
    {
        try {
            Log::info('updateFundAfterSettlement called for FundRequest #' . $fundRequest->id);
            
            $club = $fundRequest->club;
            if (!$club) {
                Log::error('No club found for FundRequest #' . $fundRequest->id);
                return;
            }

            $fund = \App\Models\Fund::where('club_id', $club->id)->first();
            if (!$fund) {
                Log::error('No fund found for club ID: ' . $club->id);
                return;
            }

            $approvedAmount = $fundRequest->approved_amount;
            $actualAmount = $fundRequest->actual_amount;
            $difference = $approvedAmount - $actualAmount;

            Log::info('Settlement details: Approved=' . $approvedAmount . ', Actual=' . $actualAmount . ', Difference=' . $difference);

            // Lấy user ID từ session
            $userId = session('user_id');
            if (!$userId) {
                Log::error('No user_id in session for settlement');
                return;
            }
            
            Log::info('Creating settlement expense transaction...');

            // ✅ LOGIC ĐÚNG: Khi duyệt CỘNG tiền vào quỹ (income), quyết toán phải TRỪ tiền chi thực tế (expense)
            
            // 1. Tạo giao dịch CHI TIÊU thực tế
            $expenseTransaction = FundTransaction::create([
                'fund_id' => $fund->id,
                'event_id' => $fundRequest->event_id,
                'type' => 'expense',
                'transaction_type' => 'settlement',
                'title' => 'Quyết toán: ' . $fundRequest->title,
                'description' => 'Chi tiêu thực tế từ yêu cầu #' . $fundRequest->id,
                'amount' => $actualAmount,
                'transaction_date' => now(),
                'status' => 'approved',
                'created_by' => $userId,
                'approved_by' => $userId,
                'approved_at' => now(),
                'receipt_paths' => $fundRequest->settlement_documents
            ]);
            
            Log::info('Created settlement expense transaction ID: ' . $expenseTransaction->id);

            // 2. Nếu có tiền thừa (chi ít hơn dự toán), GHI NHỚ để theo dõi (KHÔNG hoàn lại quỹ vì đã cộng thiếu từ đầu)
            if ($difference > 0) {
                Log::info('Surplus detected: ' . number_format($difference) . ' VNĐ');
                Log::info('Note: Surplus remains in club budget (Net effect: +' . number_format($difference) . ' VNĐ to fund)');
                // Không tạo giao dịch refund vì:
                // - Khi duyệt: +1.000.000đ
                // - Khi quyết toán: -800.000đ
                // → Kết quả: Quỹ tăng 200.000đ (tiền thừa tự nhiên nằm trong quỹ)
            }
            // 3. Nếu chi vượt quá dự toán (chi nhiều hơn), tạo giao dịch chi thêm
            elseif ($difference < 0) {
                $excessAmount = abs($difference);
                Log::warning('Creating additional expense transaction for overspending: ' . $excessAmount);
                
                // Tạo giao dịch chi thêm vì đã chi vượt dự toán
                // Không cần tạo giao dịch mới, chỉ cần log cảnh báo
                // Vì expense ở trên đã ghi đúng số tiền thực tế chi (actualAmount)
                Log::warning('Overspending detected: ' . number_format($excessAmount) . ' VNĐ over approved amount');
                Log::warning('Net effect: Fund decreased by ' . number_format($actualAmount) . ' VNĐ (approved: ' . number_format($approvedAmount) . ' VNĐ)');
            }
            // 4. Nếu chi đúng bằng dự toán
            else {
                Log::info('Settlement matches approved amount exactly.');
            }

            // Cập nhật số dư quỹ
            $fund->updateCurrentAmount();
            Log::info('Fund updated successfully. New amount: ' . $fund->current_amount);

        } catch (\Exception $e) {
            Log::error('Error updating fund after settlement: ' . $e->getMessage());
        }
    }
}