<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
use Illuminate\Http\Request;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\Field;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationTarget;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClubController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index(Request $request)
    {
        return $this->oldController->clubs($request);
    }

    public function ajaxSearch(Request $request)
    {
        return $this->oldController->ajaxSearchClubs($request);
    }

    public function create()
    {
        // Check authentication
        if (!session('user_id') || session('is_admin')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập với tài khoản sinh viên.');
        }

        $user = User::find(session('user_id'));
        if (!$user) {
            return redirect()->route('login')->with('error', 'Không tìm thấy thông tin người dùng.');
        }

        // Kiểm tra xem user có đang là leader của CLB nào không
        $isLeader = ClubMember::where('user_id', $user->id)
            ->where('position', 'leader')
            ->whereIn('status', ['active', 'approved'])
            ->exists();

        if ($isLeader) {
            return redirect()->route('student.clubs.index')
                ->with('error', 'Bạn đã là trưởng CLB, không thể tạo thêm CLB mới.');
        }
        
        // Load fields for the form
        $fields = Field::orderBy('name')->get();
        return view('student.clubs.create', compact('fields'));
    }

    public function store(Request $request)
    {
        // Check authentication
        if (!session('user_id') || session('is_admin')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập với tài khoản sinh viên.');
        }

        $user = User::find(session('user_id'));
        if (!$user) {
            return redirect()->route('login')->with('error', 'Không tìm thấy thông tin người dùng.');
        }

        // Kiểm tra xem user có đang là leader của CLB nào không
        $isLeader = ClubMember::where('user_id', $user->id)
            ->where('position', 'leader')
            ->whereIn('status', ['active', 'approved'])
            ->exists();

        if ($isLeader) {
            return redirect()->route('student.clubs.index')
                ->with('error', 'Bạn đã là trưởng CLB, không thể tạo thêm CLB mới.');
        }

        // Validation
        $rules = [
            'name' => 'required|string|max:255|unique:clubs,name',
            'description' => 'required|string',
            'introduction' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        // Validation cho field - kiểm tra xem có new_field_name không
        if ($request->has('new_field_name') && !empty($request->new_field_name)) {
            $rules['new_field_name'] = 'required|string|max:100|unique:fields,name';
        } else {
            $rules['field_id'] = 'required|exists:fields,id';
        }

        try {
            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in store club: ' . json_encode($e->errors()));
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Xử lý field
            $fieldId = $request->field_id;
            if (!empty($request->new_field_name)) {
                // Tạo field mới với slug và description
                $field = Field::create([
                    'name' => $request->new_field_name,
                    'slug' => Str::slug($request->new_field_name),
                    'description' => '', // Description bắt buộc trong migration
                ]);
                $fieldId = $field->id;
            }

            // Xử lý upload logo
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = time() . '_' . Str::slug($request->name) . '.' . $logo->getClientOriginalExtension();
                $logoPath = 'clubs/logos/' . $logoName;
                $logo->storeAs('public/uploads/clubs/logos', $logoName);
                $logoPath = 'uploads/clubs/logos/' . $logoName;
            }

            // Xử lý description từ CKEditor (có thể chứa HTML)
            $description = strip_tags($request->description);
            // Giới hạn description để lưu vào database (255 ký tự)
            if (strlen($description) > 255) {
                $description = mb_substr($description, 0, 252) . '...';
            }

            // Tạo CLB với status pending (chờ admin duyệt)
            $club = Club::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $description,
                'field_id' => $fieldId,
                'owner_id' => $user->id,
                'leader_id' => $user->id,
                'logo' => $logoPath ?? '', // Nếu không có logo, dùng chuỗi rỗng
                'status' => 'pending', // Chờ admin duyệt
                'max_members' => 100,
            ]);

            // Thêm user làm leader của CLB (position = leader, status = approved)
            ClubMember::create([
                'user_id'   => $user->id,
                'club_id'   => $club->id,
                'position'  => 'leader',
                'status'    => 'approved',
                'joined_at' => now(),
            ]);

            // Gửi thông báo cho admin
            $admins = User::where(function($query) {
                $query->where('is_admin', true)
                      ->orWhere('role', 'admin');
            })->get();

            foreach ($admins as $admin) {
                $notification = Notification::create([
                    'sender_id' => $user->id,
                    'type' => 'club_request',
                    'title' => 'Yêu cầu tạo CLB mới',
                    'message' => "Người dùng {$user->name} đã gửi yêu cầu tạo CLB mới: \"{$club->name}\". Vui lòng xem xét và duyệt.",
                    'related_id' => $club->id,
                    'related_type' => 'Club',
                ]);

                NotificationTarget::create([
                    'notification_id' => $notification->id,
                    'target_type' => 'user',
                    'target_id' => $admin->id,
                ]);
            }

            DB::commit();

            return redirect()->route('student.clubs.index')
                ->with('success', 'Yêu cầu tạo CLB đã được gửi thành công! Vui lòng chờ quản trị viên duyệt.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in store club: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($club)
    {
        return $this->oldController->showClub($club);
    }

    public function join(Request $request, $club)
    {
        return $this->oldController->joinClub($request, $club);
    }

    public function leave($club)
    {
        return $this->oldController->leaveClub($club);
    }

    public function cancelJoinRequest($club)
    {
        return $this->oldController->cancelJoinRequest($club);
    }
}



