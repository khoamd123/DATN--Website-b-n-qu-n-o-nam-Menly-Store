<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubJoinRequest;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\Field;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\ClubJoinRequest as JoinReq;
use App\Models\Fund;
use App\Models\FundTransaction;
use Illuminate\Support\Facades\Schema;
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

    public function clubs()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.clubs.index', compact('user'));
    }

    public function events()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Lấy tất cả events sắp tới (chưa kết thúc) - chỉ lấy events đã được duyệt
        $upcomingEvents = Event::with(['club', 'creator', 'images'])
            ->where('status', 'approved')
            ->where('end_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->get();

        // Lấy events đã đăng ký bởi user
        $registeredEventIds = \App\Models\EventRegistration::where('user_id', $user->id)
            ->whereIn('status', ['registered', 'pending', 'approved'])
            ->pluck('event_id')
            ->toArray();

        $registeredEvents = Event::with(['club', 'creator', 'images'])
            ->whereIn('id', $registeredEventIds)
            ->where('status', 'approved')
            ->orderBy('start_time', 'asc')
            ->get();

        // Đếm số lượng đăng ký cho mỗi event
        $eventRegistrations = \App\Models\EventRegistration::whereIn('event_id', $upcomingEvents->pluck('id'))
            ->whereIn('status', ['registered', 'pending', 'approved'])
            ->selectRaw('event_id, COUNT(*) as count')
            ->groupBy('event_id')
            ->pluck('count', 'event_id')
            ->toArray();

        // Thống kê sidebar - chỉ đếm events đã được duyệt
        $todayEvents = Event::where('status', 'approved')
            ->whereDate('start_time', now()->toDateString())
            ->count();

        $thisWeekEvents = Event::where('status', 'approved')
            ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $thisMonthEvents = Event::where('status', 'approved')
            ->whereMonth('start_time', now()->month)
            ->whereYear('start_time', now()->year)
            ->count();

        // Sự kiện hot (có nhiều đăng ký nhất) - chỉ lấy events đã được duyệt
        $hotEvents = Event::with(['club'])
            ->where('status', 'approved')
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
            'registeredEvents',
            'eventRegistrations',
            'todayEvents',
            'thisWeekEvents',
            'thisMonthEvents',
            'hotEvents'
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

            // Kiểm tra sự kiện đã được duyệt chưa
            if ($event->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này chưa được duyệt hoặc không khả dụng.'
                ], 400);
            }

            // Kiểm tra sự kiện có bị hủy không
            if ($event->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này đã bị hủy.'
                ], 400);
            }

            // Kiểm tra sự kiện đã kết thúc chưa
            if ($event->end_time < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này đã kết thúc.'
                ], 400);
            }

            // Kiểm tra hạn đăng ký
            if ($event->registration_deadline && $event->registration_deadline < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã hết hạn đăng ký cho sự kiện này.'
                ], 400);
            }

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
            \App\Models\EventRegistration::create([
                'user_id' => $user->id,
                'event_id' => $eventId,
                'status' => 'registered',
                'joined_at' => now(),
            ]);

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

            // Kiểm tra sự kiện đã được duyệt chưa
            if ($event->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này chưa được duyệt hoặc không khả dụng.'
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

            // Kiểm tra sự kiện đã bắt đầu chưa
            if ($event->start_time <= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đăng ký vì sự kiện đã bắt đầu.'
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

            // Kiểm tra sự kiện đã được duyệt chưa
            if ($event->status !== 'approved') {
                return redirect()->route('student.events.index')
                    ->with('error', 'Sự kiện này chưa được duyệt hoặc không khả dụng.');
            }

            // Kiểm tra đăng ký của user
            $isRegistered = \App\Models\EventRegistration::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->whereIn('status', ['registered', 'pending', 'approved'])
                ->exists();

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
                'registrationCount',
                'availableSlots',
                'isFull',
                'isDeadlinePassed'
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
                'start_time' => 'required|date',
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
                    EventImage::create([
                        'event_id' => $event->id,
                        'image_path' => $imagePath,
                        'alt_text' => $request->title . ' - Ảnh ' . ($index + 1),
                        'sort_order' => $index,
                    ]);
                }
            }

            return redirect()->route('student.club-management.index')
                ->with('success', 'Tạo sự kiện thành công! Sự kiện đang chờ được duyệt bởi quản trị viên.');

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

        return view('student.notifications.index', compact('user'));
    }

    public function contact()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return view('student.contact', compact('user'));
    }

    public function clubManagement()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Luôn hiển thị trang, nhưng kiểm tra quyền để hiển thị nội dung phù hợp
        $hasManagementRole = false;
        $clubId = null;
        $userPosition = null;
        $userClub = null;
        
        if ($user->clubs->count() > 0) {
            $userClub = $user->clubs->first();
            $clubId = $userClub->id;
            $userPosition = $user->getPositionInClub($clubId);
            $hasManagementRole = in_array($userPosition, ['leader', 'vice_president', 'officer']);
        }

        return view('student.club-management.index', compact('user', 'hasManagementRole', 'userPosition', 'userClub'));
    }
}
