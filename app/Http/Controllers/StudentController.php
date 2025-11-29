<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubJoinRequest;
use App\Models\Event;
use App\Models\Field;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\ClubJoinRequest as JoinReq;
use App\Models\Fund;
use App\Models\FundTransaction;
use App\Models\FundRequest;
use App\Models\ClubResource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Check if user is logged in as student
     */
    private function checkStudentAuth()
    {
        if (!session('user_id') || session('is_admin')) {
            if (session('is_admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập với tài khoản sinh viên.');
        }

        $user = User::with('clubs')->find(session('user_id'));
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_email', 'is_admin']);
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn.');
        }

        return $user;
    }

    /**
     * Display student dashboard
     */
    public function dashboard()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.dashboard', compact('user'));
    }

    public function clubs(Request $request) 
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $search = trim((string) $request->input('search'));

        // Lấy ID các CLB mà người dùng đã tham gia
        $myClubIds = $user->clubs->pluck('id')->toArray();

        // Query CLB của tôi
        $myClubsQuery = Club::whereIn('id', $myClubIds)
            ->where('status', 'active')
            ->withCount('members');

        // Áp dụng tìm kiếm cho CLB của tôi nếu có
        if ($search) {
            $myClubsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $myClubs = $myClubsQuery->orderBy('name')->get();

        // Query các CLB khác (chưa tham gia)
        $otherClubsQuery = Club::where('status', 'active')
            ->whereNotIn('id', $myClubIds)
            ->withCount('members');

        // Áp dụng bộ lọc tìm kiếm cho các CLB khác
        if ($search) {
            $otherClubsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Lấy kết quả với phân trang
        $otherClubs = $otherClubsQuery->orderBy('name')->paginate(8)->withQueryString();

        return view('student.clubs.index', compact('user', 'myClubs', 'otherClubs', 'search'));
    }

    public function ajaxSearchClubs(Request $request)
    {
        $user = $this->checkStudentAuth();
        // Nếu checkStudentAuth trả về một redirect, nghĩa là chưa đăng nhập, trả về lỗi
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response('Unauthorized.', 401);
        }

        $search = $request->input('search', '');
        $myClubIds = $user->clubs->pluck('id')->toArray();

        $otherClubs = Club::where('status', 'active')
            ->whereNotIn('id', $myClubIds)
            ->where('name', 'like', '%' . $search . '%')
            ->withCount('members')
            ->orderBy('name')
            ->paginate(8);

        return view('student.clubs._other_clubs_list', compact('otherClubs', 'search'));
    }

    public function events(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $search = trim((string) $request->input('search'));

        // Lấy events đang diễn ra (đã bắt đầu nhưng chưa kết thúc) - chỉ lấy events đã được duyệt hoặc ongoing
        $ongoingEventsQuery = Event::with(['club', 'creator', 'images'])
            ->whereIn('status', ['approved', 'ongoing'])
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now());

        // Lấy events sắp tới (chưa bắt đầu) - chỉ lấy events đã được duyệt
        $upcomingEventsQuery = Event::with(['club', 'creator', 'images'])
            ->where('status', 'approved')
            ->where('start_time', '>', now())
            ->where('end_time', '>=', now());

        // Áp dụng tìm kiếm nếu có
        if ($search) {
            $ongoingEventsQuery->where(function($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('location', 'like', '%' . $search . '%');
            });
            $upcomingEventsQuery->where(function($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        $ongoingEvents = $ongoingEventsQuery->orderBy('start_time', 'asc')->get();
        $upcomingEvents = $upcomingEventsQuery->orderBy('start_time', 'asc')->get();

        // Gộp tất cả events chưa kết thúc (đang diễn ra + sắp tới) để đếm đăng ký
        $allActiveEvents = $ongoingEvents->merge($upcomingEvents);

        // Lấy events đã đăng ký bởi user
        $registeredEventIds = \App\Models\EventRegistration::where('user_id', $user->id)
            ->whereIn('status', ['registered', 'pending', 'approved'])
            ->pluck('event_id')
            ->toArray();

        $registeredEventsQuery = Event::with(['club', 'creator', 'images'])
            ->whereIn('id', $registeredEventIds)
            ->whereIn('status', ['approved', 'ongoing']);

        // Áp dụng tìm kiếm cho registered events
        if ($search) {
            $registeredEventsQuery->where(function($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        $registeredEvents = $registeredEventsQuery->orderBy('start_time', 'asc')->get();

        // Đếm số lượng đăng ký cho mỗi event (cả đang diễn ra và sắp tới)
        $eventRegistrations = \App\Models\EventRegistration::whereIn('event_id', $allActiveEvents->pluck('id'))
            ->whereIn('status', ['registered', 'pending', 'approved'])
            ->selectRaw('event_id, COUNT(*) as count')
            ->groupBy('event_id')
            ->pluck('count', 'event_id')
            ->toArray();

        // Thống kê sidebar - đếm events đã được duyệt hoặc đang diễn ra
        $todayEvents = Event::whereIn('status', ['approved', 'ongoing'])
            ->whereDate('start_time', now()->toDateString())
            ->count();

        $thisWeekEvents = Event::whereIn('status', ['approved', 'ongoing'])
            ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $thisMonthEvents = Event::whereIn('status', ['approved', 'ongoing'])
            ->whereMonth('start_time', now()->month)
            ->whereYear('start_time', now()->year)
            ->count();

        // Sự kiện hot (có nhiều đăng ký nhất) - lấy events đã được duyệt hoặc đang diễn ra
        $hotEvents = Event::with(['club'])
            ->whereIn('status', ['approved', 'ongoing'])
            ->where('end_time', '>=', now())
            ->get()
            ->map(function($event) {
                $registrationCount = \App\Models\EventRegistration::where('event_id', $event->id)
                    ->whereIn('status', ['registered', 'pending', 'approved'])
                    ->count();
                $event->registration_count = $registrationCount;
                $event->registration_percentage = $event->max_participants > 0 
                    ? round(($registrationCount / $event->max_participants) * 100) 
                    : 0;
                return $event;
            })
            ->sortByDesc('registration_count')
            ->take(2);

        return view('student.events.index', compact(
            'user', 
            'upcomingEvents',
            'ongoingEvents',
            'registeredEvents',
            'eventRegistrations',
            'todayEvents',
            'thisWeekEvents',
            'thisMonthEvents',
            'hotEvents',
            'search'
        ));
    }

    /**
     * Register for an event
     */
    public function registerEvent($eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đăng ký sự kiện.'
            ], 401);
        }

        try {
            $event = Event::findOrFail($eventId);

            // Kiểm tra sự kiện có bị hủy không
            if ($event->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này đã bị hủy.'
                ], 400);
            }

            // Kiểm tra sự kiện đã được duyệt hoặc đang diễn ra
            if (!in_array($event->status, ['approved', 'ongoing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này chưa được duyệt hoặc không khả dụng.'
                ], 400);
            }

            // Kiểm tra sự kiện đã kết thúc chưa
            if ($event->end_time && $event->end_time < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này đã kết thúc.'
                ], 400);
            }

            // Kiểm tra hạn đăng ký (nếu có)
            if ($event->registration_deadline && $event->registration_deadline < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã hết hạn đăng ký cho sự kiện này.'
                ], 400);
            }

            // Nếu sự kiện đã bắt đầu (đang diễn ra), vẫn cho phép đăng ký nếu chưa hết hạn đăng ký
            // (có thể tham gia muộn hoặc theo dõi)

            // Kiểm tra đã đăng ký chưa
            $existingRegistration = \App\Models\EventRegistration::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->whereIn('status', ['registered', 'pending', 'approved'])
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã đăng ký sự kiện này rồi.'
                ], 400);
            }

            // Kiểm tra số lượng người tham gia
            if ($event->max_participants > 0) {
                $currentRegistrations = \App\Models\EventRegistration::where('event_id', $eventId)
                    ->whereIn('status', ['registered', 'pending', 'approved'])
                    ->count();

                if ($currentRegistrations >= $event->max_participants) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sự kiện này đã đầy.'
                    ], 400);
                }
            }

            // Tạo đăng ký mới
            $registration = \App\Models\EventRegistration::create([
                'user_id' => $user->id,
                'event_id' => $eventId,
                'status' => 'registered',
                'joined_at' => now(),
            ]);

            // Tạo thông báo cho user
            $notification = \App\Models\Notification::create([
                'sender_id' => $event->club->leader_id ?? 1, // Gửi từ CLB
                'type' => 'event_registration',
                'related_id' => $eventId,
                'related_type' => 'Event',
                'title' => 'Đăng ký tham gia sự kiện thành công',
                'message' => 'Bạn đã đăng ký tham gia sự kiện "' . $event->title . '" của ' . ($event->club->name ?? 'CLB') . '. Thời gian: ' . ($event->start_time ? $event->start_time->format('d/m/Y H:i') : 'Chưa xác định') . '.',
            ]);

            // Gửi thông báo đến user
            \App\Models\NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_type' => 'user',
                'target_id' => $user->id,
            ]);

            // Gửi email xác nhận
            try {
                \Mail::to($user->email)->send(new \App\Mail\EventRegistrationConfirmation($user, $event));
            } catch (\Exception $e) {
                \Log::error('Failed to send event registration email: ' . $e->getMessage());
                // Vẫn tiếp tục dù gửi email thất bại
            }

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tham gia sự kiện thành công'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sự kiện.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đăng ký: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel event registration
     */
    public function cancelRegistration($eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để hủy đăng ký sự kiện.'
            ], 401);
        }

        try {
            $event = Event::findOrFail($eventId);

            // Kiểm tra sự kiện có bị hủy không
            if ($event->status === 'cancelled' || $event->status === 'canceled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này đã bị hủy.'
                ], 400);
            }

            // Tìm đăng ký của user
            $registration = \App\Models\EventRegistration::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->whereIn('status', ['registered', 'pending', 'approved'])
                ->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa đăng ký sự kiện này.'
                ], 400);
            }

            // Chỉ cho phép hủy đăng ký TRƯỚC KHI sự kiện bắt đầu
            if ($event->start_time && $event->start_time <= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đăng ký vì sự kiện đã bắt đầu hoặc đã kết thúc.'
                ], 400);
            }

            // Cập nhật trạng thái đăng ký thành canceled
            $registration->update([
                'status' => 'canceled',
                'left_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hủy đăng ký sự kiện thành công'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sự kiện.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy đăng ký: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show event details
     */
    public function showEvent($eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        try {
            $event = Event::with(['club', 'creator', 'images'])->findOrFail($eventId);

            // Kiểm tra nếu user là thành viên của CLB tổ chức sự kiện
            $isClubMember = false;
            if ($event->club_id && $user->clubs->contains('id', $event->club_id)) {
                $isClubMember = true;
            }

            // Nếu sự kiện chưa được duyệt, chỉ cho phép xem nếu user là thành viên của CLB tổ chức
            if ($event->status !== 'approved' && !$isClubMember) {
                return redirect()->route('student.events.index')
                    ->with('error', 'Sự kiện này chưa được duyệt hoặc không khả dụng.');
            }

            // Kiểm tra đăng ký của user
            $isRegistered = \App\Models\EventRegistration::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->whereIn('status', ['registered', 'pending', 'approved'])
                ->exists();

            // Kiểm tra có thể hủy đăng ký không (chỉ khi sự kiện chưa bắt đầu)
            $canCancelRegistration = $isRegistered && $event->start_time && $event->start_time > now();

            // Đếm số lượng đăng ký
            $registrationCount = \App\Models\EventRegistration::where('event_id', $eventId)
                ->whereIn('status', ['registered', 'pending', 'approved'])
                ->count();

            $availableSlots = $event->max_participants > 0 ? $event->max_participants - $registrationCount : null;
            $isFull = $event->max_participants > 0 && $registrationCount >= $event->max_participants;
            $isDeadlinePassed = $event->registration_deadline && $event->registration_deadline < now();

            return view('student.events.show', compact(
                'user',
                'event',
                'isRegistered',
                'canCancelRegistration',
                'registrationCount',
                'availableSlots',
                'isFull',
                'isDeadlinePassed',
                'isClubMember'
            ));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('student.events.index')->with('error', 'Không tìm thấy sự kiện.');
        } catch (\Exception $e) {
            return redirect()->route('student.events.index')->with('error', 'Có lỗi xảy ra khi tải sự kiện.');
        }
    }

    /**
     * Show create event form for user
     */
    public function createEvent()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Kiểm tra user có CLB và có quyền tạo sự kiện không
        if ($user->clubs->count() == 0) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn cần tham gia CLB để tạo sự kiện.');
        }

        $userClub = $user->clubs->first();
        $clubId = $userClub->id;
        $userPosition = $user->getPositionInClub($clubId);
        
        // Kiểm tra quyền tạo sự kiện
        if (!$user->hasPermission('tao_su_kien', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền tạo sự kiện cho CLB này.');
        }

        return view('student.events.create', compact('user', 'userClub', 'clubId'));
    }

    /**
     * Store event created by user
     */
    public function storeEvent(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Kiểm tra user có CLB và có quyền tạo sự kiện không
        if ($user->clubs->count() == 0) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn cần tham gia CLB để tạo sự kiện.');
        }

        $userClub = $user->clubs->first();
        $clubId = $userClub->id;
        
        // Kiểm tra quyền tạo sự kiện
        if (!$user->hasPermission('tao_su_kien', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền tạo sự kiện cho CLB này.');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:10000',
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
                'mode' => 'required|in:offline,online,hybrid',
                'location' => 'nullable|string|max:255',
                'max_participants' => 'nullable|integer|min:1',
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
                'start_time.after_or_equal' => 'Thời gian bắt đầu sự kiện không được trong quá khứ.',
                'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
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
                        $columnNames[] = $colName;
                    } catch (\Exception $e) {
                        \Log::warning("StoreEvent - Failed to add column {$colName}: " . $e->getMessage());
                    }
                }
            }
            
            $eventData = [
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'image' => null,
                'club_id' => $clubId, // Tự động gán CLB của user
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'mode' => $request->mode,
                'location' => $request->location,
                'max_participants' => $request->max_participants,
                'status' => 'pending', // User tạo sự kiện sẽ ở trạng thái chờ duyệt
                'created_by' => $user->id,
            ];
            
            // Thêm các field mới vào eventData
            if (in_array('registration_deadline', $columnNames)) {
                $eventData['registration_deadline'] = $request->registration_deadline;
            }
            if (in_array('main_organizer', $columnNames)) {
                $eventData['main_organizer'] = $request->main_organizer;
            }
            if (in_array('organizing_team', $columnNames)) {
                $eventData['organizing_team'] = $request->organizing_team;
            }
            if (in_array('co_organizers', $columnNames)) {
                $eventData['co_organizers'] = $request->co_organizers;
            }
            if (in_array('contact_info', $columnNames)) {
                $eventData['contact_info'] = $contactInfo;
            }
            if (in_array('proposal_file', $columnNames)) {
                $eventData['proposal_file'] = $proposalFilePath;
            }
            if (in_array('poster_file', $columnNames)) {
                $eventData['poster_file'] = $posterFilePath;
            }
            if (in_array('permit_file', $columnNames)) {
                $eventData['permit_file'] = $permitFilePath;
            }
            if (in_array('guests', $columnNames)) {
                $eventData['guests'] = $guestsData;
            }
            
            $event = Event::create($eventData);
            
            // Xử lý upload nhiều ảnh
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $imagePath = $image->store('events', 'public');
                    \App\Models\EventImage::create([
                        'event_id' => $event->id,
                        'image_path' => $imagePath,
                        'alt_text' => $request->title . ' - Ảnh ' . ($index + 1),
                        'sort_order' => $index,
                    ]);
                }
            }

            return redirect()->route('student.club-management.index')
                ->with('success', 'Tạo sự kiện thành công! Sự kiện của bạn đang chờ quản trị viên duyệt.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('StoreEvent Error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tạo sự kiện: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Manage events for user's club
     */
    public function manageEvents()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Kiểm tra user có CLB và có quyền tạo sự kiện không
        if ($user->clubs->count() == 0) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn cần tham gia CLB để quản lý sự kiện.');
        }

        $userClub = $user->clubs->first();
        $clubId = $userClub->id;
        
        // Kiểm tra quyền tạo sự kiện
        if (!$user->hasPermission('tao_su_kien', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý sự kiện cho CLB này.');
        }

        // Lấy tất cả sự kiện của CLB
        $allEvents = Event::with(['club', 'creator', 'images'])
            ->where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Lấy các sự kiện đang chờ duyệt
        $pendingEvents = $allEvents->where('status', 'pending')->values();

        // Lấy các sự kiện đã duyệt
        $approvedEvents = $allEvents->where('status', 'approved')->values();

        // Lấy các sự kiện đang diễn ra
        $ongoingEvents = $allEvents->where('status', 'ongoing')->values();

        // Lấy các sự kiện đã hoàn thành
        $completedEvents = $allEvents->where('status', 'completed')->values();

        // Lấy các sự kiện đã hủy
        $cancelledEvents = $allEvents->where('status', 'cancelled')->values();

        // Thống kê
        $stats = [
            'total' => $allEvents->count(),
            'pending' => $pendingEvents->count(),
            'approved' => $approvedEvents->count(),
            'ongoing' => $ongoingEvents->count(),
            'completed' => $completedEvents->count(),
            'cancelled' => $cancelledEvents->count(),
        ];

        return view('student.events.manage', compact('user', 'userClub', 'clubId', 'allEvents', 'pendingEvents', 'approvedEvents', 'ongoingEvents', 'completedEvents', 'cancelledEvents', 'stats'));
    }

    /**
     * Restore cancelled event
     */
    public function restoreEvent($eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Kiểm tra user có CLB và có quyền tạo sự kiện không
        if ($user->clubs->count() == 0) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn cần tham gia CLB để quản lý sự kiện.');
        }

        $userClub = $user->clubs->first();
        $clubId = $userClub->id;
        
        // Kiểm tra quyền tạo sự kiện
        if (!$user->hasPermission('tao_su_kien', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý sự kiện cho CLB này.');
        }

        try {
            $event = \App\Models\Event::findOrFail($eventId);
            
            // Kiểm tra sự kiện thuộc CLB của user
            if ($event->club_id != $clubId) {
                return redirect()->route('student.events.manage')
                    ->with('error', 'Sự kiện không thuộc CLB của bạn.');
            }

            // Chỉ cho phép khôi phục sự kiện đã hủy
            if ($event->status !== 'cancelled') {
                return redirect()->route('student.events.manage')
                    ->with('error', 'Chỉ có thể khôi phục sự kiện đã hủy.');
            }

            // Kiểm tra thời gian - nếu sự kiện đã kết thúc thì không thể khôi phục
            if ($event->end_time && $event->end_time->isPast()) {
                return redirect()->route('student.events.manage')
                    ->with('error', 'Không thể khôi phục sự kiện đã kết thúc.');
            }

            // Khôi phục sự kiện - chuyển về trạng thái approved
            // Nếu sự kiện đã bắt đầu nhưng chưa kết thúc, chuyển thành ongoing
            $newStatus = 'approved';
            if ($event->start_time && $event->start_time->isPast() && $event->end_time && $event->end_time->isFuture()) {
                $newStatus = 'ongoing';
            } elseif ($event->start_time && $event->start_time->isPast() && $event->end_time && $event->end_time->isPast()) {
                // Nếu đã kết thúc thì không thể khôi phục (đã được kiểm tra ở trên)
                $newStatus = 'completed';
            }

            $event->status = $newStatus;
            $event->cancellation_reason = null;
            $event->cancelled_at = null;
            $event->save();

            return redirect()->route('student.events.manage')
                ->with('success', 'Đã khôi phục sự kiện thành công.');
        } catch (\Exception $e) {
            \Log::error('Restore event error: ' . $e->getMessage());
            return redirect()->route('student.events.manage')
                ->with('error', 'Có lỗi xảy ra khi khôi phục sự kiện: ' . $e->getMessage());
        }
    }

    /**
     * Xóa sự kiện (soft delete)
     */
    public function deleteEvent($eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Kiểm tra user có CLB và có quyền tạo sự kiện không
        if ($user->clubs->count() == 0) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn cần tham gia CLB để quản lý sự kiện.');
        }

        $userClub = $user->clubs->first();
        $clubId = $userClub->id;
        
        // Kiểm tra quyền tạo sự kiện
        if (!$user->hasPermission('tao_su_kien', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý sự kiện cho CLB này.');
        }

        try {
            $event = \App\Models\Event::findOrFail($eventId);
            
            // Kiểm tra sự kiện thuộc CLB của user
            if ($event->club_id != $clubId) {
                return redirect()->route('student.events.manage')
                    ->with('error', 'Sự kiện không thuộc CLB của bạn.');
            }

            // Chỉ cho phép xóa sự kiện ở trạng thái pending, draft, hoặc cancelled
            // Không cho phép xóa sự kiện đang diễn ra hoặc đã hoàn thành
            if (in_array($event->status, ['ongoing', 'completed'])) {
                return redirect()->route('student.events.manage')
                    ->with('error', 'Không thể xóa sự kiện đang diễn ra hoặc đã hoàn thành.');
            }

            // Kiểm tra thời gian - không cho phép xóa nếu sự kiện đã bắt đầu
            if ($event->start_time && $event->start_time->isPast()) {
                return redirect()->route('student.events.manage')
                    ->with('error', 'Không thể xóa sự kiện đã bắt đầu.');
            }

            // Soft delete sự kiện
            $event->delete();

            return redirect()->route('student.events.manage')
                ->with('success', 'Đã xóa sự kiện thành công.');
        } catch (\Exception $e) {
            \Log::error('Delete event error: ' . $e->getMessage());
            return redirect()->route('student.events.manage')
                ->with('error', 'Có lỗi xảy ra khi xóa sự kiện: ' . $e->getMessage());
        }
    }

    public function profile()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.profile.index', compact('user'));
    }

    public function notifications()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Lấy thông báo dành cho user này
        $notificationIds = \App\Models\NotificationTarget::where('target_type', 'user')
            ->where('target_id', $user->id)
            ->pluck('notification_id');

        // Lấy thông báo từ CLB mà user là thành viên
        $userClubIds = $user->clubs->pluck('id')->toArray();
        $clubNotificationIds = \App\Models\NotificationTarget::where('target_type', 'club')
            ->whereIn('target_id', $userClubIds)
            ->pluck('notification_id');

        // Gộp tất cả notification IDs
        $allNotificationIds = $notificationIds->merge($clubNotificationIds)->unique();

        // Lấy thông báo
        $notifications = \App\Models\Notification::with(['sender'])
            ->whereIn('id', $allNotificationIds)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Lấy trạng thái đã đọc
        $readNotificationIds = \App\Models\NotificationRead::where('user_id', $user->id)
            ->where('is_read', true)
            ->pluck('notification_id')
            ->toArray();

        return view('student.notifications.index', compact('user', 'notifications', 'readNotificationIds'));
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['success' => false], 401);
        }

        // Kiểm tra notification có thuộc về user không
        $notificationTarget = \App\Models\NotificationTarget::where('notification_id', $notificationId)
            ->where(function($query) use ($user) {
                $query->where(function($q) use ($user) {
                    $q->where('target_type', 'user')
                      ->where('target_id', $user->id);
                })->orWhere(function($q) use ($user) {
                    $userClubIds = $user->clubs->pluck('id')->toArray();
                    $q->where('target_type', 'club')
                      ->whereIn('target_id', $userClubIds);
                });
            })
            ->first();

        if (!$notificationTarget) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông báo'], 404);
        }

        // Đánh dấu đã đọc
        \App\Models\NotificationRead::updateOrCreate(
            [
                'notification_id' => $notificationId,
                'user_id' => $user->id,
            ],
            [
                'is_read' => true,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function contact()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.contact', compact('user'));
    }

    /**
     * Helper method: Get the club that user has management role in
     * Returns the club membership with the highest priority (leader > vice_president > treasurer)
     */
    private function getManagedClub($user)
    {
        $allManagedClubs = ClubMember::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'active'])
            ->whereIn('position', ['leader', 'vice_president', 'treasurer'])
            ->with('club')
            ->get();
        
        if ($allManagedClubs->isEmpty()) {
            return null;
        }
        
        // Ưu tiên: leader > vice_president > treasurer, nếu cùng role thì lấy CLB mới nhất
        $treasurerClubs = $allManagedClubs->where('position', 'treasurer');
        $vicePresidentClubs = $allManagedClubs->where('position', 'vice_president');
        $leaderClubs = $allManagedClubs->where('position', 'leader');
        
        if ($leaderClubs->count() > 0) {
            return $leaderClubs->sortByDesc('joined_at')->first();
        } elseif ($vicePresidentClubs->count() > 0) {
            return $vicePresidentClubs->sortByDesc('joined_at')->first();
        } elseif ($treasurerClubs->count() > 0) {
            return $treasurerClubs->sortByDesc('joined_at')->first();
        } else {
            return $allManagedClubs->first();
        }
    }

    public function clubManagement(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $search = trim((string) $request->input('search'));

        // Luôn hiển thị trang, nhưng kiểm tra quyền để hiển thị nội dung phù hợp
        $hasManagementRole = false;
        $clubId = null;
        $userPosition = null;
        $userClub = null;
        $clubStats = [
            'members' => ['active' => 0, 'pending' => 0],
            'events' => ['total' => 0, 'upcoming' => 0],
            'announcements' => ['total' => 0, 'today' => 0],
        ];
        $clubMembers = collect();
        $allPermissions = collect();

        // Tìm CLB mà user có role quản lý (leader, vice_president, treasurer)
        $clubMembership = $this->getManagedClub($user);
        $allManagedClubs = ClubMember::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'active'])
            ->whereIn('position', ['leader', 'vice_president', 'treasurer'])
            ->with('club')
            ->get();
        
        if ($clubMembership) {
            $managedClub = $clubMembership->club;
            $clubId = $managedClub->id;
            $userPosition = $clubMembership->position;
            $hasManagementRole = true;
        } else {
            // Nếu không có CLB nào có role quản lý, lấy CLB đầu tiên để hiển thị thông báo
            if ($user->clubs->count() > 0) {
                $managedClub = $user->clubs->first();
                $clubId = $managedClub->id;
            $clubMember = ClubMember::where('user_id', $user->id)->where('club_id', $clubId)->first();
            $userPosition = $clubMember ? $clubMember->position : null;
                $hasManagementRole = false;
            } else {
                $managedClub = null;
                $clubId = null;
                $userPosition = null;
            }
        }
        
        $userClub = $managedClub;
        
        if ($userClub && $clubId) {

            // Tính toán thống kê cơ bản cho view
            $activeMembers = ClubMember::where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->count();
            $pendingMembers = ClubJoinRequest::where('club_id', $clubId)
                ->where('status', 'pending')
                ->count();
            $totalEvents = Event::where('club_id', $clubId)->count();
            $upcomingEvents = Event::where('club_id', $clubId)
                ->where('start_time', '>=', now())
                ->count();
            $totalAnnouncements = Post::where('club_id', $clubId)
                ->where('type', 'announcement')
                ->where('status', '!=', 'deleted')
                ->count();
            $todayAnnouncements = Post::where('club_id', $clubId)
                ->where('type', 'announcement')
                ->whereDate('created_at', now()->toDateString())
                ->count();
            
            // Posts stats
            $totalPosts = Post::where('club_id', $clubId)
                ->where('type', 'post')
                ->where('status', '!=', 'deleted')
                ->count();

            // Fund requests stats
            $totalFundRequests = FundRequest::where('club_id', $clubId)->count();
            $pendingFundRequests = FundRequest::where('club_id', $clubId)->where('status', 'pending')->count();

            // Fund stats
            $fund = \App\Models\Fund::where('club_id', $clubId)->first();
            $fundId = $fund?->id ?? null;
            $totalIncome = 0;
            $totalExpense = 0;
            $balance = 0;

            if ($fundId) {
                $totalIncome = \App\Models\FundTransaction::where('fund_id', $fundId)
                    ->where('type', 'income')
                    ->where('status', 'approved')
                    ->sum('amount');
                $totalExpense = \App\Models\FundTransaction::where('fund_id', $fundId)
                    ->where('type', 'expense')
                    ->where('status', 'approved')
                    ->sum('amount');
                $balance = (int) ($fund->initial_amount ?? 0) + (int) $totalIncome - (int) $totalExpense;
            }

            // Resources stats
            $totalResources = \App\Models\ClubResource::where('club_id', $clubId)->count();
            $totalFiles = \App\Models\ClubResourceFile::whereHas('clubResource', function($q) use ($clubId) {
                $q->where('club_id', $clubId);
            })->count();

            $clubStats = [
                'members' => ['active' => $activeMembers, 'pending' => $pendingMembers],
                'events' => ['total' => $totalEvents, 'upcoming' => $upcomingEvents],
                'announcements' => ['total' => $totalAnnouncements, 'today' => $todayAnnouncements],
                'posts' => $totalPosts,
                'fundRequests' => ['total' => $totalFundRequests, 'pending' => $pendingFundRequests],
                'fund' => [
                    'balance' => $balance,
                    'income' => $totalIncome,
                    'expense' => $totalExpense,
                    'exists' => $fund !== null,
                ],
                'resources' => ['total' => $totalResources, 'files' => $totalFiles],
            ];

            // Danh sách thành viên (phục vụ các thẻ hoặc view cần)
            $clubMembersQuery = ClubMember::with('user')
                ->where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active']);

            // Áp dụng tìm kiếm nếu có
            if ($search) {
                $clubMembersQuery->whereHas('user', function($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $clubMembers = $clubMembersQuery
                ->orderByRaw("FIELD(position, 'leader', 'vice_president', 'treasurer', 'member') ASC")
                ->orderByDesc('joined_at')
                ->get()
                ->map(function ($member) use ($clubId) {
                    $member->permission_names = $member->user
                        ? $member->user->getClubPermissions($clubId)
                        : [];
                    return $member;
                });

            // Tìm kiếm sự kiện nếu có
            $searchEvents = collect();
            if ($search) {
                $searchEvents = Event::where('club_id', $clubId)
                    ->where(function($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                              ->orWhere('description', 'like', '%' . $search . '%');
                    })
                    ->orderBy('start_time', 'desc')
                    ->limit(5)
                    ->get();
            }

            // Tìm kiếm bài viết nếu có
            $searchPosts = collect();
            if ($search) {
                $searchPosts = Post::where('club_id', $clubId)
                    ->where(function($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                              ->orWhere('content', 'like', '%' . $search . '%');
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            }

            $allPermissions = Permission::orderBy('name')->get();
        } else {
            $searchEvents = collect();
            $searchPosts = collect();
        }

        // Truyền thêm $clubId và $search để tránh lỗi undefined variable trong view
        return view(
            'student.club-management.index',
            compact('user', 'hasManagementRole', 'userPosition', 'userClub', 'clubId', 'clubStats', 'clubMembers', 'allPermissions', 'search', 'searchEvents', 'searchPosts')
        );
    }

    /**
     * Members management page
     */
    public function manageMembers($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        // Only leader/vice/treasurer can access members management
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president', 'treasurer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý thành viên CLB này.');
        }

        // Danh sách thành viên + gán quyền hiện có
        $clubMembers = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->orderByRaw("FIELD(position, 'leader', 'vice_president', 'treasurer', 'member') ASC")
            ->orderByDesc('joined_at')
            ->get()
            ->map(function ($member) use ($clubId) {
                $member->permission_names = $member->user
                    ? $member->user->getClubPermissions($clubId)
                    : [];
                return $member;
            });

        $allPermissions = Permission::orderBy('name')->get();

        return view('student.club-management.members', [
            'user' => $user,
            'club' => $club,
            'clubMembers' => $clubMembers,
            'allPermissions' => $allPermissions,
            'userPosition' => $position,
            'clubId' => $clubId,
        ]);
    }

    /**
     * Club settings page
     */
    public function clubSettings($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::with(['leader'])->findOrFail($clubId);

        // Only leader can access settings
        if ($user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB mới có quyền truy cập trang cài đặt.');
        }

        $fields = Field::orderBy('name')->get();

        // Simple stats for right sidebar
        $clubStats = [
            'members' => ClubMember::where('club_id', $clubId)->whereIn('status', ['approved', 'active'])->count(),
            'events' => Event::where('club_id', $clubId)->count(),
            'posts' => Post::where('club_id', $clubId)->count(),
        ];

        return view('student.club-management.settings', [
            'user' => $user,
            'club' => $club,
            'clubId' => $clubId,
            'fields' => $fields,
            'clubStats' => $clubStats,
        ]);
    }

    /**
     * Update club settings
     */
    public function updateClubSettings(Request $request, $clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        if ($user->getPositionInClub($clubId) !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới có quyền cập nhật cài đặt.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_members' => 'required|integer|min:1|max:1000',
            'field_id' => 'nullable|exists:fields,id',
            'logo' => 'nullable|image|max:2048',
        ]);

        $update = [
            'name' => $request->name,
            'description' => $request->description,
            'max_members' => $request->max_members,
        ];
        if ($request->filled('field_id')) {
            $update['field_id'] = $request->field_id;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $ext = $logo->getClientOriginalExtension();
            $filename = time() . '_' . $clubId . '.' . $ext;
            $dir = public_path('uploads/clubs/logos');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $logo->move($dir, $filename);
            // delete old
            if ($club->logo && file_exists(public_path($club->logo))) {
                @unlink(public_path($club->logo));
            }
            $update['logo'] = 'uploads/clubs/logos/' . $filename;
        }

        $club->update($update);

        return redirect()
            ->route('student.club-management.settings', ['club' => $clubId])
            ->with('success', 'Đã cập nhật cài đặt CLB thành công.');
    }


    /**
     * Update member permissions in a club
     */
    public function updateMemberPermissions(Request $request, $clubId, $memberId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Bạn không có quyền cập nhật phân quyền.');
        }

        $request->validate([
            'position' => 'required|in:member,treasurer,vice_president',
        ]);

        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->firstOrFail();

        if (!$clubMember->user || $clubMember->user_id === $user->id) {
            return redirect()->back()->with('error', 'Không hợp lệ.');
        }

        // Không cho phép thay đổi position của leader hoặc owner
        if (in_array($clubMember->position, ['leader', 'owner']) && $clubMember->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Không thể thay đổi vai trò của Trưởng CLB hoặc Chủ nhiệm.');
        }

        $newPosition = $request->input('position');
        $oldPosition = $clubMember->position;

        try {
            DB::transaction(function () use ($clubMember, $clubId, $newPosition, $oldPosition) {
                // Kiểm tra giới hạn vai trò
                $checkResult = $this->enforceRoleLimitsForStudent($clubMember->user_id, $clubId, $newPosition, $oldPosition);
                if (isset($checkResult['error'])) {
                    throw new \Exception($checkResult['error']);
                }
                if (isset($checkResult['position'])) {
                    $newPosition = $checkResult['position'];
                }

                // Xóa tất cả quyền cũ
                DB::table('user_permissions_club')
                    ->where('user_id', $clubMember->user_id)
                    ->where('club_id', $clubId)
                    ->delete();
                    
                // Cấp quyền mặc định theo vai trò
                $permissionNames = [];
                
                switch ($newPosition) {
                    case 'vice_president':
                        // Phó CLB: 4 quyền
                        $permissionNames = ['quan_ly_thanh_vien', 'tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'];
                        break;
                        
                    case 'treasurer':
                        // Thủ quỹ: 2 quyền
                        $permissionNames = ['quan_ly_quy', 'xem_bao_cao'];
                        break;
                        
                    case 'member':
                    default:
                        // Thành viên: Chỉ xem báo cáo
                        $permissionNames = ['xem_bao_cao'];
                        break;
                    }

                // Thêm quyền mới
                $permissions = Permission::whereIn('name', $permissionNames)->get();
                foreach ($permissions as $permission) {
                    DB::table('user_permissions_club')->insert([
                        'user_id' => $clubMember->user_id,
                        'club_id' => $clubId,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Cập nhật position - SỬ DỤNG DB::table để đảm bảo cập nhật trực tiếp vào database
                DB::table('club_members')
                    ->where('id', $clubMember->id)
                    ->update(['position' => $newPosition]);
                
                // Refresh lại model để đảm bảo dữ liệu mới nhất
                $clubMember->refresh();

                \Log::info("Updated position for user {$clubMember->user_id} in club {$clubId}: {$oldPosition} -> {$newPosition}");
            });

            return redirect()->back()->with('success', 'Đã cập nhật thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Áp dụng giới hạn vai trò: 2 Vice President, 1 Treasurer
     */
    private function enforceRoleLimitsForStudent($userId, $clubId, $newPosition, $oldPosition = null)
    {
        $result = ['position' => $newPosition];
        
        // KIỂM TRA: User đã là thủ quỹ, phó CLB hoặc chủ nhiệm ở CLB khác chưa?
        if (in_array($newPosition, ['treasurer', 'vice_president'])) {
            $existingRole = ClubMember::where('user_id', $userId)
                ->where('club_id', '!=', $clubId)
                        ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'treasurer', 'vice_president', 'chunhiem'])
                ->first();
                    
            if ($existingRole) {
                $existingClub = Club::find($existingRole->club_id);
                $result['error'] = "Tài khoản này đã là thủ quỹ/phó CLB/chủ nhiệm ở CLB '{$existingClub->name}'. Ở CLB này chỉ được làm thành viên.";
                $result['position'] = 'member';
                return $result;
            }
        }
        
        if ($newPosition === 'vice_president') {
            // Được có 2 Vice President
                    $vicePresidentCount = ClubMember::where('club_id', $clubId)
                        ->where('position', 'vice_president')
                ->where('user_id', '!=', $userId)
                ->whereIn('status', ['approved', 'active'])
                        ->count();
                    
            if ($vicePresidentCount >= 2) {
                // Nếu đã có 2 phó CLB, không cho phép thêm
                $result['error'] = "CLB này đã có đủ 2 phó CLB. Vui lòng bỏ 1 phó CLB trước khi thêm mới.";
                $result['position'] = $oldPosition ?: 'member';
                return $result;
            }
            
        } elseif ($newPosition === 'treasurer') {
            // Chỉ được có 1 Treasurer
            $existingTreasurer = ClubMember::where('club_id', $clubId)
                ->where('position', 'treasurer')
                ->where('user_id', '!=', $userId)
                ->whereIn('status', ['approved', 'active'])
                ->first();
                
            if ($existingTreasurer) {
                // Chuyển thủ quỹ cũ về thành viên
                DB::table('club_members')
                    ->where('user_id', $existingTreasurer->user_id)
                    ->where('club_id', $clubId)
                    ->update(['position' => 'member']);
                
                // Xóa quyền của thủ quỹ cũ, chỉ giữ xem_bao_cao
                $xemBaoCaoPerm = Permission::where('name', 'xem_bao_cao')->first();
                DB::table('user_permissions_club')
                    ->where('user_id', $existingTreasurer->user_id)
                    ->where('club_id', $clubId)
                    ->delete();
                if ($xemBaoCaoPerm) {
                    DB::table('user_permissions_club')->insert([
                        'user_id' => $existingTreasurer->user_id,
                        'club_id' => $clubId,
                        'permission_id' => $xemBaoCaoPerm->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                \Log::info("Chuyển thủ quỹ cũ {$existingTreasurer->user_id} về thành viên trong CLB {$clubId}");
            }
        }
        
        return $result;
    }

    /**
     * Show form to create fund request
     */
    public function fundRequestCreate()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }

        $club = $user->clubs->first();
        $clubId = $club->id;
        $position = $user->getPositionInClub($clubId);

        // Chỉ leader mới được tạo yêu cầu
        if ($position !== 'leader') {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB mới có quyền tạo yêu cầu cấp kinh phí.');
        }

        // Lấy các sự kiện của CLB
        $events = Event::where('club_id', $clubId)
            ->orderBy('start_time', 'desc')
            ->get();

        return view('student.club-management.fund-request-create', [
            'user' => $user,
            'club' => $club,
            'events' => $events,
        ]);
    }

    /**
     * Store fund request
     */
    public function fundRequestStore(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }

        $club = $user->clubs->first();
        $clubId = $club->id;
        $position = $user->getPositionInClub($clubId);

        if (!in_array($position, ['leader', 'vice_president', 'treasurer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền tạo yêu cầu cấp kinh phí.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requested_amount' => 'required|numeric|min:0',
            'event_id' => 'required|exists:events,id',
            'expense_items' => 'nullable|array',
            'expense_items.*.item' => 'required_with:expense_items|string|max:255',
            'expense_items.*.amount' => 'required_with:expense_items|numeric|min:0',
        ]);

        // Kiểm tra sự kiện thuộc về CLB của user
        $event = Event::findOrFail($request->event_id);
        if ($event->club_id != $clubId) {
            return redirect()->back()
                ->with('error', 'Sự kiện không thuộc về CLB của bạn.')
                ->withInput();
        }

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'requested_amount' => $request->requested_amount,
            'event_id' => $request->event_id,
            'club_id' => $clubId,
            'created_by' => $user->id,
            'status' => 'pending',
            'expense_items' => $request->expense_items ?? null,
        ];

        // Xử lý tài liệu hỗ trợ
        if ($request->hasFile('supporting_documents')) {
            $documents = [];
            $uploadPath = public_path('storage/fund-requests');
            
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            foreach ($request->file('supporting_documents') as $index => $document) {
                if (!$document || !$document->isValid() || $document->getSize() == 0) {
                    continue;
                }
                
                try {
                    $filename = time() . '_' . $index . '_' . $document->getClientOriginalName();
                    $document->move($uploadPath, $filename);
                    $documents[] = 'fund-requests/' . $filename;
                } catch (\Exception $e) {
                    continue;
                }
            }
            $data['supporting_documents'] = $documents;
        }

        try {
            FundRequest::create($data);
            return redirect()->route('student.club-management.fund-requests')
                ->with('success', 'Yêu cầu cấp kinh phí đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * List fund requests for student's club
     */
    public function fundRequests(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }

        $club = $user->clubs->first();
        $clubId = $club->id;

        $query = FundRequest::with(['event', 'creator', 'approver', 'settler'])
            ->where('club_id', $clubId);

        // Filter by settlement status
        if ($request->filled('settlement') && $request->settlement === 'settled') {
            $query->where('settlement_status', 'settled');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('event', function($eventQuery) use ($search) {
                      $eventQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('student.club-management.fund-requests', [
            'user' => $user,
            'club' => $club,
            'requests' => $requests,
        ]);
    }

    /**
     * Show fund request detail
     */
    public function fundRequestShow($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }

        $club = $user->clubs->first();
        $fundRequest = FundRequest::with(['event', 'club', 'creator', 'approver', 'settler'])
            ->where('id', $id)
            ->where('club_id', $club->id)
            ->firstOrFail();

        return view('student.club-management.fund-request-show', [
            'user' => $user,
            'club' => $club,
            'fundRequest' => $fundRequest,
        ]);
    }

    /**
     * Edit fund request (only for rejected requests)
     */
    public function fundRequestEdit($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }

        $club = $user->clubs->first();
        $fundRequest = FundRequest::with(['event', 'club'])
            ->where('id', $id)
            ->where('club_id', $club->id)
            ->firstOrFail();

        // Chỉ cho phép sửa yêu cầu bị từ chối
        if ($fundRequest->status !== 'rejected') {
            return redirect()->route('student.club-management.fund-requests.show', $id)
                ->with('error', 'Chỉ có thể sửa yêu cầu đã bị từ chối.');
        }

        // Kiểm tra quyền (chỉ leader mới được sửa)
        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->route('student.club-management.fund-requests.show', $id)
                ->with('error', 'Chỉ Trưởng CLB mới được sửa yêu cầu.');
        }

        $events = Event::where('club_id', $club->id)
            ->orderBy('start_time', 'desc')
            ->get();

        return view('student.club-management.fund-request-edit', [
            'user' => $user,
            'club' => $club,
            'fundRequest' => $fundRequest,
            'events' => $events,
        ]);
    }

    /**
     * Update fund request
     */
    public function fundRequestUpdate(Request $request, $id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }

        $club = $user->clubs->first();
        $fundRequest = FundRequest::where('id', $id)
            ->where('club_id', $club->id)
            ->firstOrFail();

        // Chỉ cho phép sửa yêu cầu bị từ chối
        if ($fundRequest->status !== 'rejected') {
            return redirect()->route('student.club-management.fund-requests.show', $id)
                ->with('error', 'Chỉ có thể sửa yêu cầu đã bị từ chối.');
        }

        // Kiểm tra quyền
        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->route('student.club-management.fund-requests.show', $id)
                ->with('error', 'Chỉ Trưởng CLB mới được sửa yêu cầu.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requested_amount' => 'required|numeric|min:0',
            'event_id' => 'required|exists:events,id',
            'expense_items' => 'nullable|array',
            'expense_items.*.item' => 'required_with:expense_items|string|max:255',
            'expense_items.*.amount' => 'required_with:expense_items|numeric|min:0',
        ]);

        // Kiểm tra sự kiện thuộc về CLB
        $event = Event::findOrFail($request->event_id);
        if ($event->club_id != $club->id) {
            return redirect()->back()
                ->with('error', 'Sự kiện không thuộc về CLB của bạn.')
                ->withInput();
        }

        // Cập nhật thông tin
        $fundRequest->title = $request->title;
        $fundRequest->description = $request->description;
        $fundRequest->requested_amount = $request->requested_amount;
        $fundRequest->event_id = $request->event_id;
        $fundRequest->expense_items = $request->expense_items ?? null;

        // Xử lý tài liệu hỗ trợ mới (nếu có)
        if ($request->hasFile('supporting_documents')) {
            $documents = $fundRequest->supporting_documents ?? [];
            $uploadPath = public_path('storage/fund-requests');
            
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            foreach ($request->file('supporting_documents') as $index => $document) {
                if (!$document || !$document->isValid() || $document->getSize() == 0) {
                    continue;
                }
                
                try {
                    $filename = time() . '_' . $index . '_' . $document->getClientOriginalName();
                    $document->move($uploadPath, $filename);
                    $documents[] = 'fund-requests/' . $filename;
                } catch (\Exception $e) {
                    continue;
                }
            }
            $fundRequest->supporting_documents = $documents;
        }

        $fundRequest->save();

        return redirect()->route('student.club-management.fund-requests.show', $id)
            ->with('success', 'Yêu cầu đã được cập nhật thành công! Bạn có thể gửi lại để duyệt.');
    }

    /**
     * Resubmit fund request (reset status to pending)
     */
    public function fundRequestResubmit($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }

        $club = $user->clubs->first();
        $fundRequest = FundRequest::where('id', $id)
            ->where('club_id', $club->id)
            ->firstOrFail();

        // Chỉ cho phép gửi lại yêu cầu bị từ chối
        if ($fundRequest->status !== 'rejected') {
            return redirect()->route('student.club-management.fund-requests.show', $id)
                ->with('error', 'Chỉ có thể gửi lại yêu cầu đã bị từ chối.');
        }

        // Kiểm tra quyền
        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->route('student.club-management.fund-requests.show', $id)
                ->with('error', 'Chỉ Trưởng CLB mới được gửi lại yêu cầu.');
        }

        // Reset status về pending và xóa thông tin duyệt
        $fundRequest->status = 'pending';
        $fundRequest->approved_by = null;
        $fundRequest->approved_at = null;
        $fundRequest->approved_amount = null;
        $fundRequest->approval_notes = null;
        // Giữ lại rejection_reason để người dùng biết lý do từ chối trước đó
        $fundRequest->settlement_status = 'pending';
        $fundRequest->save();

        return redirect()->route('student.club-management.fund-requests.show', $id)
            ->with('success', 'Yêu cầu đã được gửi lại thành công! Đang chờ duyệt.');
    }

    /**
     * Remove a member from the club
     */
    public function removeMember(Request $request, $clubId, $memberId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa thành viên.');
        }

        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->firstOrFail();

        if (!$clubMember->user || $clubMember->user_id === $user->id) {
            return redirect()->back()->with('error', 'Không hợp lệ.');
        }
        if (in_array($clubMember->position, ['leader','owner'])) {
            return redirect()->back()->with('error', 'Không thể xóa Trưởng CLB/Chủ nhiệm.');
        }

        $reason = trim((string) $request->input('reason'));
        DB::transaction(function () use ($clubMember, $clubId, $reason) {
            DB::table('user_permissions_club')
                ->where('user_id', $clubMember->user_id)
                ->where('club_id', $clubId)
                ->delete();
            $clubMember->update([
                'status' => 'inactive',
                'left_at' => now(),
                'left_reason' => $reason ?: null,
            ]);
            $clubMember->delete();
        });

        return redirect()->back()->with('success', 'Đã xóa thành viên.');
    }

    /**
     * List club join requests
     */
    public function clubJoinRequests($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president', 'treasurer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền xem đơn tham gia CLB này.');
        }

        $requests = JoinReq::with('user')
            ->where('club_id', $clubId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('student.club-management.join-requests', [
            'user' => $user,
            'club' => $club,
            'clubId' => $clubId,
            'requests' => $requests,
        ]);
    }

    public function approveClubJoinRequest($clubId, $requestId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president', 'treasurer'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền duyệt đơn.');
        }
        $req = JoinReq::where('club_id', $clubId)->findOrFail($requestId);
        if ($req->status === 'pending') {
            $req->status = 'approved';
            $req->reviewed_by = $user->id;
            $req->reviewed_at = now();
            $req->save();
            // thêm vào bảng club_members nếu cần, tùy logic hiện có
        }
        return redirect()->back()->with('success', 'Đã duyệt đơn tham gia.');
    }

    public function rejectClubJoinRequest(Request $request, $clubId, $requestId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president', 'treasurer'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền từ chối đơn.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);
        
        $req = JoinReq::with(['user', 'club'])->where('club_id', $clubId)->findOrFail($requestId);
        if ($req->status === 'pending') {
            $rejectionReason = $request->input('rejection_reason');
            $req->status = 'rejected';
            $req->reviewed_by = $user->id;
            $req->reviewed_at = now();
            $req->rejection_reason = $rejectionReason;
            $req->save();
            
            // Tạo thông báo cho user
            $notification = \App\Models\Notification::create([
                'sender_id' => $user->id,
                'type' => 'club_rejection',
                'related_id' => $req->id,
                'related_type' => 'ClubJoinRequest',
                'title' => 'Đơn tham gia CLB đã bị từ chối',
                'message' => 'Đơn tham gia CLB "' . ($req->club->name ?? 'CLB') . '" của bạn đã bị từ chối. ' . ($rejectionReason ? 'Lý do: ' . $rejectionReason : ''),
            ]);

            // Gửi thông báo đến user
            \App\Models\NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_type' => 'user',
                'target_id' => $req->user_id,
            ]);
            
            // Gửi email thông báo
            try {
                \Mail::to($req->user->email)->send(new \App\Mail\ClubJoinRequestRejected($req, $rejectionReason));
            } catch (\Exception $e) {
                \Log::error('Failed to send rejection email: ' . $e->getMessage());
                // Vẫn tiếp tục dù gửi email thất bại
            }
        }
        return redirect()->back()->with('success', 'Đã từ chối đơn tham gia và gửi thông báo qua email.');
    }

    /**
     * Club reports dashboard (fund + activities)
     */
    public function clubReports(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Get club from query parameter or use managed club
        $clubId = $request->query('club');
        if ($clubId) {
            $club = Club::findOrFail($clubId);
            // Kiểm tra user có phải thành viên của CLB này không
            $isMember = ClubMember::where('club_id', $clubId)
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'active'])
                ->exists();
            
            if (!$isMember) {
                return redirect()->route('student.clubs.show', $clubId)
                    ->with('error', 'Bạn cần là thành viên của CLB này để xem báo cáo.');
            }
        } else {
            // Tìm CLB mà user có role quản lý
            $clubMembership = $this->getManagedClub($user);
            
            if (!$clubMembership) {
                // Nếu không có CLB nào có role quản lý, lấy CLB đầu tiên để hiển thị thông báo
            if ($user->clubs->isEmpty()) {
                return redirect()->route('student.clubs.index')
                    ->with('error', 'Bạn chưa tham gia CLB nào.');
            }
            $club = $user->clubs->first();
            } else {
                $club = $clubMembership->club;
            }
        }
        $clubId = $club->id;

        // Kiểm tra quyền xem báo cáo (chỉ leader/treasurer có quyền xem thông tin quỹ)
        $canViewReports = $user->hasPermission('xem_bao_cao', $clubId);
        $userPosition = $user->getPositionInClub($clubId);
        $isLeaderOrOfficer = in_array($userPosition, ['leader', 'vice_president', 'treasurer']);

        // Simple stats
        $totalMembers = ClubMember::where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->count();
        $newMembersThisMonth = ClubMember::where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $totalEvents = Event::where('club_id', $clubId)->count();
        $upcomingEvents = Event::where('club_id', $clubId)
            ->where('start_time', '>=', now())
            ->count();

        // Fund stats - hiển thị cho tất cả thành viên có quyền xem báo cáo
        $fund = Fund::where('club_id', $clubId)->first();
        $fundId = $fund?->id ?? null;
        $totalIncome = 0;
        $totalExpense = 0;
        $balance = 0;
        $totalTransactions = 0;
        $publicExpenses = collect(); // Danh sách chi tiêu công khai cho thành viên xem

        if ($fundId) {
            if ($canViewReports) {
                // Tất cả thành viên có quyền xem báo cáo: xem thông tin quỹ (chỉ xem, không tạo)
                $totalIncome = FundTransaction::where('fund_id', $fundId)
                    ->where('type', 'income')
                    ->where('status', 'approved')
                    ->sum('amount');
                $totalExpense = FundTransaction::where('fund_id', $fundId)
                    ->where('type', 'expense')
                    ->where('status', 'approved')
                    ->sum('amount');
                // Số dư = Số tiền ban đầu + Tổng thu - Tổng chi (giống logic admin)
                $balance = (int) ($fund->initial_amount ?? 0) + (int) $totalIncome - (int) $totalExpense;
                // Tổng số giao dịch
                $totalTransactions = FundTransaction::where('fund_id', $fundId)
                    ->where('status', 'approved')
                    ->count();
            } else {
                // Thành viên thông thường: chỉ xem các khoản chi đã được duyệt (công khai)
                $publicExpenses = FundTransaction::with(['fund', 'creator', 'approver'])
                    ->where('fund_id', $fundId)
                    ->where('type', 'expense')
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc')
                    ->limit(20) // Giới hạn 20 giao dịch gần nhất
                    ->get();
                // Tính tổng chi (chỉ để hiển thị, không có số dư)
                $totalExpense = FundTransaction::where('fund_id', $fundId)
                    ->where('type', 'expense')
                    ->where('status', 'approved')
                    ->sum('amount');
            }
        }

        // Member structure (leader/treasurer/member) simple distribution
        $structureMap = [
            'leader' => 'Trưởng',
            'vice_president' => 'Phó',
            'treasurer' => 'Thủ quỹ',
            'member' => 'Thành viên',
        ];
        $memberStructureCounts = ClubMember::where('club_id', $clubId)
            ->whereIn('status', ['approved', 'active'])
            ->select('position', DB::raw('COUNT(*) as cnt'))
            ->groupBy('position')
            ->pluck('cnt', 'position')
            ->toArray();
        $memberStructure = [
            'labels' => array_values($structureMap),
            'data' => [
                $memberStructureCounts['leader'] ?? 0,
                $memberStructureCounts['vice_president'] ?? 0,
                $memberStructureCounts['treasurer'] ?? 0,
                $memberStructureCounts['member'] ?? 0,
            ],
        ];

        // Events by month (last 12 months)
        $labels12 = [];
        $events12 = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $labels12[] = $d->format('m/Y');
            $events12[] = Event::where('club_id', $clubId)
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->count();
        }

        // Member growth (last 3 months cumulative)
        $labels3 = [];
        $data3 = [];
        for ($i = 2; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $labels3[] = $d->format('m/Y');
            $data3[] = ClubMember::where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->where('created_at', '<=', $d->copy()->endOfMonth())
                ->count();
        }

        // Activity trend (6 months): new members & new events in month
        $labels6 = [];
        $newMembers6 = [];
        $newEvents6 = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $labels6[] = $d->format('m/Y');
            $newMembers6[] = ClubMember::where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->count();
            $newEvents6[] = Event::where('club_id', $clubId)
                ->whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->count();
        }

        $stats = [
            'totalMembers' => $totalMembers,
            'newMembersThisMonth' => $newMembersThisMonth,
            'totalEvents' => $totalEvents,
            'upcomingEvents' => $upcomingEvents,
            'fund' => [
                'totalIncome' => (int) $totalIncome,
                'totalExpense' => (int) $totalExpense,
                'balance' => (int) $balance,
                'totalTransactions' => (int) ($totalTransactions ?? 0),
            ],
            'memberStructure' => $memberStructure,
            'eventsByMonth' => ['labels' => $labels12, 'data' => $events12],
            'memberGrowth' => ['labels' => $labels3, 'data' => $data3],
            'activityTrend' => [
                'labels' => $labels6,
                'newMembers' => $newMembers6,
                'newEvents' => $newEvents6,
            ],
        ];

        return view('student.club-management.reports', compact('user', 'club', 'stats', 'canViewReports', 'isLeaderOrOfficer', 'publicExpenses'));
    }

    /**
     * Club posts and announcements management
     */
    public function clubManagementPosts($clubId, Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail($clubId);
        
        // Kiểm tra quyền
        $canPostAnnouncement = $user->hasPermission('dang_thong_bao', $clubId);
        $userPosition = $user->getPositionInClub($clubId);
        $isLeaderOrOfficer = in_array($userPosition, ['leader', 'vice_president', 'treasurer']);
        
        if (!$canPostAnnouncement && !$isLeaderOrOfficer) {
            return redirect()->route('student.clubs.show', $clubId)
                ->with('error', 'Bạn không có quyền quản lý bài viết của CLB này.');
        }

        // Xác định tab hiện tại
        $activeTab = $request->query('tab', 'posts');

        // Query posts (type='post')
        $postsQuery = Post::with(['user', 'attachments'])
            ->where('club_id', $clubId)
            ->where('type', 'post')
            ->where('status', '!=', 'deleted');

        if ($request->filled('search')) {
            $postsQuery->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $postsQuery->where('status', $request->status);
        }

        $posts = $postsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'posts_page');

        // Query announcements (type='announcement')
        $announcementsQuery = Post::with(['user'])
            ->where('club_id', $clubId)
            ->where('type', 'announcement')
            ->where('status', '!=', 'deleted');

        if ($request->filled('announcement_search')) {
            $announcementsQuery->where('title', 'like', '%' . $request->announcement_search . '%');
        }

        if ($request->filled('announcement_status') && $request->announcement_status !== 'all') {
            $announcementsQuery->where('status', $request->announcement_status);
        }

        $announcements = $announcementsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'announcements_page');

        return view('student.club-management.posts', compact(
            'user',
            'club',
            'clubId',
            'activeTab',
            'posts',
            'announcements',
            'canPostAnnouncement'
        ));
    }

    /**
     * Display resources management page for club
     */
    public function clubManagementResources($clubId, Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail($clubId);
        
        // Kiểm tra quyền
        $userPosition = $user->getPositionInClub($clubId);
        $isLeaderOrOfficer = in_array($userPosition, ['leader', 'vice_president', 'treasurer']);
        
        if (!$isLeaderOrOfficer) {
            return redirect()->route('student.clubs.show', $clubId)
                ->with('error', 'Bạn không có quyền quản lý tài nguyên của CLB này.');
        }

        // Query resources
        $resourcesQuery = ClubResource::with(['user', 'images', 'files'])
            ->where('club_id', $clubId)
            ->where('status', '!=', 'deleted');

        if ($request->filled('search')) {
            $resourcesQuery->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $resourcesQuery->where('status', $request->status);
        }

        $resources = $resourcesQuery->orderBy('created_at', 'desc')->paginate(10);

        return view('student.club-management.resources', compact(
            'user',
            'club',
            'clubId',
            'resources'
        ));
    }

    /**
     * Show form to create new resource
     */
    public function createResource($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail($clubId);
        
        // Kiểm tra quyền
        $userPosition = $user->getPositionInClub($clubId);
        $isLeaderOrOfficer = in_array($userPosition, ['leader', 'vice_president', 'treasurer']);
        
        if (!$isLeaderOrOfficer) {
            return redirect()->route('student.clubs.show', $clubId)
                ->with('error', 'Bạn không có quyền tạo tài nguyên cho CLB này.');
        }

        return view('student.club-management.resources.create', compact(
            'user',
            'club',
            'clubId'
        ));
    }

    /**
     * Store newly created resource
     */
    public function storeResource($clubId, Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail($clubId);
        
        // Kiểm tra quyền
        $userPosition = $user->getPositionInClub($clubId);
        $isLeaderOrOfficer = in_array($userPosition, ['leader', 'vice_president', 'treasurer']);
        
        if (!$isLeaderOrOfficer) {
            return redirect()->route('student.clubs.show', $clubId)
                ->with('error', 'Bạn không có quyền tạo tài nguyên cho CLB này.');
        }

        try {
            $request->validate([
                'title' => 'required|string|min:5|max:255',
                'description' => 'nullable|string|max:1000',
                'status' => 'required|in:active,inactive,archived',
                'files' => 'nullable|array|max:10',
                'files.*' => 'file|mimes:doc,docx,xls,xlsx,xlsm,pdf,ppt,pptx|max:20480', // 20MB per file
                'images' => 'nullable|array|max:10',
                'images.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,avi,mov|max:102400', // 100MB per file
                'external_link' => 'nullable|url|max:500',
                'tags' => 'nullable|string'
            ], [
                'title.required' => 'Tiêu đề là bắt buộc.',
                'title.min' => 'Tiêu đề phải có ít nhất :min ký tự.',
                'title.max' => 'Tiêu đề không được vượt quá :max ký tự.',
                'files.max' => 'Tối đa 10 file.',
                'files.*.mimes' => 'File phải có định dạng: DOC, DOCX, XLS, XLSX, XLSM, PDF, PPT, PPTX.',
                'files.*.max' => 'Mỗi file không được vượt quá 20MB.',
                'images.max' => 'Tối đa 10 file.',
                'images.*.mimes' => 'File phải có định dạng: JPEG, PNG, JPG, GIF, WEBP, MP4, AVI, MOV.',
                'images.*.max' => 'Mỗi file không được vượt quá 100MB.',
                'external_link.url' => 'Link không hợp lệ.'
            ]);

            $title = $request->input('title');
            $tags = $request->tags ? explode(',', $request->tags) : null;
            
            // Tạo slug unique
            $baseSlug = Str::slug($title);
            $slug = $baseSlug . '-' . time();
            $counter = 1;
            while (ClubResource::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . time() . '-' . $counter;
                $counter++;
            }
            
            $resource = ClubResource::create([
                'title' => $title,
                'slug' => $slug,
                'description' => $request->description,
                'resource_type' => 'other',
                'club_id' => $clubId, // Tự động lấy từ route
                'user_id' => $user->id,
                'status' => $request->status,
                'external_link' => $request->external_link,
                'tags' => $tags
            ]);

            // Handle file album upload
            if ($request->hasFile('files')) {
                $this->handleResourceFileUpload($resource, $request->file('files'));
            }

            // Handle image album upload
            if ($request->hasFile('images')) {
                $this->handleResourceImageUpload($resource, $request->file('images'));
            }

            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('success', 'Tài nguyên đã được tạo thành công!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('StoreResource Error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tạo tài nguyên: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Handle file upload for resources
     */
    private function handleResourceFileUpload($resource, $files)
    {
        foreach ($files as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('club-resources/files', $filename, 'public');
            
            \App\Models\ClubResourceFile::create([
                'club_resource_id' => $resource->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'sort_order' => $index,
                'is_primary' => $index === 0
            ]);
        }
    }

    /**
     * Handle image upload for resources
     */
    private function handleResourceImageUpload($resource, $images)
    {
        foreach ($images as $index => $image) {
            if (!$image || !$image->isValid()) {
                continue;
            }

            $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('club-resources/images', $filename, 'public');
            
            // Tạo thumbnail nếu là ảnh
            $thumbnailPath = null;
            if (str_starts_with($image->getMimeType(), 'image/')) {
                try {
                    $thumbnailPath = $this->createImageThumbnail($path, $filename);
                } catch (\Exception $e) {
                    \Log::warning('Failed to create thumbnail: ' . $e->getMessage());
                }
            }
            
            \App\Models\ClubResourceImage::create([
                'club_resource_id' => $resource->id,
                'image_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'image_name' => $image->getClientOriginalName(),
                'image_type' => $image->getMimeType(),
                'image_size' => $image->getSize(),
                'sort_order' => $index,
                'is_primary' => $index === 0
            ]);
        }
    }

    /**
     * Create thumbnail for image
     */
    private function createImageThumbnail($originalPath, $filename)
    {
        $fullPath = storage_path('app/public/' . $originalPath);
        
        if (!file_exists($fullPath)) {
            return null;
        }

        // Kiểm tra GD extension
        if (!extension_loaded('gd')) {
            \Log::warning('GD extension not loaded, skipping thumbnail creation');
            return null;
        }

        $imageInfo = @getimagesize($fullPath);
        if (!$imageInfo) {
            return null;
        }

        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Tạo thumbnail path
        $thumbnailPath = 'club-resources/thumbnails/' . 'thumb_' . $filename;
        $thumbnailFullPath = storage_path('app/public/' . $thumbnailPath);
        
        // Tạo thư mục nếu chưa có
        $thumbnailDir = dirname($thumbnailFullPath);
        if (!is_dir($thumbnailDir)) {
            @mkdir($thumbnailDir, 0755, true);
        }

        // Tính toán kích thước thumbnail (max 300x300)
        $maxSize = 300;
        $ratio = min($maxSize / $width, $maxSize / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Tạo image resource từ file gốc
        $sourceImage = null;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $sourceImage = @imagecreatefromjpeg($fullPath);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($fullPath);
                break;
            case 'image/gif':
                $sourceImage = @imagecreatefromgif($fullPath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $sourceImage = @imagecreatefromwebp($fullPath);
                }
                break;
            default:
                return null;
        }

        if (!$sourceImage) {
            return null;
        }

        // Tạo thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency cho PNG và GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Lưu thumbnail
        $saved = false;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $saved = @imagejpeg($thumbnail, $thumbnailFullPath, 85);
                break;
            case 'image/png':
                $saved = @imagepng($thumbnail, $thumbnailFullPath, 9);
                break;
            case 'image/gif':
                $saved = @imagegif($thumbnail, $thumbnailFullPath);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    $saved = @imagewebp($thumbnail, $thumbnailFullPath, 85);
                }
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($thumbnail);

        return $saved ? $thumbnailPath : null;
    }
    
    /**
     * Fund transactions list for student (read-only)
     */
    public function fundTransactions(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Lấy CLB từ query parameter hoặc từ managed club
        $clubId = $request->query('club');
        if ($clubId) {
            $club = Club::findOrFail($clubId);
            // Kiểm tra user có phải thành viên của CLB này không
            $isMember = ClubMember::where('club_id', $clubId)
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'active'])
                ->exists();
            
            if (!$isMember) {
                return redirect()->route('student.clubs.show', $clubId)
                    ->with('error', 'Bạn cần là thành viên của CLB này để xem giao dịch quỹ.');
            }
        } else {
            // Tìm CLB mà user có role quản lý (leader, vice_president, treasurer)
            $clubMembership = $this->getManagedClub($user);
            
            if (!$clubMembership) {
            return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không có quyền quản lý CLB nào.');
        }
            
            $club = $clubMembership->club;
        }

        // Permission: reuse view report permission
        if (!$user->hasPermission('xem_bao_cao', $club->id)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền xem giao dịch quỹ.');
        }

        $fund = Fund::where('club_id', $club->id)->first();
        $position = $user->getPositionInClub($club->id);
        if (!$fund) {
            return view('student.club-management.fund-transactions', [
                'user' => $user,
                'club' => $club,
                'transactions' => collect(),
                'summary' => ['income' => 0, 'expense' => 0, 'balance' => 0],
                'filterType' => $request->input('type'),
                'position' => $position,
            ]);
        }

        $query = FundTransaction::where('fund_id', $fund->id)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc');

        $filterType = $request->input('type'); // income | expense | null
        if (in_array($filterType, ['income', 'expense'])) {
            $query->where('type', $filterType);
        }

        $transactions = $query->paginate(20)->withQueryString();

        $income = FundTransaction::where('fund_id', $fund->id)
            ->where('status', 'approved')->where('type', 'income')->sum('amount');
        $expense = FundTransaction::where('fund_id', $fund->id)
            ->where('status', 'approved')->where('type', 'expense')->sum('amount');
        // Số dư = Số tiền ban đầu + Tổng thu - Tổng chi (giống logic admin)
        $summary = [
            'income' => (int) $income,
            'expense' => (int) $expense,
            'balance' => (int) ($fund->initial_amount ?? 0) + (int) $income - (int) $expense,
        ];

        $position = $user->getPositionInClub($club->id);

        return view('student.club-management.fund-transactions', [
            'user' => $user,
            'club' => $club,
            'transactions' => $transactions,
            'summary' => $summary,
            'filterType' => $filterType,
            'position' => $position,
        ]);
    }

    /**
     * Show create transaction form
     */
    public function fundTransactionCreate(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        
        // Lấy CLB từ query parameter hoặc từ managed club
        $clubId = $request->query('club');
        if ($clubId) {
            $club = Club::findOrFail($clubId);
            // Kiểm tra user có phải thành viên của CLB này không
            $isMember = ClubMember::where('club_id', $clubId)
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'active'])
                ->exists();
            
            if (!$isMember) {
                return redirect()->route('student.clubs.show', $clubId)
                    ->with('error', 'Bạn cần là thành viên của CLB này để tạo giao dịch quỹ.');
            }
            
            $position = $user->getPositionInClub($clubId);
        } else {
            // Tìm CLB mà user có role quản lý
            $clubMembership = $this->getManagedClub($user);
            
            if (!$clubMembership) {
            return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không có quyền quản lý CLB nào.');
        }
            
            $club = $clubMembership->club;
            $position = $clubMembership->position;
        }
        if (!in_array($position, ['leader', 'vice_president', 'treasurer'])) {
            return redirect()->route('student.club-management.fund-transactions')
                ->with('error', 'Bạn không có quyền tạo giao dịch.');
        }
        return view('student.club-management.fund-transaction-create', [
            'user' => $user,
            'club' => $club,
        ]);
    }

    /**
     * Store transaction (leader auto-approved, treasurer pending)
     */
    public function fundTransactionStore(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        // Tìm CLB mà user có role quản lý
        $clubMembership = $this->getManagedClub($user);
        
        if (!$clubMembership) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý CLB nào.');
        }
        
        $club = $clubMembership->club;
        $position = $clubMembership->position;
        $fund = Fund::firstOrCreate(['club_id' => $club->id], [
            'name' => 'Quỹ ' . $club->name,
            'current_amount' => 0,
        ]);
        if (!in_array($position, ['leader', 'vice_president', 'treasurer'])) {
            return redirect()->route('student.club-management.fund-transactions')
                ->with('error', 'Bạn không có quyền tạo giao dịch.');
        }

        $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:1|max:999999999999',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'transaction_date' => 'required|date|before_or_equal:today',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:5120',
        ], [
            'type.required' => 'Vui lòng chọn loại giao dịch.',
            'type.in' => 'Loại giao dịch không hợp lệ.',
            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.numeric' => 'Số tiền phải là số.',
            'amount.min' => 'Số tiền phải lớn hơn 0.',
            'amount.max' => 'Số tiền quá lớn (tối đa 999,999,999,999 VNĐ).',
            'category.max' => 'Danh mục không được vượt quá 255 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 5000 ký tự.',
            'transaction_date.required' => 'Vui lòng chọn ngày giao dịch.',
            'transaction_date.date' => 'Ngày giao dịch không hợp lệ.',
            'transaction_date.before_or_equal' => 'Ngày giao dịch không được lớn hơn ngày hiện tại.',
            'attachment.file' => 'File đính kèm không hợp lệ.',
            'attachment.mimes' => 'File đính kèm chỉ chấp nhận định dạng: JPG, JPEG, PNG, GIF, WEBP, PDF.',
            'attachment.max' => 'Kích thước file đính kèm không được vượt quá 5MB.',
        ]);

        // Leader auto-approved, Officer pending
        $status = $position === 'leader' ? 'approved' : 'pending';

        $tx = new FundTransaction();
        $tx->fund_id = $fund->id;
        $tx->type = $request->type;
        $tx->amount = (int) $request->amount;
        $tx->category = $request->category ?: null;
        $tx->description = $request->description ?: null;
        $tx->status = $status;
        $tx->created_by = $user->id;
        // Thiết lập tiêu đề giao dịch để tránh lỗi DB yêu cầu 'title' NOT NULL
        $autoTitle = trim(($request->category ?: ucfirst($request->type)) . ' ' . number_format((int) $request->amount, 0, ',', '.') . ' VNĐ');
        $tx->title = $request->input('title', $autoTitle);
        // Ngày giao dịch: nếu bảng có cột transaction_date thì luôn set
        if (Schema::hasColumn('fund_transactions', 'transaction_date')) {
            $tx->transaction_date = $request->filled('transaction_date')
                ? \Carbon\Carbon::parse($request->transaction_date)
                : now();
        } else {
            // fallback: nếu không có cột transaction_date, có thể set created_at cho khớp hiển thị
            if ($request->filled('transaction_date')) {
                $tx->created_at = \Carbon\Carbon::parse($request->transaction_date);
            }
        }
        // Attachment (optional simple store public/uploads/fund/)
        if ($request->hasFile('attachment')) {
            $dir = public_path('uploads/fund');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $name = time() . '_' . $user->id . '.' . $request->file('attachment')->getClientOriginalExtension();
            $request->file('attachment')->move($dir, $name);
            $storedPath = 'uploads/fund/' . $name;
            // Chỉ gán nếu cột tồn tại trong DB
            if (Schema::hasColumn('fund_transactions', 'attachment_path')) {
                $tx->attachment_path = $storedPath;
            } elseif (Schema::hasColumn('fund_transactions', 'attachment')) {
                // Một số schema có thể dùng 'attachment'
                $tx->attachment = $storedPath;
            } else {
                // Nếu không có cột file, ghép vào description để không mất thông tin
                $tx->description = trim(($tx->description ? $tx->description . ' ' : '') . '(chứng từ: ' . $storedPath . ')');
            }
        }
        $tx->save();

        // If approved and type expense, could validate balance; for simplicity we rely on admin reconciliation
        return redirect()->route('student.club-management.fund-transactions')
            ->with('success', $status === 'approved' ? 'Đã tạo giao dịch và duyệt.' : 'Đã tạo giao dịch, chờ duyệt.');
    }

    /**
     * Approve transaction (leader only)
     */
    public function approveFundTransaction($transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        // Tìm CLB mà user có role quản lý
        $clubMembership = $this->getManagedClub($user);
        
        if (!$clubMembership) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý CLB nào.');
        }
        
        $club = $clubMembership->club;
        $position = $clubMembership->position;
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới được duyệt.');
        }
        $tx = FundTransaction::findOrFail($transactionId);
        $fund = Fund::where('club_id', $club->id)->first();
        if ($tx->fund_id !== ($fund->id ?? null)) {
            return redirect()->back()->with('error', 'Giao dịch không thuộc quỹ CLB của bạn.');
        }
        if ($tx->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch không ở trạng thái chờ duyệt.');
        }
        $tx->status = 'approved';
        $tx->save();
        return redirect()->back()->with('success', 'Đã duyệt giao dịch.');
    }

    /**
     * Reject transaction (leader only)
     */
    public function rejectFundTransaction($transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        // Tìm CLB mà user có role quản lý
        $clubMembership = $this->getManagedClub($user);
        
        if (!$clubMembership) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý CLB nào.');
        }
        
        $club = $clubMembership->club;
        $position = $clubMembership->position;
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới được từ chối.');
        }
        $tx = FundTransaction::findOrFail($transactionId);
        $fund = Fund::where('club_id', $club->id)->first();
        if ($tx->fund_id !== ($fund->id ?? null)) {
            return redirect()->back()->with('error', 'Giao dịch không thuộc quỹ CLB của bạn.');
        }
        if ($tx->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch không ở trạng thái chờ duyệt.');
        }
        $tx->status = 'rejected';
        $tx->save();
        return redirect()->back()->with('success', 'Đã từ chối giao dịch.');
    }

    /**
     * Show transaction detail
     */
    public function fundTransactionShow($transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        $club = $user->clubs->first();
        $tx = FundTransaction::with(['creator', 'approver', 'event'])->findOrFail($transactionId);
        $fund = Fund::where('club_id', $club->id)->first();
        if ($tx->fund_id !== ($fund->id ?? null)) {
            return redirect()->route('student.club-management.fund-transactions')
                ->with('error', 'Giao dịch không thuộc quỹ CLB của bạn.');
        }
        return view('student.club-management.fund-transaction-show', [
            'user' => $user,
            'club' => $club,
            'tx' => $tx,
        ]);
    }

    /**
     * Display posts for students with member-only access control
     */
    public function posts(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Lấy danh sách CLB mà user là thành viên
        $userClubIds = $user->clubs->pluck('id')->toArray();
        
        // Base query cho bài viết có quyền xem
        $baseQuery = Post::with(['club', 'user', 'attachments'])
            ->where(function($q) use ($userClubIds) {
                // Bài viết công khai
                $q->where('status', 'published')
                  // Hoặc bài viết chỉ thành viên CLB mà user là thành viên
                  ->orWhere(function($subQ) use ($userClubIds) {
                      $subQ->where('status', 'members_only')
                           ->whereIn('club_id', $userClubIds);
                  });
            })
            // Chỉ hiển thị bài viết hợp lệ cho trang tin tức
            ->whereIn('type', ['post', 'announcement'])
            // Loại bỏ bài viết đã xóa mềm (ở thùng rác)
            ->whereNull('deleted_at')
            // Loại bỏ bài có status legacy 'deleted' nếu còn
            ->where('status', '!=', 'deleted');

        // Tách query cho bài viết và thông báo
        $postsQuery = (clone $baseQuery);
        $announcementsQuery = (clone $baseQuery);

        // Bài viết: loại trừ announcement (trừ khi filter theo type = announcement)
        if ($request->has('type') && $request->type === 'announcement') {
            $postsQuery->where('type', 'announcement');
        } else {
            $postsQuery->where('type', '!=', 'announcement');
        }

        // Thông báo: chỉ lấy announcement
        $announcementsQuery->where('type', 'announcement');

        // Filter by club cho cả 2
        if ($request->has('club_id') && $request->club_id) {
            $postsQuery->where('club_id', $request->club_id);
            $announcementsQuery->where('club_id', $request->club_id);
        }

        // Search cho cả 2
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $postsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
            $announcementsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Filter by type (all, latest, popular) cho bài viết
        $filter = $request->input('filter', 'all');
        if ($filter === 'latest') {
            $postsQuery->orderBy('created_at', 'desc');
        } elseif ($filter === 'popular') {
            $postsQuery->orderBy('views', 'desc')->orderBy('created_at', 'desc');
        } else {
            $postsQuery->orderBy('created_at', 'desc');
        }

        // Thông báo luôn sắp xếp theo mới nhất
        $announcementsQuery->orderBy('created_at', 'desc');

        $posts = $postsQuery->paginate(3)->withQueryString();
        $announcements = $announcementsQuery->limit(5)->get();
        $clubs = Club::where('status', 'active')->get();
        $search = $request->input('search', '');

        // Lấy thông báo mới nhất để hiển thị modal
        $latestAnnouncement = $announcementsQuery->first();

        // Kiểm tra xem có thông báo mới hơn thông báo đã xem gần nhất không
        // Modal sẽ hiển thị mỗi lần vào trang cho đến khi có thông báo mới (ID lớn hơn)
        $lastViewedAnnouncementId = session('last_viewed_announcement_id', 0);
        $shouldShowModal = false;
        if ($latestAnnouncement) {
            // Hiển thị modal nếu:
            // 1. Chưa có thông báo nào được xem (lastViewedAnnouncementId = 0)
            // 2. Hoặc thông báo hiện tại có ID >= thông báo đã xem (hiển thị lại mỗi lần)
            // Modal sẽ tiếp tục hiển thị cho đến khi có thông báo mới (ID lớn hơn)
            if ($latestAnnouncement->id >= $lastViewedAnnouncementId) {
                $shouldShowModal = true;
            }
        }

        return view('student.posts.index', compact('posts', 'clubs', 'user', 'latestAnnouncement', 'shouldShowModal', 'announcements', 'search', 'filter'));
    }

    /**
     * Display single post with member-only access control
     */
    public function showPost($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $post = Post::with(['club', 'user', 'comments.user', 'attachments'])->findOrFail($id);
        
        // Kiểm tra quyền xem bài viết
        $canView = false;
        
        if ($post->status === 'published') {
            $canView = true;
        } elseif ($post->status === 'members_only') {
            // Kiểm tra xem user có phải thành viên của CLB không
            $userClubIds = $user->clubs->pluck('id')->toArray();
            $canView = in_array($post->club_id, $userClubIds);
        }

        if (!$canView) {
            return redirect()->route('student.posts')->with('error', 'Bạn không có quyền xem bài viết này.');
        }

        // Bài viết liên quan: cùng CLB, đã xuất bản (hoặc members_only nếu là thành viên CLB), loại post/announcement
        $relatedQuery = Post::with(['club', 'user'])
            ->where('id', '!=', $post->id)
            ->where('club_id', $post->club_id)
            ->whereIn('type', ['post', 'announcement'])
            ->orderBy('created_at', 'desc')
            ->limit(6);

        // Quyền xem cho bài viết liên quan
        $relatedQuery->where(function($q) use ($user) {
            $userClubIds = $user->clubs->pluck('id')->toArray();
            $q->where('status', 'published')
              ->orWhere(function($sub) use ($userClubIds) {
                  $sub->where('status', 'members_only')
                      ->whereIn('club_id', $userClubIds);
              });
        });

        $relatedPosts = $relatedQuery->get();

        // Tăng lượt xem (đơn giản, không đếm trùng phiên)
        try {
            $post->increment('views');
            $post->refresh();
        } catch (\Throwable $e) {
            // ignore
        }

        return view('student.posts.show', compact('post', 'user', 'relatedPosts'));
    }

    /**
     * Add a new comment to a post (students)
     */
    public function addPostComment(Request $request, $id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $post = Post::findOrFail($id);

        // Kiểm tra quyền bình luận (phải xem được bài viết)
        $canComment = false;
        if ($post->status === 'published') {
            $canComment = true;
        } elseif ($post->status === 'members_only') {
            $userClubIds = $user->clubs->pluck('id')->toArray();
            $canComment = in_array($post->club_id, $userClubIds);
        }

        if (!$canComment) {
            return redirect()->route('student.posts')->with('error', 'Bạn không có quyền bình luận bài viết này.');
        }

        $request->validate([
            'content' => 'required|string|max:2000'
        ]);

        \App\Models\PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $request->content
        ]);

        return redirect()->route('student.posts.show', $post->id)->with('success', 'Đã gửi bình luận!');
    }
    
    /**
     * Show create post form for student
     */
    public function createPost()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $clubs = Club::whereIn('id', $user->clubs->pluck('id'))->where('status', 'active')->get();
        if ($clubs->isEmpty()) {
            return redirect()->route('student.posts')->with('error', 'Bạn cần tham gia CLB trước khi tạo bài viết.');
        }
        return view('student.posts.create', compact('user', 'clubs'));
    }

    /**
     * Store student post
     */
    public function storePost(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:published,members_only,hidden',
            'type' => 'nullable|in:post,announcement,document',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096'
        ]);
        $data = $request->only(['title','content','club_id','status']);
        $data['type'] = $request->input('type', 'post');
        $data['user_id'] = $user->id;
        // Generate unique slug
        $baseSlug = Str::slug($data['title']);
        $slug = $baseSlug;
        $suffix = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
        $data['slug'] = $slug;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $destination = public_path('uploads/posts');
            if (!is_dir($destination)) {
                @mkdir($destination, 0755, true);
            }
            $image->move($destination, $filename);
            $data['image'] = 'uploads/posts/' . $filename;
        }
        // Remove featured image from content if present
        if (!empty($data['image']) && !empty($data['content'])) {
            $relative = ltrim($data['image'], '/');
            $assetUrl = asset($relative);
            $pattern = '#<img[^>]+src=["\\\'](?:' . preg_quote($assetUrl, '#') . '|' . preg_quote('/' . $relative, '#') . '|' . preg_quote($relative, '#') . ')[^"\\\']*["\\\'][^>]*>#i';
            $data['content'] = preg_replace($pattern, '', $data['content']);
        }
        $post = \App\Models\Post::create($data);
        return redirect()->route('student.posts.show', $post->id)->with('success', 'Tạo bài viết thành công!');
    }

    /**
     * Show edit post form
     */
    public function editPost($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $post = Post::findOrFail($id);
        if ($post->user_id !== $user->id) {
            return redirect()->route('student.posts.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa bài viết này.');
        }
        $clubs = Club::whereIn('id', $user->clubs->pluck('id'))->where('status', 'active')->get();
        return view('student.posts.edit', compact('user','post','clubs'));
    }

    /**
     * Show the form for creating a new post in a club's forum.
     */
    public function createClubPost(Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Check if user is a member of the club
        $isMember = $user->clubs()->where('club_id', $club->id)->exists();

        if (!$isMember) {
            return redirect()->route('student.clubs.show', $club->id)->with('error', 'Chỉ thành viên mới có thể đăng bài.');
        }

        return view('student.posts.create', compact('user', 'club'));
    }

    /**
     * Update student post
     */
    public function updatePost(Request $request, $id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $post = Post::findOrFail($id);
        if ($post->user_id !== $user->id) {
            return redirect()->route('student.posts.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa bài viết này.');
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:published,members_only,hidden',
            'type' => 'nullable|in:post,announcement,document',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_image' => 'nullable|in:0,1'
        ]);
        $data = $request->only(['title','content','club_id','status']);
        $data['type'] = $request->input('type', $post->type ?? 'post');
        // Regenerate slug if title changed or slug missing
        if ($post->title !== $data['title'] || empty($post->slug)) {
            $baseSlug = Str::slug($data['title']);
            $slug = $baseSlug;
            $suffix = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }
            $data['slug'] = $slug;
        }
        if ($request->input('remove_image') === '1' && !empty($post->image)) {
            if (\Illuminate\Support\Str::startsWith($post->image, ['uploads/','/uploads/'])) {
                @unlink(public_path(ltrim($post->image,'/')));
            }
            $post->image = null;
        }
        if ($request->hasFile('image')) {
            if (!empty($post->image) && \Illuminate\Support\Str::startsWith($post->image, ['uploads/','/uploads/'])) {
                @unlink(public_path(ltrim($post->image,'/')));
            }
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $destination = public_path('uploads/posts');
            if (!is_dir($destination)) {
                @mkdir($destination, 0755, true);
            }
            $image->move($destination, $filename);
            $data['image'] = 'uploads/posts/' . $filename;
        }
        // Remove featured image(s) from content if present (both old and new featured images)
        if (!empty($data['content'])) {
            $imagesToStrip = [];
            if (!empty($post->image)) {
                $imagesToStrip[] = $post->image;
            }
            if (!empty($data['image'])) {
                $imagesToStrip[] = $data['image'];
            }
            foreach ($imagesToStrip as $imgPath) {
                $relative = ltrim($imgPath, '/');
                $assetUrl = asset($relative);
                $pattern = '#<img[^>]+src=["\\\'](?:' . preg_quote($assetUrl, '#') . '|' . preg_quote('/' . $relative, '#') . '|' . preg_quote($relative, '#') . ')[^"\\\']*["\\\'][^>]*>#i';
                $data['content'] = preg_replace($pattern, '', $data['content']);
            }
        }
        $post->update($data);
        return redirect()->route('student.posts.show', $post->id)->with('success', 'Cập nhật bài viết thành công!');
    }

    /**
     * Upload image from editor and return public URL (AJAX)
     */
    public function uploadEditorImage(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['error' => ['message' => 'Vui lòng đăng nhập.']], 401);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            // Lưu vào thư mục public/uploads/posts/content
            $destination = public_path('uploads/posts/content');
            if (!is_dir($destination)) {
                @mkdir($destination, 0755, true);
            }

            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
            $image->move($destination, $filename);
            
            $url = 'uploads/posts/content/' . $filename;
            $fullUrl = asset($url);

            // CKEditor 5 sử dụng JSON response
            return response()->json([
                'url' => $fullUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'Không thể upload ảnh: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Show form to create announcement
     */
    public function createAnnouncement(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        
        $clubs = Club::whereIn('id', $user->clubs->pluck('id'))->where('status', 'active')->get();
        
        if ($clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')->with('error', 'Bạn cần tham gia CLB trước khi tạo thông báo.');
        }
        
        return view('student.announcements.create', compact('user', 'clubs'));
    }

    /**
     * Store announcement
     */
    public function storeAnnouncement(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:published,members_only,hidden',
            'type' => 'nullable|in:post,announcement,document',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096'
        ]);
        
        $data = $request->only(['title', 'content', 'club_id', 'status']);
        $data['type'] = 'announcement';
        $data['user_id'] = $user->id;
        
        // Generate unique slug
        $baseSlug = Str::slug($data['title']);
        $slug = $baseSlug;
        $suffix = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
        $data['slug'] = $slug;
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $destination = public_path('uploads/posts');
            if (!is_dir($destination)) {
                @mkdir($destination, 0755, true);
            }
            $image->move($destination, $filename);
            $data['image'] = 'uploads/posts/' . $filename;
        }
        
        // Remove featured image from content if present
        if (!empty($data['image']) && !empty($data['content'])) {
            $relative = ltrim($data['image'], '/');
            $assetUrl = asset($relative);
            $pattern = '#<img[^>]+src=["\\\'](?:' . preg_quote($assetUrl, '#') . '|' . preg_quote('/' . $relative, '#') . '|' . preg_quote($relative, '#') . ')[^"\\\']*["\\\'][^>]*>#i';
            $data['content'] = preg_replace($pattern, '', $data['content']);
        }
        
        $post = Post::create($data);
        
        return redirect()->route('student.posts.show', $post->id)->with('success', 'Tạo thông báo thành công!');
    }

    /**
     * List posts created by current student
     */
    public function myPosts(\Illuminate\Http\Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        // Query tương tự admin nhưng filter theo user_id
        // Loại bỏ các bài viết đã bị soft delete và có status = 'deleted'
        $query = Post::with(['club'])
            ->where('user_id', $user->id)
            ->whereIn('type', ['post', 'announcement'])
            ->where('status', '!=', 'deleted')
            ->orderBy('created_at', 'desc');
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        $posts = $query->paginate(12);
        return view('student.posts.manage', compact('user','posts'));
    }

    /**
     * Delete a post created by current student
     */
    public function deletePost($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $post = Post::findOrFail($id);
        if ($post->user_id !== $user->id) {
            return redirect()->route('student.posts.manage')->with('error', 'Bạn không có quyền xóa bài viết này.');
        }
        // remove featured image file if under uploads
        if (!empty($post->image) && \Illuminate\Support\Str::startsWith($post->image, ['uploads/','/uploads/'])) {
            @unlink(public_path(ltrim($post->image,'/')));
        }
        $post->delete();
        return redirect()->route('student.posts.manage')->with('success', 'Đã xóa bài viết.');
    }

    /**
     * Display a single club's details page.
     */
    public function showClub(Club $club)
    {
         $user = User::find(session('user_id'));
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }

        // Tải trước các mối quan hệ cần thiết để tối ưu hóa truy vấn
        $club->load(['leader', 'field', 'members' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->loadCount('members');

        $isMember = $user->clubs()->where('club_id', $club->id)->exists();

        $data = [
            'club' => $club,
            'user' => $user,
            'isMember' => $isMember,
            'joinRequest' => null,
            'clubMember' => null,
            'events' => collect(),
            'announcements' => collect(),
            'posts' => collect(),
            'galleryImages' => collect(),
        ];

        if ($isMember) {
            // Lấy thông tin thành viên từ collection đã được tải trước
            $data['clubMember'] = $club->members->first();
            
            // Tải các dữ liệu khác chỉ khi người dùng là thành viên
            $data['events'] = $club->events()->where('status', 'approved')->where('start_time', '>=', now())->orderBy('start_time', 'asc')->get();
            $data['announcements'] = $club->posts()->where('type', 'announcement')->where('status', 'published')->orderBy('created_at', 'desc')->limit(5)->get();
            
            // Tải ảnh cho thư viện
            $eventImages = \App\Models\EventImage::whereIn('event_id', $club->events()->where('status', 'completed')->pluck('id'))->get();
            $postImages = \App\Models\PostAttachment::whereIn('post_id', $club->posts()->whereNotNull('image')->pluck('id'))->get();
            $data['galleryImages'] = $eventImages->concat($postImages);
        } else {
            $data['joinRequest'] = $club->joinRequests()->where('user_id', $user->id)->where('status', 'pending')->first();
        }
        
        // Load posts cho người xem
        // Nếu là thành viên: hiển thị cả published và members_only
        // Nếu không phải thành viên: chỉ hiển thị published
        $postsQuery = $club->posts()
            ->with(['user', 'comments'])
            ->where('type', 'post');
            
        if ($isMember) {
            // Thành viên: xem cả published và members_only
            $postsQuery->whereIn('status', ['published', 'members_only']);
        } else {
            // Không phải thành viên: chỉ xem published
            $postsQuery->where('status', 'published');
        }
        
        $data['posts'] = $postsQuery
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('student.clubs.show', $data);
    }

    /**
     * Show the form for creating a new club.
     */
    public function createClub()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Business logic: A user can only be a leader/treasurer in one club.
        // Let's check if the user is already a leader or treasurer in any club.
        $isLeaderOrOfficer = ClubMember::where('user_id', $user->id) // Tìm vai trò của user
            ->whereIn('position', ['leader', 'vice_president', 'treasurer'])
            ->whereIn('status', ['active', 'approved']) // Chỉ kiểm tra các vai trò đang hoạt động
            ->whereHas('club', function ($query) { // Chỉ tính các CLB chưa bị xóa
                $query->whereNull('deleted_at');
            })
            ->exists();

        if ($isLeaderOrOfficer) {
            return redirect()->route('student.clubs.index')->with('error', 'Bạn đã là thủ quỹ/phó CLB hoặc trưởng của một CLB khác và không thể tạo thêm CLB mới.');
        }

        $fields = Field::orderBy('name')->get();

        return view('student.clubs.create', compact('user', 'fields'));
    }

    /**
     * Store a newly created club request.
     */
    public function storeClub(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Re-check eligibility
        $isLeaderOrOfficer = ClubMember::where('user_id', $user->id) // Tìm vai trò của user
            ->whereIn('position', ['leader', 'vice_president', 'treasurer'])
            ->whereIn('status', ['active', 'approved']) // Thêm dòng này để sửa lỗi
            ->whereHas('club', function ($query) { // Chỉ tính các CLB chưa bị xóa
                $query->whereNull('deleted_at');
            })
            ->exists();

        if ($isLeaderOrOfficer) {
            return redirect()->route('student.clubs.index')->with('error', 'Bạn đã là cán sự hoặc trưởng của một CLB khác và không thể tạo thêm CLB mới.');
        }

        // Custom validation: nếu có new_field_name thì không cần field_id, và ngược lại
        $hasNewField = $request->filled('new_field_name');
        $fieldIdValue = $request->input('field_id', '');
        $hasFieldId = !empty($fieldIdValue) && !str_starts_with((string)$fieldIdValue, 'new_');
        
        if (!$hasNewField && !$hasFieldId) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['field_id' => 'Vui lòng chọn lĩnh vực hoặc tạo lĩnh vực mới.']);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:clubs,name',
            'description' => 'required|string', // Cho phép HTML từ CKEditor, kiểm tra độ dài text thuần ở dưới
            'introduction' => 'nullable|string|max:20000',
            'field_id' => $hasNewField ? 'nullable' : 'required|exists:fields,id',
            'new_field_name' => $hasFieldId ? 'nullable' : 'required|string|max:100|unique:fields,name',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Max 2MB
        ], [
            'name.unique' => 'Tên câu lạc bộ này đã tồn tại.',
            'logo.max' => 'Kích thước logo không được vượt quá 2MB.',
            'field_id.required' => 'Vui lòng chọn lĩnh vực.',
            'field_id.exists' => 'Lĩnh vực không hợp lệ.',
            'new_field_name.required' => 'Vui lòng nhập tên lĩnh vực mới.',
            'new_field_name.unique' => 'Lĩnh vực này đã tồn tại.',
        ]);

        // Kiểm tra độ dài mô tả (text thuần, không tính HTML tags)
        $descriptionText = strip_tags($request->description);
        if (mb_strlen($descriptionText) > 255) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['description' => 'Mô tả ngắn không được vượt quá 255 ký tự (không tính HTML).']);
        }

        // Xử lý field_id: tạo field mới nếu có new_field_name
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

        // Create slug for the club
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        while (Club::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . $slug . '.' . $logo->getClientOriginalExtension();
            $logoDir = public_path('uploads/clubs/logos');
            if (!is_dir($logoDir)) {
                @mkdir($logoDir, 0755, true);
            }
            $logo->move($logoDir, $logoName);
            $logoPath = 'uploads/clubs/logos/' . $logoName;
        }

        // Create the club with 'pending' status
        $club = Club::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'introduction' => $request->introduction,
            'logo' => $logoPath,
            'field_id' => $fieldId,
            'owner_id' => $user->id, // The creator is the owner/proposer
            'leader_id' => $user->id, // Tentatively set the creator as the leader
            'status' => 'pending', // IMPORTANT: Set status to pending for admin approval
            'established_at' => now(),
        ]);

        // Automatically add the creator as the leader in the club_members table
        if ($club) {
            $clubMember = ClubMember::create([
                'club_id' => $club->id,
                'user_id' => $user->id,
                'position' => 'leader', // Set the creator as the leader
                'status' => 'approved', // The leader is automatically approved
                'joined_at' => now(),
            ]);

            // Grant all permissions to the leader by default
            if ($clubMember) {
                $allPermissionIds = Permission::pluck('id');
                $permissionsToInsert = $allPermissionIds->map(function ($permissionId) use ($user, $club) {
                    return [
                        'user_id' => $user->id,
                        'club_id' => $club->id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                DB::table('user_permissions_club')->insert($permissionsToInsert);
            }

            // Tự động tạo quỹ cho CLB mới
            try {
                Fund::create([
                    'club_id' => $club->id,
                    'name' => 'Quỹ ' . $club->name,
                    'description' => 'Quỹ tự động được tạo khi thành lập CLB',
                    'initial_amount' => 0,
                    'current_amount' => 0,
                    'status' => 'active',
                    'source' => 'Hệ thống',
                    'created_by' => $user->id,
                ]);
            } catch (\Exception $e) {
                // Log lỗi nhưng không chặn việc tạo CLB
                \Log::error('Lỗi khi tạo quỹ tự động cho CLB: ' . $e->getMessage());
            }
        }

        return redirect()->route('student.clubs.index')->with('success', 'Yêu cầu tạo CLB của bạn đã được gửi thành công và đang chờ xét duyệt!');
    }

    /**
     * Allow a student to leave a club.
     */
    public function leaveClub(Request $request, Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $membership = ClubMember::where('user_id', $user->id)
            ->where('club_id', $club->id)
            ->first();

        if (!$membership) {
            return redirect()->back()->with('error', 'Bạn không phải là thành viên của câu lạc bộ này.');
        }

        // Prevent leaders from leaving directly
        if (in_array($membership->position, ['leader', 'owner'])) {
            return redirect()->back()->with('error', 'Trưởng/Chủ nhiệm CLB không thể rời đi. Vui lòng chuyển giao vai trò trước.');
        }

        $membership->delete();

        // Update session
        $clubRoles = session('club_roles', []);
        unset($clubRoles[$club->id]);
        session(['club_roles' => $clubRoles]);

        return redirect()->route('student.clubs.index')->with('success', 'Bạn đã rời khỏi câu lạc bộ ' . $club->name . ' thành công.');
    }

    /**
     * Allow a student to send a join request to a club.
     */
    public function joinClub(Request $request, Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // 1. Kiểm tra xem user đã là thành viên chưa
        $isMember = $club->members()->where('user_id', $user->id)->exists();
        if ($isMember) {
            return redirect()->back()->with('error', 'Bạn đã là thành viên của câu lạc bộ này.');
        }

        // 2. Kiểm tra xem user đã có yêu cầu đang chờ xử lý chưa
        $hasPendingRequest = $club->joinRequests()->where('user_id', $user->id)->where('status', 'pending')->exists();
        if ($hasPendingRequest) {
            return redirect()->back()->with('info', 'Bạn đã gửi yêu cầu tham gia câu lạc bộ này rồi. Vui lòng chờ duyệt.');
        }

        // 3. Tạo yêu cầu tham gia mới
        ClubJoinRequest::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'message' => $request->input('message'), // Tùy chọn: có thể thêm ô lời nhắn
        ]);

        return redirect()->back()->with('success', 'Đã gửi yêu cầu tham gia thành công! Vui lòng chờ ban quản trị CLB duyệt.');
    }

    /**
     * Allow a student to cancel their join request.
     */
    public function cancelJoinRequest(Request $request, Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $joinRequest = ClubJoinRequest::where('user_id', $user->id)
            ->where('club_id', $club->id)
            ->where('status', 'pending')
            ->first();

        if (!$joinRequest) {
            return redirect()->back()->with('error', 'Không tìm thấy yêu cầu tham gia để hủy.');
        }

        $joinRequest->delete();

        return redirect()->back()->with('success', 'Đã hủy yêu cầu tham gia câu lạc bộ ' . $club->name . '.');
    }

    /**
     * Mark announcement as viewed
     */
    public function markAnnouncementViewed(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['success' => false], 401);
        }

        $announcementId = $request->input('announcement_id');
        if ($announcementId) {
            // Chỉ cập nhật ID thông báo đã xem nếu thông báo này mới hơn
            // Điều này cho phép modal hiển thị lại mỗi lần vào trang cho đến khi có thông báo mới
            $lastViewedId = session('last_viewed_announcement_id', 0);
            if ($announcementId > $lastViewedId) {
                session(['last_viewed_announcement_id' => $announcementId]);
            }
            // Nếu đóng modal của thông báo cũ, không cập nhật - để modal tiếp tục hiển thị
        }

        return response()->json(['success' => true]);
    }

}
