<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Fund;
use App\Models\FundTransaction;
use App\Models\Event;
use App\Models\Club;
use App\Services\VNPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $vnpayService;

    public function __construct(VNPayService $vnpayService)
    {
        $this->vnpayService = $vnpayService;
    }

    /**
     * Hiển thị form tạo thanh toán
     */
    public function create(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        $fundId = $request->fund_id;
        $eventId = $request->event_id;
        $clubId = $request->club_id;
        $amount = $request->amount;
        $paymentType = $request->payment_type ?? 'other';

        // Validate số tiền nếu có
        if ($amount && $amount <= 0) {
            return back()->with('error', 'Số tiền không hợp lệ.');
        }

        $fund = null;
        $event = null;
        $club = null;

        if ($fundId) {
            $fund = Fund::find($fundId);
            if ($fund && !$clubId && $fund->club_id) {
                $clubId = $fund->club_id;
            }
        }
        if ($eventId) {
            $event = Event::find($eventId);
            if ($event && !$clubId && $event->club_id) {
                $clubId = $event->club_id;
            }
        }
        if ($clubId) {
            $club = Club::find($clubId);
            // Nếu có club nhưng chưa có fund, tự động tìm fund của club
            if ($club && !$fund) {
                $fund = Fund::where('club_id', $club->id)->first();
            }
        }

        return view('payments.create', compact('fund', 'event', 'club', 'amount', 'paymentType'));
    }

    /**
     * Tạo thanh toán và chuyển hướng đến cổng thanh toán
     */
    public function store(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1000', // Tối thiểu 1,000 VNĐ
            'payment_type' => 'required|in:event_registration,club_fee,fund_contribution,other',
            'payment_method' => 'required|in:vnpay',
            'description' => 'nullable|string|max:255',
            'fund_id' => 'nullable|exists:funds,id',
            'event_id' => 'nullable|exists:events,id',
            'club_id' => 'nullable|exists:clubs,id',
        ]);

        try {
            DB::beginTransaction();

            // Tạo payment record
            $payment = Payment::create([
                'user_id' => $userId,
                'fund_id' => $request->fund_id,
                'event_id' => $request->event_id,
                'club_id' => $request->club_id,
                'amount' => $request->amount,
                'currency' => 'VND',
                'payment_method' => $request->payment_method,
                'payment_type' => $request->payment_type,
                'status' => 'pending',
                'description' => $request->description ?? $this->getDefaultDescription($request->payment_type, $request),
                'expires_at' => now()->addHours(24), // Hết hạn sau 24 giờ
            ]);

            // Tạo URL thanh toán VNPay
            $paymentUrl = null;
            if ($request->payment_method === 'vnpay') {
                try {
                    $paymentUrl = $this->vnpayService->createPaymentUrl($payment);
                    $payment->payment_url = $paymentUrl;
                    $payment->save();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error creating VNPay URL: ' . $e->getMessage());
                    return back()->with('error', 'Lỗi tạo URL thanh toán: ' . $e->getMessage())->withInput();
                }
            }

            DB::commit();

            // Chuyển hướng đến cổng thanh toán
            if ($paymentUrl) {
                return redirect($paymentUrl);
            } else {
                return back()->with('error', 'Phương thức thanh toán không được hỗ trợ.')->withInput();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating payment: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tạo thanh toán: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Xử lý callback từ VNPay
     */
    public function vnpayReturn(Request $request)
    {
        $inputData = $request->all();

        // Xác thực callback
        if (!$this->vnpayService->verifyCallback($inputData)) {
            Log::warning('VNPay callback verification failed', $inputData);
            return redirect()->route('payments.failed')
                ->with('error', 'Xác thực thanh toán thất bại.');
        }

        // Xử lý kết quả thanh toán
        $result = $this->vnpayService->processCallback($inputData);
        $payment = Payment::where('payment_code', $result['payment_code'])->first();

        if (!$payment) {
            Log::error('Payment not found: ' . $result['payment_code']);
            return redirect()->route('payments.failed')
                ->with('error', 'Không tìm thấy giao dịch thanh toán.');
        }

        // Nếu đã xử lý rồi thì không xử lý lại
        if ($payment->isCompleted()) {
            return redirect()->route('payments.success', $payment->id)
                ->with('success', 'Thanh toán đã được xử lý trước đó.');
        }

        try {
            DB::beginTransaction();

            if ($result['success']) {
                // Đánh dấu thanh toán thành công
                $payment->markAsCompleted($result['transaction_id'], $inputData);
                $payment->bank_code = $result['bank_code'];
                $payment->save();

                // Tạo giao dịch quỹ nếu có fund_id
                if ($payment->fund_id) {
                    $this->createFundTransaction($payment);
                }

                // Xử lý theo loại thanh toán
                $this->handlePaymentSuccess($payment);

                DB::commit();

                return redirect()->route('payments.success', $payment->id)
                    ->with('success', 'Thanh toán thành công!');
            } else {
                // Đánh dấu thanh toán thất bại
                $payment->markAsFailed($result['message']);
                DB::commit();

                return redirect()->route('payments.failed')
                    ->with('error', 'Thanh toán thất bại: ' . $result['message']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment callback: ' . $e->getMessage());
            return redirect()->route('payments.failed')
                ->with('error', 'Có lỗi xảy ra khi xử lý thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Tạo giao dịch quỹ từ thanh toán thành công
     */
    private function createFundTransaction($payment)
    {
        $fund = Fund::find($payment->fund_id);
        if (!$fund) {
            return;
        }

        $fundTransaction = FundTransaction::create([
            'fund_id' => $fund->id,
            'type' => 'income',
            'amount' => $payment->amount,
            'title' => $payment->description ?? 'Thanh toán online: ' . $payment->payment_code,
            'description' => 'Thanh toán qua ' . strtoupper($payment->payment_method) . '. Mã thanh toán: ' . $payment->payment_code,
            'transaction_date' => now(),
            'status' => 'approved', // Thanh toán online tự động được duyệt
            'created_by' => $payment->user_id,
            'approved_by' => $payment->user_id,
            'approved_at' => now(),
            'source' => 'Thanh toán online',
        ]);

        // Liên kết payment với fund_transaction
        $payment->fund_transaction_id = $fundTransaction->id;
        $payment->save();

        // Cập nhật số dư quỹ
        $fund->updateCurrentAmount();
    }

    /**
     * Xử lý khi thanh toán thành công theo loại
     */
    private function handlePaymentSuccess($payment)
    {
        switch ($payment->payment_type) {
            case 'event_registration':
                // Xử lý đăng ký sự kiện
                // Có thể cập nhật EventRegistration status
                break;
            case 'club_fee':
                // Xử lý đóng phí CLB
                break;
            case 'fund_contribution':
                // Đã xử lý trong createFundTransaction
                break;
        }
    }

    /**
     * Lấy mô tả mặc định theo loại thanh toán
     */
    private function getDefaultDescription($paymentType, $request)
    {
        $descriptions = [
            'event_registration' => 'Đăng ký tham gia sự kiện',
            'club_fee' => 'Đóng phí CLB',
            'fund_contribution' => 'Đóng góp quỹ',
            'other' => 'Thanh toán dịch vụ',
        ];

        $description = $descriptions[$paymentType] ?? 'Thanh toán dịch vụ';

        if ($request->event_id) {
            $event = Event::find($request->event_id);
            if ($event) {
                $description .= ': ' . $event->name;
            }
        }

        if ($request->club_id) {
            $club = Club::find($request->club_id);
            if ($club) {
                $description .= ' - ' . $club->name;
            }
        }

        return $description;
    }

    /**
     * Hiển thị trang thanh toán thành công
     */
    public function success($id)
    {
        $payment = Payment::with(['user', 'fund', 'event', 'club', 'fundTransaction'])
            ->findOrFail($id);

        return view('payments.success', compact('payment'));
    }

    /**
     * Hiển thị trang thanh toán thất bại
     */
    public function failed()
    {
        return view('payments.failed');
    }

    /**
     * Hiển thị lịch sử thanh toán của user
     */
    public function history(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('simple.login')->with('error', 'Vui lòng đăng nhập.');
        }

        $payments = Payment::where('user_id', $userId)
            ->with(['fund', 'event', 'club'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('payments.history', compact('payments'));
    }

    /**
     * Hủy thanh toán
     */
    public function cancel($id)
    {
        $userId = session('user_id');
        $payment = Payment::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($payment->isCompleted()) {
            return back()->with('error', 'Không thể hủy thanh toán đã hoàn thành.');
        }

        $payment->markAsCancelled('Người dùng hủy thanh toán');

        return redirect()->route('payments.history')
            ->with('success', 'Đã hủy thanh toán thành công.');
    }
}
