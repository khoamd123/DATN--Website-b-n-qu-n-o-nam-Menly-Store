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
use App\Models\Notification;
use App\Models\NotificationTarget;
use App\Models\NotificationRead;
use App\Models\ClubPaymentQr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Check if user is logged in as student
     * Made public so nested controllers (NotificationController, etc.) can reuse.
     */
    public function checkStudentAuth()
    {
        if (!session('user_id') || session('is_admin')) {
            if (session('is_admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập với tài khoản sinh viên.');
        }

        try {
        $user = User::with('clubs')->find(session('user_id'));
        
        if (!$user) {
            session()->forget(['user_id', 'user_name', 'user_email', 'is_admin']);
            return redirect()->route('login')->with('error', 'Phiên đăng nhập đã hết hạn.');
        }

        return $user;
        } catch (\Illuminate\Database\QueryException $e) {
            // Xử lý lỗi kết nối database
            \Log::error('Database connection error in checkStudentAuth: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.');
        } catch (\Exception $e) {
            // Xử lý các lỗi khác
            \Log::error('Error in checkStudentAuth: ' . $e->getMessage());
            session()->forget(['user_id', 'user_name', 'user_email', 'is_admin']);
            return redirect()->route('login')->with('error', 'Đã xảy ra lỗi. Vui lòng đăng nhập lại.');
        }
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

        // Lấy thông báo mới nhất để hiển thị modal
        $announcementData = $this->getLatestAnnouncementForModal($user);
        
        return view('student.dashboard', array_merge(compact('user'), $announcementData));
    }

    /**
     * Get latest announcement for modal display
     */
    private function getLatestAnnouncementForModal($user)
    {
        // Lấy danh sách CLB mà user là thành viên
        $userClubIds = $user->clubs->pluck('id')->toArray();
        
        // Query thông báo có quyền xem
        $announcementsQuery = Post::with(['club', 'user'])
            ->where('type', 'announcement')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'deleted')
            ->where(function($q) use ($userClubIds) {
                // Bài viết công khai - ai cũng xem được
                $q->where('status', 'published');
                
                // Hoặc bài viết chỉ thành viên CLB mà user là thành viên
                if (!empty($userClubIds)) {
                    $q->orWhere(function($subQ) use ($userClubIds) {
                        $subQ->where('status', 'members_only')
                             ->whereIn('club_id', $userClubIds);
                    });
                }
            })
            ->orderBy('created_at', 'desc');
        
        // Lấy thông báo mới nhất
        $latestAnnouncement = $announcementsQuery->first();
        
        // Kiểm tra xem có thông báo mới hơn thông báo đã xem gần nhất không
        $lastViewedAnnouncementId = session('last_viewed_announcement_id', 0);
        $shouldShowModal = false;
        if ($latestAnnouncement) {
            // Hiển thị modal mỗi lần vào trang cho đến khi có thông báo mới hơn
            // Modal sẽ tiếp tục hiển thị cho đến khi có thông báo mới (ID lớn hơn)
            if ($latestAnnouncement->id >= $lastViewedAnnouncementId) {
                $shouldShowModal = true;
            }
        }
        
        return [
            'latestAnnouncement' => $latestAnnouncement,
            'shouldShowModal' => $shouldShowModal
        ];
    }

    public function clubs(Request $request) 
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $search = $request->input('search');

        // Lấy ID các CLB mà người dùng đang là thành viên (chưa rời)
        // Chỉ lấy các CLB có status active/approved và chưa bị soft delete
        $allMemberClubIds = ClubMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'approved'])
            ->whereNull('deleted_at')
            ->pluck('club_id')
            ->toArray();

        // 1. Câu lạc bộ của tôi: Các CLB mà user là owner HOẶC leader
        $myClubIds = Club::where('status', 'active')
            ->where(function($query) use ($user) {
                $query->where('owner_id', $user->id)
                      ->orWhere('leader_id', $user->id);
            })
            ->pluck('id')
            ->toArray();
        
        // Thêm các CLB mà user là leader (từ ClubMember)
        $leaderClubIds = ClubMember::where('user_id', $user->id)
            ->where('position', 'leader')
            ->whereIn('status', ['active', 'approved'])
            ->whereNull('deleted_at')
            ->pluck('club_id')
            ->toArray();
        
        $myClubIds = array_unique(array_merge($myClubIds, $leaderClubIds));
        
        $myClubs = Club::whereIn('id', $myClubIds)
            ->where('status', 'active')
            ->withCount([
                'clubMembers as active_members_count' => function ($query) {
                    $query->whereIn('status', ['approved', 'active']);
                }
            ])
            ->orderBy('name')
            ->get();

        // 2. Câu lạc bộ đã tham gia: Các CLB mà user là member (nhưng không phải owner/leader)
        $joinedClubIds = array_diff($allMemberClubIds, $myClubIds);
        
        $joinedClubs = Club::whereIn('id', $joinedClubIds)
            ->where('status', 'active')
            ->withCount([
                'clubMembers as active_members_count' => function ($query) {
                    $query->whereIn('status', ['approved', 'active']);
                }
            ])
            ->orderBy('name')
            ->get();

        // 3. Câu lạc bộ khác: Các CLB khác mà user chưa tham gia
        $otherClubIds = array_merge($myClubIds, $joinedClubIds);
        $otherClubsQuery = Club::where('status', 'active')
            ->whereNotIn('id', $otherClubIds)
            ->withCount([
                'clubMembers as active_members_count' => function ($query) {
                    $query->whereIn('status', ['approved', 'active']);
                }
            ]);

        // Áp dụng bộ lọc tìm kiếm cho các CLB khác
        if ($search) {
            $otherClubsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        // Lấy kết quả với phân trang
        $otherClubs = $otherClubsQuery->orderBy('name')->paginate(8);

        // Kiểm tra xem user có đang là leader của CLB nào không
        $isLeader = \App\Models\ClubMember::where('user_id', $user->id)
            ->where('position', 'leader')
            ->whereIn('status', ['active', 'approved'])
            ->exists();

        return view('student.clubs.index', compact('user', 'myClubs', 'joinedClubs', 'otherClubs', 'search', 'isLeader'));
    }

    public function ajaxSearchClubs(Request $request)
    {
        $user = $this->checkStudentAuth();
        // Nếu checkStudentAuth trả về một redirect, nghĩa là chưa đăng nhập, trả về lỗi
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response('Unauthorized.', 401);
        }

        $search = $request->input('search', '');
        // Lấy ID các CLB mà người dùng đang là thành viên (chưa rời)
        $allMemberClubIds = ClubMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'approved'])
            ->whereNull('deleted_at')
            ->pluck('club_id')
            ->toArray();

        // Lấy ID các CLB mà user là owner hoặc leader
        $myClubIds = Club::where('status', 'active')
            ->where(function($query) use ($user) {
                $query->where('owner_id', $user->id)
                      ->orWhere('leader_id', $user->id);
            })
            ->pluck('id')
            ->toArray();
        
        // Thêm các CLB mà user là leader (từ ClubMember)
        $leaderClubIds = ClubMember::where('user_id', $user->id)
            ->where('position', 'leader')
            ->whereIn('status', ['active', 'approved'])
            ->whereNull('deleted_at')
            ->pluck('club_id')
            ->toArray();
        
        $myClubIds = array_unique(array_merge($myClubIds, $leaderClubIds));
        
        // Lấy ID các CLB mà user đã tham gia (nhưng không phải owner/leader)
        $joinedClubIds = array_diff($allMemberClubIds, $myClubIds);
        
        // Lấy ID tất cả các CLB mà user đã tham gia (bao gồm cả owner/leader)
        $allJoinedClubIds = array_merge($myClubIds, $joinedClubIds);

        $otherClubs = Club::where('status', 'active')
            ->whereNotIn('id', $allJoinedClubIds)
            ->where('name', 'like', '%' . $search . '%')
            ->withCount([
                'clubMembers as active_members_count' => function ($query) {
                    $query->whereIn('status', ['approved', 'active']);
                }
            ])
            ->orderBy('name')
            ->paginate(8);

        return view('student.clubs._other_clubs_list', compact('otherClubs', 'search'));
    }

    /**
     * Show form for creating a new club.
     */
    public function createClub()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Kiểm tra xem user có đang là leader của CLB nào không
        $isLeader = \App\Models\ClubMember::where('user_id', $user->id)
            ->where('position', 'leader')
            ->whereIn('status', ['active', 'approved'])
            ->exists();

        if ($isLeader) {
            return redirect()->route('student.clubs.index')
                ->with('error', 'Bạn đã là trưởng CLB, không thể tạo thêm CLB mới.');
        }

        $fields = Field::orderBy('name')->get();

        return view('student.clubs.create', compact('user', 'fields'));
    }

    /**
     * Store a new club request.
     */
    public function storeClub(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Kiểm tra xem user có đang là leader của CLB nào không
        $isLeader = \App\Models\ClubMember::where('user_id', $user->id)
            ->where('position', 'leader')
            ->whereIn('status', ['active', 'approved'])
            ->exists();

        if ($isLeader) {
            return redirect()->route('student.clubs.index')
                ->with('error', 'Bạn đã là trưởng CLB, không thể tạo thêm CLB mới.');
        }

        // Validate description riêng để xử lý HTML từ CKEditor
        $description = $request->input('description', '');
        $descriptionText = strip_tags($description);
        $descriptionText = trim($descriptionText);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required',
            'introduction' => 'nullable|string',
            'field_id' => 'nullable|exists:fields,id',
            'new_field_name' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'name.required' => 'Vui lòng nhập tên câu lạc bộ.',
            'name.max' => 'Tên câu lạc bộ không được vượt quá 255 ký tự.',
            'description.required' => 'Vui lòng nhập mô tả ngắn về câu lạc bộ.',
            'logo.image' => 'Logo phải là file hình ảnh.',
            'logo.mimes' => 'Logo phải có định dạng: jpeg, png, jpg, gif, webp.',
            'logo.max' => 'Logo không được vượt quá 2MB.',
        ]);

        // Kiểm tra độ dài description sau khi strip HTML
        if (mb_strlen($descriptionText) > 255) {
            return back()->withInput()->with('error', 'Mô tả ngắn không được vượt quá 255 ký tự. Hiện tại: ' . mb_strlen($descriptionText) . ' ký tự.');
        }
        
        if (empty($descriptionText)) {
            return back()->withInput()->with('error', 'Vui lòng nhập mô tả ngắn về câu lạc bộ.');
        }

        // Validate field_id hoặc new_field_name
        $fieldId = $request->field_id;
        if (empty($fieldId) && empty($request->new_field_name)) {
            return back()->withInput()->with('error', 'Vui lòng chọn một lĩnh vực có sẵn hoặc tạo lĩnh vực mới.');
        }

        // Xử lý field_id: nếu có new_field_name thì tạo field mới, nếu không thì dùng field_id
        $fieldId = null;
        if ($request->filled('new_field_name')) {
            $newFieldName = trim($request->new_field_name);
            if (empty($newFieldName)) {
                return back()->withInput()->with('error', 'Tên lĩnh vực mới không được để trống.');
            }
            $slug = Str::slug($newFieldName);
            if (empty($slug)) {
                $slug = 'field-' . time();
            }
            $originalSlug = $slug;
            $suffix = 1;
            while (Field::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $suffix;
                $suffix++;
            }
            try {
                $field = Field::create([
                    'name' => $newFieldName,
                    'slug' => $slug,
                    'description' => 'Lĩnh vực mới được tạo bởi ' . $user->name,
                ]);
                $fieldId = $field->id;
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Không thể tạo lĩnh vực mới: ' . $e->getMessage());
            }
        } elseif (!empty($request->field_id)) {
            $fieldId = $request->field_id;
        } else {
            return back()->withInput()->with('error', 'Vui lòng chọn một lĩnh vực có sẵn hoặc tạo lĩnh vực mới.');
        }

        $clubSlug = Str::slug($request->name);
        if (empty($clubSlug)) {
            $clubSlug = 'club-' . time();
        }
        $originalClubSlug = $clubSlug;
        $clubSuffix = 1;
        while (Club::withTrashed()->where('slug', $clubSlug)->exists()) {
            $clubSlug = $originalClubSlug . '-' . $clubSuffix;
            $clubSuffix++;
        }

        $logoPath = '';
        if ($request->hasFile('logo')) {
            try {
                $logo = $request->file('logo');
                $filename = time() . '_' . $originalClubSlug . '.' . $logo->getClientOriginalExtension();
                $logoDir = public_path('uploads/clubs/logos');
                if (!is_dir($logoDir)) {
                    @mkdir($logoDir, 0755, true);
                }
                $logo->move($logoDir, $filename);
                $logoPath = 'uploads/clubs/logos/' . $filename;
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Không thể tải logo lên: ' . $e->getMessage());
            }
        }

        try {
            $club = Club::create([
                'name' => trim($request->name),
                'slug' => $clubSlug,
                'description' => $request->description, // HTML từ CKEditor
                'logo' => $logoPath ?: null,
                'field_id' => $fieldId,
                'owner_id' => $user->id,
                'leader_id' => $user->id,
                'status' => 'pending',
            ]);

            // Tạo thành viên với vai trò leader cho người tạo CLB
            ClubMember::create([
                'club_id' => $club->id,
                'user_id' => $user->id,
                'position' => 'leader',
                'status' => 'approved',
                'joined_at' => now(),
            ]);

            // Cấp tất cả quyền cho leader
            $allPermissions = Permission::all();
            foreach ($allPermissions as $permission) {
                DB::table('user_permissions_club')->insert([
                    'user_id' => $user->id,
                    'club_id' => $club->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Tự động tạo quỹ cho CLB
            try {
                Fund::create([
                    'club_id' => $club->id,
                    'name' => 'Quỹ ' . $club->name,
                    'balance' => 0,
                    'created_by' => $user->id,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Không thể tạo quỹ cho CLB mới: ' . $e->getMessage());
            }

            // Gửi thông báo cho tất cả admin về yêu cầu tạo CLB mới
            try {
                $this->notifyAdmins([
                    'sender_id' => $user->id,
                    'title' => 'Yêu cầu tạo CLB mới cần duyệt',
                    'message' => "Sinh viên {$user->name} đã gửi yêu cầu tạo CLB mới: \"{$club->name}\". Vui lòng xem xét và duyệt yêu cầu.",
                    'related_id' => $club->id,
                    'related_type' => 'Club',
                    'type' => 'club',
                ]);
            } catch (\Exception $e) {
                \Log::warning('Không thể gửi thông báo cho admin về CLB mới: ' . $e->getMessage());
                // Không fail toàn bộ request nếu thông báo lỗi
            }

        } catch (\Exception $e) {
            \Log::error('Lỗi khi tạo CLB: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Không thể tạo câu lạc bộ. Vui lòng thử lại sau.');
        }

        return redirect()->route('student.clubs.index')
            ->with('success', 'Yêu cầu tạo câu lạc bộ đã được gửi. Ban quản trị sẽ xem xét và phản hồi sớm nhất.');
    }

    public function events()
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Lấy danh sách CLB mà user là thành viên
        $userClubIds = $user->clubs->pluck('id')->toArray();

        // Lấy events đang diễn ra (đã bắt đầu nhưng chưa kết thúc) - chỉ lấy events đã được duyệt hoặc ongoing
        // Logic visibility: 
        // - public hoặc NULL: Tất cả mọi người đều thấy
        // - internal: CHỈ thành viên của CLB tạo sự kiện đó mới thấy (club_id của event)
        $ongoingEvents = Event::with(['club', 'creator', 'images'])
            ->whereIn('status', ['approved', 'ongoing'])
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->where(function($query) use ($userClubIds) {
                // Công khai hoặc NULL: tất cả mọi người thấy
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                      ->orWhereNull('visibility'); // Coi NULL là public
                })
                    // Hoặc nội bộ: CHỈ thành viên của CLB tạo sự kiện đó
                    ->orWhere(function($q) use ($userClubIds) {
                        $q->where('visibility', 'internal')
                          ->whereIn('club_id', $userClubIds); // Chỉ CLB tạo event mới được xem
                    });
            })
            ->orderBy('start_time', 'asc')
            ->get();

        // Lấy events sắp tới (chưa bắt đầu) - chỉ lấy events đã được duyệt
        $upcomingEvents = Event::with(['club', 'creator', 'images'])
            ->where('status', 'approved')
            ->where('start_time', '>', now())
            ->where('end_time', '>=', now())
            ->where(function($query) use ($userClubIds) {
                // Công khai hoặc NULL: tất cả mọi người thấy
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                      ->orWhereNull('visibility'); // Coi NULL là public
                })
                    // Hoặc nội bộ: CHỈ thành viên của CLB tạo sự kiện đó
                    ->orWhere(function($q) use ($userClubIds) {
                        $q->where('visibility', 'internal')
                          ->whereIn('club_id', $userClubIds); // Chỉ CLB tạo event mới được xem
                    });
            })
            ->orderBy('start_time', 'asc')
            ->get();

        // Gộp tất cả events chưa kết thúc (đang diễn ra + sắp tới) để đếm đăng ký
        $allActiveEvents = $ongoingEvents->merge($upcomingEvents);

        // Lấy events đã đăng ký bởi user
        $registeredEventIds = \App\Models\EventRegistration::where('user_id', $user->id)
            ->whereIn('status', ['registered', 'pending', 'approved'])
            ->pluck('event_id')
            ->toArray();

        // Events đã đăng ký: hiển thị nếu public hoặc internal mà user là thành viên CLB tạo event
        // Chỉ hiển thị các sự kiện chưa kết thúc
        $registeredEvents = Event::with(['club', 'creator', 'images'])
            ->whereIn('id', $registeredEventIds)
            ->whereIn('status', ['approved', 'ongoing'])
            ->where('end_time', '>=', now())
            ->where(function($query) use ($userClubIds) {
                // Công khai hoặc NULL: tất cả mọi người thấy
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                      ->orWhereNull('visibility'); // Coi NULL là public
                })
                    // Hoặc nội bộ: CHỈ thành viên của CLB tạo sự kiện đó
                    ->orWhere(function($q) use ($userClubIds) {
                        $q->where('visibility', 'internal')
                          ->whereIn('club_id', $userClubIds); // Chỉ CLB tạo event mới được xem
                    });
            })
            ->orderBy('start_time', 'asc')
            ->get();

        // Đếm số lượng đăng ký cho mỗi event (cả đang diễn ra và sắp tới)
        $eventRegistrations = \App\Models\EventRegistration::whereIn('event_id', $allActiveEvents->pluck('id'))
            ->whereIn('status', ['registered', 'pending', 'approved'])
            ->selectRaw('event_id, COUNT(*) as count')
            ->groupBy('event_id')
            ->pluck('count', 'event_id')
            ->toArray();

        // Thống kê sidebar - đếm events đã được duyệt hoặc đang diễn ra
        // Logic: public hoặc NULL = tất cả thấy, internal = chỉ thành viên CLB tạo event thấy
        $visibilityFilter = function($query) use ($userClubIds) {
            $query->where(function($q) {
                $q->where('visibility', 'public')
                  ->orWhereNull('visibility'); // Coi NULL là public
            })
            ->orWhere(function($q) use ($userClubIds) {
                $q->where('visibility', 'internal')
                  ->whereIn('club_id', $userClubIds); // Chỉ CLB tạo event mới được xem
            });
        };
        
        $todayEvents = Event::whereIn('status', ['approved', 'ongoing'])
            ->whereDate('start_time', now()->toDateString())
            ->where('end_time', '>=', now())
            ->where($visibilityFilter)
            ->count();

        $thisWeekEvents = Event::whereIn('status', ['approved', 'ongoing'])
            ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('end_time', '>=', now())
            ->where($visibilityFilter)
            ->count();

        $thisMonthEvents = Event::whereIn('status', ['approved', 'ongoing'])
            ->whereMonth('start_time', now()->month)
            ->whereYear('start_time', now()->year)
            ->where('end_time', '>=', now())
            ->where($visibilityFilter)
            ->count();

        // Sự kiện hot (có nhiều đăng ký nhất) - lấy events đã được duyệt hoặc đang diễn ra
        // Logic: public hoặc NULL = tất cả thấy, internal = chỉ thành viên CLB tạo event thấy
        $hotEvents = Event::with(['club'])
            ->whereIn('status', ['approved', 'ongoing'])
            ->where('end_time', '>=', now())
            ->where(function($query) use ($userClubIds) {
                // Công khai hoặc NULL: tất cả mọi người thấy
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                      ->orWhereNull('visibility'); // Coi NULL là public
                })
                    // Hoặc nội bộ: CHỈ thành viên của CLB tạo sự kiện đó
                    ->orWhere(function($q) use ($userClubIds) {
                        $q->where('visibility', 'internal')
                          ->whereIn('club_id', $userClubIds); // Chỉ CLB tạo event mới được xem
                    });
            })
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
            'hotEvents'
        ));
    }

    /**
     * Register for an event
     */
    public function registerEvent($eventId)
    {
        // Log để debug
        \Log::info('Register event called with eventId: ' . $eventId);
        
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đăng ký sự kiện.'
            ], 401);
        }

        try {
            // Chuyển đổi eventId sang integer để đảm bảo đúng kiểu
            $eventId = (int) $eventId;
            
            if ($eventId <= 0) {
                \Log::error('Invalid eventId: ' . $eventId);
                return response()->json([
                    'success' => false,
                    'message' => 'ID sự kiện không hợp lệ.'
                ], 400);
            }
            
            // Tìm event - chỉ tìm những event chưa bị xóa
            $event = Event::find($eventId);
            
            \Log::info('Event lookup result: ' . ($event ? 'Found (ID: ' . $event->id . ')' : 'Not found'));
            
            // Nếu không tìm thấy event, kiểm tra xem có bị soft delete không
            if (!$event) {
                $deletedEvent = Event::withTrashed()->find($eventId);
                if ($deletedEvent) {
                    \Log::info('Event found but soft deleted: ' . $eventId);
                    return response()->json([
                        'success' => false,
                        'message' => 'Sự kiện này đã bị xóa.'
                    ], 404);
                }
                
                // Kiểm tra xem có event nào với ID này không (kể cả bị xóa)
                $anyEvent = Event::withTrashed()->where('id', $eventId)->first();
                \Log::error('Event not found at all. eventId: ' . $eventId . ', anyEvent: ' . ($anyEvent ? 'exists' : 'not exists'));
                
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sự kiện với ID: ' . $eventId
                ], 404);
            }
            
            // Đảm bảo $event là một model instance, không phải collection
            if (!$event instanceof \App\Models\Event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sự kiện.'
                ], 404);
            }

            // Kiểm tra và thêm các cột cần thiết nếu chưa có
            $columnNames = \Illuminate\Support\Facades\Schema::getColumnListing('events');
            $columnsToAdd = [];
            
            if (!in_array('status', $columnNames)) {
                $columnsToAdd['status'] = "ALTER TABLE events ADD COLUMN status VARCHAR(50) DEFAULT 'pending'";
            }
            if (!in_array('visibility', $columnNames)) {
                $columnsToAdd['visibility'] = "ALTER TABLE events ADD COLUMN visibility ENUM('public', 'internal') DEFAULT 'public' AFTER status";
            }
            
            if (!empty($columnsToAdd)) {
                try {
                    foreach ($columnsToAdd as $columnName => $sql) {
                        \Illuminate\Support\Facades\DB::statement($sql);
                    }
                    $event->refresh(); // Refresh để load cột mới
                } catch (\Exception $e) {
                    \Log::error('Failed to add columns to events table: ' . $e->getMessage());
                }
            }

            // Kiểm tra sự kiện có bị hủy không
            $eventStatus = $event->status ?? 'pending';
            if ($eventStatus === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này đã bị hủy.'
                ], 400);
            }

            // Kiểm tra sự kiện đã được duyệt hoặc đang diễn ra
            if (!in_array($eventStatus, ['approved', 'ongoing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sự kiện này chưa được duyệt hoặc không khả dụng.'
                ], 400);
            }

            // Kiểm tra visibility: nếu là internal và user không phải thành viên CLB thì không cho đăng ký
            // Lấy visibility với giá trị mặc định 'public' nếu NULL
            $eventVisibility = $event->visibility ?? 'public';
            if ($eventVisibility === 'internal') {
                $isClubMember = $event->club_id && $user->clubs->contains('id', $event->club_id);
                if (!$isClubMember) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sự kiện này chỉ dành cho thành viên CLB ' . ($event->club->name ?? '') . '.'
                    ], 403);
                }
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
            try {
                $registration = \App\Models\EventRegistration::create([
                'user_id' => $user->id,
                'event_id' => $eventId,
                'status' => 'registered',
                'joined_at' => now(),
            ]);

                // Kiểm tra xem registration đã được tạo thành công chưa
                if (!$registration || !$registration->id) {
                    throw new \Exception('Không thể tạo đăng ký sự kiện.');
                }

                // Gửi email xác nhận đăng ký (không bắt buộc)
                try {
                    if ($user->email && class_exists(\App\Mail\EventRegistrationConfirmation::class)) {
                        \Mail::to($user->email)->send(new \App\Mail\EventRegistrationConfirmation($user, $event));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send event registration email: ' . $e->getMessage());
                    // Không throw exception, chỉ log lỗi để không ảnh hưởng đến việc đăng ký
                }

            return response()->json([
                'success' => true,
                    'message' => 'Đăng ký tham gia sự kiện thành công!'
                ], 200);
            } catch (\Illuminate\Database\QueryException $e) {
                \Log::error('Database error when creating event registration: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo đăng ký. Vui lòng thử lại sau.'
                ], 500);
            } catch (\Exception $e) {
                \Log::error('Error creating event registration: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi đăng ký: ' . $e->getMessage()
                ], 500);
            }

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

            // Kiểm tra nếu user là thành viên của CLB tổ chức sự kiện
            $isClubMember = false;
            if ($event->club_id && $user->clubs->contains('id', $event->club_id)) {
                $isClubMember = true;
            }

            // Kiểm tra visibility: nếu là internal và user không phải thành viên CLB thì không cho xem
            // Lấy visibility với giá trị mặc định 'public' nếu NULL
            $eventVisibility = $event->visibility ?? 'public';
            if ($eventVisibility === 'internal' && !$isClubMember) {
                return redirect()->route('student.events.index')
                    ->with('error', 'Sự kiện này chỉ dành cho thành viên CLB ' . ($event->club->name ?? '') . '.');
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

            // Đếm số lượng đăng ký
            $registrationCount = \App\Models\EventRegistration::where('event_id', $eventId)
                ->whereIn('status', ['registered', 'pending', 'approved'])
                ->count();

            $availableSlots = $event->max_participants > 0 ? $event->max_participants - $registrationCount : null;
            $isFull = $event->max_participants > 0 && $registrationCount >= $event->max_participants;
            $isDeadlinePassed = $event->registration_deadline && $event->registration_deadline < now();
            
            // Kiểm tra xem user có thể hủy đăng ký không
            // Có thể hủy nếu: đã đăng ký, sự kiện chưa bị hủy, và sự kiện chưa bắt đầu hoặc chưa quá deadline
            $canCancelRegistration = $isRegistered 
                && $event->status !== 'cancelled' 
                && ($event->start_time > now() || ($event->registration_deadline && $event->registration_deadline > now()));

            // Track viewer - ghi nhận người đang xem sự kiện
            try {
                \App\Models\EventViewer::updateOrCreate(
                    [
                        'event_id' => $eventId,
                        'user_id' => $user->id,
                    ],
                    [
                        'viewed_at' => now(),
                        'last_activity_at' => now(),
                    ]
                );
            } catch (\Exception $e) {
                // Bỏ qua nếu bảng chưa được migrate
                \Log::warning('EventViewer tracking failed: ' . $e->getMessage());
            }

            // Lấy số người đang xem (active trong 5 phút gần nhất)
            $activeViewersCount = 0;
            try {
                $activeViewersCount = \App\Models\EventViewer::where('event_id', $eventId)
                    ->where('last_activity_at', '>=', now()->subMinutes(5))
                    ->count();
            } catch (\Exception $e) {
                // Bỏ qua nếu bảng chưa được migrate
            }

            // Kiểm tra quyền xem danh sách người đăng ký
            // Chỉ cán bộ CLB (leader, vice_president) hoặc admin mới xem được
            $canViewRegistrations = false;
            $registrations = collect();
            
            if ($event->club_id) {
                $userPosition = $user->getPositionInClub($event->club_id);
                // Cán bộ CLB (leader, vice_president) hoặc có quyền quản lý sự kiện
                if (in_array($userPosition, ['leader', 'vice_president']) || 
                    $user->hasPermission('tao_su_kien', $event->club_id)) {
                    $canViewRegistrations = true;
                }
            }
            
            // Admin cũng xem được
            if (session('is_admin')) {
                $canViewRegistrations = true;
            }
            
            // Lấy danh sách người đăng ký nếu có quyền
            if ($canViewRegistrations) {
                $registrations = \App\Models\EventRegistration::with('user')
                    ->where('event_id', $eventId)
                    ->whereIn('status', ['registered', 'pending', 'approved'])
                    ->orderBy('joined_at', 'desc')
                    ->get();
            }

            return view('student.events.show', compact(
                'user',
                'event',
                'isRegistered',
                'registrationCount',
                'availableSlots',
                'isFull',
                'isDeadlinePassed',
                'isClubMember',
                'canCancelRegistration',
                'activeViewersCount',
                'canViewRegistrations',
                'registrations'
            ));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('student.events.index')->with('error', 'Không tìm thấy sự kiện.');
        } catch (\Exception $e) {
            return redirect()->route('student.events.index')->with('error', 'Có lỗi xảy ra khi tải sự kiện.');
        }
    }

    /**
     * Update viewer activity (ping)
     */
    public function updateViewerActivity($eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            \App\Models\EventViewer::updateOrCreate(
                [
                    'event_id' => $eventId,
                    'user_id' => $user->id,
                ],
                [
                    'viewed_at' => now(),
                    'last_activity_at' => now(),
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get active viewers for an event
     */
    public function getEventViewers($eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $event = Event::findOrFail($eventId);
            
            // Lấy danh sách người đang xem (active trong 5 phút gần nhất)
            $viewers = \App\Models\EventViewer::where('event_id', $eventId)
                ->where('last_activity_at', '>=', now()->subMinutes(5))
                ->with('user:id,name,avatar')
                ->orderBy('last_activity_at', 'desc')
                ->get();

            $viewersData = $viewers->map(function($viewer) {
                $diffMinutes = $viewer->last_activity_at->diffInMinutes(now());
                return [
                    'id' => $viewer->user->id,
                    'name' => $viewer->user->name,
                    'avatar' => $viewer->user->avatar ?? '/images/avatar/avatar.png',
                    'is_online' => $diffMinutes < 1,
                    'last_activity' => $diffMinutes < 1 ? 'Đang online' : $viewer->last_activity_at->diffForHumans(),
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $viewers->count(),
                'viewers' => $viewersData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

        // Lấy danh sách CLB mà user có quyền tạo sự kiện
        $clubsWithPermission = $user->clubs->filter(function ($club) use ($user) {
            return $user->hasPermission('tao_su_kien', $club->id);
        });

        if ($clubsWithPermission->count() === 0) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn cần tham gia CLB và có quyền tạo sự kiện.');
        }

        // Chọn CLB theo tham số hoặc CLB đầu tiên đủ quyền
        $selectedClubId = request()->input('club_id');
        if ($selectedClubId) {
            $selectedClub = $clubsWithPermission->firstWhere('id', (int) $selectedClubId);
            if (!$selectedClub) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không có quyền tạo sự kiện cho CLB đã chọn.');
            }
        } else {
            $selectedClub = $clubsWithPermission->first();
        }

        $clubId = $selectedClub->id;

        return view('student.events.create', [
            'user'    => $user,
            'userClub'=> $selectedClub,
            'clubId'  => $clubId,
            'clubs'   => $clubsWithPermission,
        ]);
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

        try {
            $request->validate([
                'club_id' => 'required|integer|exists:clubs,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:10000',
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
                'mode' => 'required|in:offline,online,hybrid',
                'location' => 'nullable|string|max:255',
                'max_participants' => 'nullable|integer|min:1',
                'registration_deadline' => 'nullable|date|before_or_equal:start_time',
                'visibility' => 'required|in:public,internal',
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

            // Lấy CLB từ request và kiểm tra quyền
            $clubId = (int) $request->input('club_id');
            $userClub = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$userClub) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
            if (!$user->hasPermission('tao_su_kien', $clubId)) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không có quyền tạo sự kiện cho CLB này.');
            }

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
                'visibility' => "ENUM('public', 'internal') DEFAULT 'public'",
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
            
            // Đảm bảo visibility luôn có trong columnNames sau khi kiểm tra/tạo
            if (!in_array('visibility', $columnNames)) {
                $columnNames[] = 'visibility';
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
                'visibility' => $request->visibility ?? 'public', // Luôn thêm visibility với giá trị mặc định public
            ];
            
            // Thêm các field mới vào eventData
            if (in_array('registration_deadline', $columnNames)) {
                $eventData['registration_deadline'] = $request->registration_deadline;
            }
            if (in_array('main_organizer', $columnNames)) {
                // Luôn lưu người phụ trách chính là người tạo sự kiện
                $eventData['main_organizer'] = $user->name;
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

            $this->notifyAdmins([
                'sender_id' => $user->id,
                'title' => 'Sự kiện mới cần duyệt',
                'message' => "Sự kiện \"{$event->title}\" của CLB \"{$event->club->name}\" đã được tạo và đang chờ quản trị viên duyệt.",
                'related_id' => $event->id,
                'related_type' => 'Event',
                'type' => 'event',
            ]);

            $this->notifyAdmins([
                'sender_id' => $user->id,
                'title' => 'Sự kiện đã được tạo',
                'message' => "Sự kiện \"{$event->title}\" của CLB \"{$event->club->name}\" vừa được tạo và đang chờ duyệt.",
                'related_id' => $event->id,
                'related_type' => 'Event',
                'type' => 'event',
            ]);

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
     * Send notification to admins with safe column handling
     */
    protected function notifyAdmins(array $payload)
    {
        if (!Schema::hasTable('notifications')) {
            return null;
        }

        // Chỉ lấy các field cơ bản, luôn có trong database
        $notificationData = [
            'sender_id' => $payload['sender_id'],
            'title' => $payload['title'] ?? '',
            'message' => $payload['message'] ?? '',
        ];

        // Chỉ thêm các field optional nếu cột tồn tại trong database
        // Kiểm tra từng cột riêng biệt để đảm bảo chính xác
        if (Schema::hasColumn('notifications', 'type')) {
            if (isset($payload['type']) && !empty($payload['type'])) {
                $notificationData['type'] = $payload['type'];
            }
        }
        if (Schema::hasColumn('notifications', 'related_id')) {
            if (isset($payload['related_id']) && $payload['related_id'] !== null) {
                $notificationData['related_id'] = $payload['related_id'];
            }
        }
        if (Schema::hasColumn('notifications', 'related_type')) {
            if (isset($payload['related_type']) && !empty($payload['related_type'])) {
                $notificationData['related_type'] = $payload['related_type'];
            }
        }

        try {
            // Đảm bảo chỉ insert các field hợp lệ
            $notification = Notification::create($notificationData);
            if (!$notification) {
                return null;
            }

            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                NotificationTarget::create([
                    'notification_id' => $notification->id,
                    'target_type' => 'user',
                    'target_id' => $admin->id,
                ]);
                NotificationRead::create([
                    'notification_id' => $notification->id,
                    'user_id' => $admin->id,
                    'is_read' => false,
                ]);
            }

            return $notification;
        } catch (\Illuminate\Database\QueryException $e) {
            // Nếu lỗi do thiếu cột, thử thêm cột và tạo lại
            if (strpos($e->getMessage(), "Unknown column 'type'") !== false || 
                strpos($e->getMessage(), "Unknown column") !== false) {
                try {
                    // Thử thêm các cột cần thiết
                    if (!Schema::hasColumn('notifications', 'type')) {
                        DB::statement("ALTER TABLE notifications ADD COLUMN type VARCHAR(50) NULL AFTER sender_id");
                    }
                    if (!Schema::hasColumn('notifications', 'related_id')) {
                        DB::statement("ALTER TABLE notifications ADD COLUMN related_id BIGINT UNSIGNED NULL AFTER type");
                    }
                    if (!Schema::hasColumn('notifications', 'related_type')) {
                        DB::statement("ALTER TABLE notifications ADD COLUMN related_type VARCHAR(50) NULL AFTER related_id");
                    }
                    
                    // Thử tạo lại notification
                    $notification = Notification::create($notificationData);
                    if ($notification) {
                        $admins = User::where('is_admin', true)->get();
                        foreach ($admins as $admin) {
                            NotificationTarget::create([
                                'notification_id' => $notification->id,
                                'target_type' => 'user',
                                'target_id' => $admin->id,
                            ]);
                            NotificationRead::create([
                                'notification_id' => $notification->id,
                                'user_id' => $admin->id,
                                'is_read' => false,
                            ]);
                        }
                        return $notification;
                    }
                } catch (\Exception $retryException) {
                    \Log::error('Failed to auto-fix and create admin notification: ' . $retryException->getMessage());
                }
            }
            \Log::error('Failed to create admin notification: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            \Log::error('Failed to create admin notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Show edit event form for user
     */
    public function editEvent($eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        try {
            $event = Event::with('images')->findOrFail($eventId);
            
            // Kiểm tra quyền: chỉ người tạo hoặc thành viên CLB có quyền tạo sự kiện mới được chỉnh sửa
            if ($event->created_by != $user->id) {
                // Kiểm tra xem user có phải là thành viên của CLB và có quyền tạo sự kiện không
                if (!$event->club_id || !$user->clubs->contains('id', $event->club_id)) {
                    return redirect()->route('student.events.index')
                        ->with('error', 'Bạn không có quyền chỉnh sửa sự kiện này.');
                }
                
                if (!$user->hasPermission('tao_su_kien', $event->club_id)) {
                    return redirect()->route('student.events.index')
                        ->with('error', 'Bạn không có quyền chỉnh sửa sự kiện này.');
                }
            }
            
            // Ngăn chặn chỉnh sửa nếu sự kiện đã hoàn thành, đang diễn ra hoặc đã bị hủy
            if ($event->status === 'completed') {
                return redirect()->route('student.events.show', $event->id)
                    ->with('error', 'Không thể chỉnh sửa sự kiện đã hoàn thành.');
            }
            
            if ($event->status === 'ongoing') {
                return redirect()->route('student.events.show', $event->id)
                    ->with('error', 'Không thể chỉnh sửa sự kiện đang diễn ra.');
            }
            
            // Lấy CLB của user để hiển thị trong form
            $userClub = $user->clubs->where('id', $event->club_id)->first();
            if (!$userClub) {
                return redirect()->route('student.events.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB tổ chức sự kiện này.');
            }
            
            return view('student.events.edit', compact('event', 'user', 'userClub'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('student.events.index')->with('error', 'Không tìm thấy sự kiện.');
        } catch (\Exception $e) {
            return redirect()->route('student.events.index')->with('error', 'Có lỗi xảy ra khi tải form chỉnh sửa.');
        }
    }

    /**
     * Update event created by user
     */
    public function updateEvent(Request $request, $eventId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        try {
            $event = Event::findOrFail($eventId);
            
            // Kiểm tra quyền
            if ($event->created_by != $user->id) {
                if (!$event->club_id || !$user->clubs->contains('id', $event->club_id)) {
                    return redirect()->route('student.events.index')
                        ->with('error', 'Bạn không có quyền chỉnh sửa sự kiện này.');
                }
                
                if (!$user->hasPermission('tao_su_kien', $event->club_id)) {
                    return redirect()->route('student.events.index')
                        ->with('error', 'Bạn không có quyền chỉnh sửa sự kiện này.');
                }
            }
            
            // Ngăn chặn chỉnh sửa nếu sự kiện đã hoàn thành hoặc đang diễn ra
            if ($event->status === 'completed') {
                return back()->with('error', 'Không thể chỉnh sửa sự kiện đã hoàn thành.');
            }
            
            if ($event->status === 'ongoing') {
                return back()->with('error', 'Không thể chỉnh sửa sự kiện đang diễn ra.');
            }
            
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:10000',
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                'remove_images' => 'nullable|array',
                'remove_images.*' => 'integer|exists:event_images,id',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'mode' => 'required|in:offline,online,hybrid',
                'location' => 'nullable|string|max:255',
                'max_participants' => 'nullable|integer|min:1',
                'registration_deadline' => 'nullable|date|before_or_equal:start_time',
                'visibility' => 'required|in:public,internal',
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
                'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
                'registration_deadline.before_or_equal' => 'Hạn chót đăng ký phải trước hoặc bằng thời gian bắt đầu sự kiện.',
                'contact_email.email' => 'Email không hợp lệ.',
            ]);

            // Validate guest_other_info khi có chọn "other"
            if (is_array($request->guest_types) && in_array('other', $request->guest_types)) {
                if (empty(trim($request->guest_other_info ?? ''))) {
                    return back()->withErrors(['guest_other_info' => 'Vui lòng nhập thông tin khách mời khi chọn "Khác..."'])->withInput();
                }
            }

            // Cập nhật slug nếu title thay đổi
            $slug = $event->slug;
            if ($request->title !== $event->title) {
                $slugCandidate = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->title)));
                $slugCandidate = preg_replace('/-+/', '-', $slugCandidate);
                $slugCandidate = trim($slugCandidate, '-');
                $originalSlug = $slugCandidate;
                $counter = 1;
                while (Event::where('slug', $slugCandidate)->where('id', '!=', $event->id)->exists()) {
                    $slugCandidate = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $slug = $slugCandidate;
            }

            // Xử lý upload files (chỉ upload nếu có file mới)
            if ($request->hasFile('proposal_file')) {
                if ($event->proposal_file) {
                    \Storage::disk('public')->delete($event->proposal_file);
                }
                $proposalFilePath = $request->file('proposal_file')->store('events/files', 'public');
            } else {
                $proposalFilePath = $event->proposal_file;
            }
            
            if ($request->hasFile('poster_file')) {
                if ($event->poster_file) {
                    \Storage::disk('public')->delete($event->poster_file);
                }
                $posterFilePath = $request->file('poster_file')->store('events/posters', 'public');
            } else {
                $posterFilePath = $event->poster_file;
            }
            
            if ($request->hasFile('permit_file')) {
                if ($event->permit_file) {
                    \Storage::disk('public')->delete($event->permit_file);
                }
                $permitFilePath = $request->file('permit_file')->store('events/permits', 'public');
            } else {
                $permitFilePath = $event->permit_file;
            }
            
            // Xử lý contact_info
            $contactInfo = null;
            if ($request->contact_phone || $request->contact_email) {
                $contactInfo = json_encode([
                    'phone' => $request->contact_phone,
                    'email' => $request->contact_email,
                ]);
            } elseif ($event->contact_info) {
                $contactInfo = $event->contact_info;
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
            } elseif ($event->guests) {
                $guestsData = $event->guests;
            }
            
            // Xóa ảnh được chọn
            if ($request->has('remove_images') && is_array($request->remove_images)) {
                foreach ($request->remove_images as $imageId) {
                    $image = \App\Models\EventImage::find($imageId);
                    if ($image && $image->event_id == $event->id) {
                        \Storage::disk('public')->delete($image->image_path);
                        $image->delete();
                    }
                }
            }
            
            // Đảm bảo cột visibility tồn tại
            $columns = DB::select("SHOW COLUMNS FROM events");
            $columnNames = array_column($columns, 'Field');
            
            if (!in_array('visibility', $columnNames)) {
                try {
                    DB::statement("ALTER TABLE events ADD COLUMN visibility ENUM('public', 'internal') DEFAULT 'public'");
                    $columnNames[] = 'visibility';
                } catch (\Exception $e) {
                    \Log::warning("UpdateEvent - Failed to add column visibility: " . $e->getMessage());
                }
            }
            
            // Cập nhật event
            $updateData = [
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'mode' => $request->mode,
                'location' => $request->location,
                'max_participants' => $request->max_participants,
                'registration_deadline' => $request->registration_deadline,
                // Người phụ trách luôn là người đang chỉnh sửa (người tạo)
                'main_organizer' => $user->name,
                'organizing_team' => $request->organizing_team,
                'co_organizers' => $request->co_organizers,
                'contact_info' => $contactInfo,
                'proposal_file' => $proposalFilePath,
                'poster_file' => $posterFilePath,
                'permit_file' => $permitFilePath,
                'guests' => $guestsData,
                'status' => 'pending', // Chuyển về chế độ chờ duyệt sau khi chỉnh sửa
                'visibility' => $request->visibility ?? 'public', // Luôn cập nhật visibility
            ];
            
            $event->update($updateData);
            
            // Xử lý upload nhiều ảnh mới
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

            $this->notifyAdmins([
                'sender_id' => $user->id,
                'title' => 'Sự kiện cần duyệt lại',
                'message' => "Sự kiện \"{$event->title}\" vừa được chỉnh sửa và đang chờ admin duyệt lại.",
                'related_id' => $event->id,
                'related_type' => 'Event',
                'type' => 'event',
            ]);

            return redirect()->route('student.events.show', $event->id)
                ->with('success', 'Cập nhật sự kiện thành công! Sự kiện đã được chuyển về trạng thái chờ duyệt.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('UpdateEvent Error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật sự kiện: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Manage events for user's club
     */
    public function manageEvents(Request $request)
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

        // Lấy club từ query parameter hoặc tìm CLB mà user có quyền quản lý sự kiện
        $clubId = $request->input('club');
        
        if ($clubId) {
            $userClub = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$userClub) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
        } else {
            // Tìm CLB mà user có quyền quản lý sự kiện (leader hoặc vice_president)
            $userClub = null;
            foreach ($user->clubs as $club) {
                $position = $user->getPositionInClub($club->id);
                if (in_array($position, ['leader', 'vice_president'])) {
                    if ($user->hasPermission('tao_su_kien', $club->id)) {
                        $userClub = $club;
                        break;
                    }
                }
            }
            
            // Nếu không tìm thấy, lấy CLB đầu tiên
            if (!$userClub) {
        $userClub = $user->clubs->first();
            }
        }
        
        $clubId = $userClub->id;
        
        // Kiểm tra quyền tạo sự kiện
        if (!$user->hasPermission('tao_su_kien', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý sự kiện cho CLB này.');
        }

        // Query sự kiện với bộ lọc
        $query = Event::with(['club', 'creator', 'images'])
            ->where('club_id', $clubId);

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Sắp xếp và phân trang
        $events = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        // Tính toán thống kê
        $stats = [
            'total' => Event::where('club_id', $clubId)->count(),
            'pending' => Event::where('club_id', $clubId)->where('status', 'pending')->count(),
            'approved' => Event::where('club_id', $clubId)->where('status', 'approved')->count(),
            'ongoing' => Event::where('club_id', $clubId)->where('status', 'ongoing')->count(),
            'completed' => Event::where('club_id', $clubId)->where('status', 'completed')->count(),
            'cancelled' => Event::where('club_id', $clubId)->where('status', 'cancelled')->count(),
        ];

        // Lấy tất cả events theo từng trạng thái (không phân trang) cho tabs
        $allEvents = Event::with(['club', 'creator', 'images'])
            ->where('club_id', $clubId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $pendingEvents = $allEvents->where('status', 'pending');
        $approvedEvents = $allEvents->where('status', 'approved');
        $ongoingEvents = $allEvents->where('status', 'ongoing');
        $completedEvents = $allEvents->where('status', 'completed');
        $cancelledEvents = $allEvents->where('status', 'cancelled');

        return view('student.events.manage', compact(
            'user', 
            'userClub', 
            'clubId', 
            'events', 
            'stats',
            'allEvents',
            'pendingEvents',
            'approvedEvents',
            'ongoingEvents',
            'completedEvents',
            'cancelledEvents'
        ));
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

    public function notifications(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Lấy danh sách CLB mà user là thành viên
        $userClubIds = $user->clubs->pluck('id')->toArray();
        
        // Lấy notifications từ model Notification thông qua NotificationTarget
        $notificationsQuery = \App\Models\Notification::with(['sender', 'reads' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->whereHas('targets', function($query) use ($user, $userClubIds) {
                $query->where(function($q) use ($user, $userClubIds) {
                    // Target là user cụ thể
                    $q->where(function($subQ) use ($user) {
                        $subQ->where('target_type', 'user')
                             ->where('target_id', $user->id);
                    });
                    // Hoặc target là tất cả users
                    $q->orWhere(function($subQ) {
                        $subQ->where('target_type', 'all');
                    });
                    // Hoặc target là club mà user là thành viên
                    if (!empty($userClubIds)) {
                        $q->orWhere(function($subQ) use ($userClubIds) {
                            $subQ->where('target_type', 'club')
                                 ->whereIn('target_id', $userClubIds);
                        });
                    }
                });
            })
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');
        
        // Filter theo category
        $category = $request->input('category');
        if ($category === 'system') {
            $notificationsQuery->where('type', 'system');
        } elseif ($category === 'announcements') {
            $notificationsQuery->where('type', 'announcement');
        } elseif ($category === 'clubs') {
            $notificationsQuery->where('type', 'club');
        }
        
        // Filter theo tab (Tất cả, Chưa đọc, Đã đọc)
        $filter = $request->input('filter', 'all');
        if ($filter === 'unread') {
            // Chưa đọc: chưa có record trong notification_reads hoặc is_read = 0
            $notificationsQuery->whereDoesntHave('reads', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('is_read', 1);
            });
        } elseif ($filter === 'read') {
            // Đã đọc: có record trong notification_reads với is_read = 1
            $notificationsQuery->whereHas('reads', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('is_read', 1);
            });
        }
        
        // Phân trang notifications
        $notifications = $notificationsQuery->paginate(10)->withQueryString();

        // Thêm thuộc tính is_read cho mỗi notification
        $notifications->getCollection()->transform(function($notification) use ($user) {
            $read = $notification->reads->firstWhere('user_id', $user->id);
            $notification->is_read = $read ? (bool)$read->is_read : false;
            return $notification;
        });

        $notificationsCollection = $notifications->getCollection();
        $announcementNotifications = $notificationsCollection->filter(function($notification) {
            return $notification->type === 'announcement' || $notification->related_type === Event::class;
        });
        $systemNotifications = $notificationsCollection->filter(function($notification) {
            return $notification->type === 'system';
        });
        $eventNotifications = $notificationsCollection->filter(function($notification) {
            return $notification->type === 'event'
                || strtolower($notification->related_type ?? '') === 'app\\models\\event'
                || strtolower($notification->related_type ?? '') === 'event';
        });
        
        // Tính stats
        $allNotificationsQuery = \App\Models\Notification::whereHas('targets', function($query) use ($user, $userClubIds) {
                $query->where(function($q) use ($user, $userClubIds) {
                    $q->where(function($subQ) use ($user) {
                        $subQ->where('target_type', 'user')
                             ->where('target_id', $user->id);
                    });
                    $q->orWhere(function($subQ) {
                        $subQ->where('target_type', 'all');
                    });
                    if (!empty($userClubIds)) {
                        $q->orWhere(function($subQ) use ($userClubIds) {
                            $subQ->where('target_type', 'club')
                                 ->whereIn('target_id', $userClubIds);
                        });
                    }
                });
            })
            ->whereNull('deleted_at');
        
        $hasTypeColumn = Schema::hasColumn('notifications', 'type');

        $stats = [
            'system' => $hasTypeColumn
                ? (clone $allNotificationsQuery)
                    ->where('type', 'system')
                    ->whereDoesntHave('reads', function($query) use ($user) {
                        $query->where('user_id', $user->id)->where('is_read', 1);
                    })
                    ->count()
                : 0,
            'announcements' => $hasTypeColumn
                ? (clone $allNotificationsQuery)
                    ->where('type', 'announcement')
                    ->whereDoesntHave('reads', function($query) use ($user) {
                        $query->where('user_id', $user->id)->where('is_read', 1);
                    })
                    ->count()
                : 0,
            'clubs' => $hasTypeColumn
                ? (clone $allNotificationsQuery)
                    ->where('type', 'club')
                    ->whereDoesntHave('reads', function($query) use ($user) {
                        $query->where('user_id', $user->id)->where('is_read', 1);
                    })
                    ->count()
                : 0,
            'awards' => 0,
        ];
        
        // Lấy cài đặt thông báo từ session hoặc database
        $notificationSettings = [
            'email' => session('notification_settings.email', true),
            'push' => session('notification_settings.push', true),
            'event' => session('notification_settings.event', true),
            'club' => session('notification_settings.club', true),
        ];
        
        return view('student.notifications.index', compact(
            'user',
            'notifications',
            'announcementNotifications',
            'systemNotifications',
            'eventNotifications',
            'stats',
            'filter',
            'category',
            'notificationSettings'
        ));
    }
    
    /**
     * Save notification settings
     */
    public function saveNotificationSettings(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $settings = [
            'email' => $request->input('email', false),
            'push' => $request->input('push', false),
            'event' => $request->input('event', false),
            'club' => $request->input('club', false),
        ];
        
        // Lưu vào session
        session(['notification_settings' => $settings]);
        
        // Có thể lưu vào database nếu có bảng user_settings
        // UserSetting::updateOrCreate(
        //     ['user_id' => $user->id],
        //     ['notification_settings' => json_encode($settings)]
        // );
        
        return response()->json(['success' => true, 'message' => 'Đã lưu cài đặt thông báo']);
    }

    public function markNotificationRead($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        try {
            $notification = \App\Models\Notification::findOrFail($id);
            
            // Kiểm tra xem user có quyền đọc thông báo này không
            $hasAccess = $notification->targets()
                ->where('target_type', 'user')
                ->where('target_id', $user->id)
                ->exists();
            
            if (!$hasAccess) {
                return back()->with('error', 'Bạn không có quyền truy cập thông báo này.');
            }

            // Cập nhật hoặc tạo notification_read record
            $notificationRead = \App\Models\NotificationRead::firstOrNew([
                'notification_id' => $notification->id,
                'user_id' => $user->id,
            ]);
            
            $notificationRead->is_read = true;
            $notificationRead->save();

            return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi đánh dấu thông báo.');
        }
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
        $clubStats = [
            'members' => ['active' => 0, 'pending' => 0],
            'events' => ['total' => 0, 'upcoming' => 0],
            'posts' => 0,
            'announcements' => ['total' => 0, 'today' => 0],
        ];
        $clubMembers = collect();
        $allPermissions = collect();

        if ($user->clubs->count() > 0) {
            // Tìm CLB mà user có quyền quản lý (leader, vice_president, treasurer)
            $userClub = null;
            $clubId = null;
            $userPosition = null;
            
            foreach ($user->clubs as $club) {
                $clubMember = ClubMember::where('user_id', $user->id)
                    ->where('club_id', $club->id)
                    ->whereIn('status', ['approved', 'active'])
                    ->first();
                
                if ($clubMember && in_array($clubMember->position, ['leader', 'vice_president', 'treasurer'])) {
                    $userClub = $club;
                    $clubId = $club->id;
                    $userPosition = $clubMember->position;
                    break; // Lấy CLB đầu tiên có quyền quản lý
                }
            }
            
            // Nếu không tìm thấy CLB có quyền quản lý, lấy CLB đầu tiên để hiển thị thông báo
            if (!$userClub && $user->clubs->count() > 0) {
            $userClub = $user->clubs->first();
            $clubId = $userClub->id;
                $clubMember = ClubMember::where('user_id', $user->id)
                    ->where('club_id', $clubId)
                    ->whereIn('status', ['approved', 'active'])
                    ->first();
            $userPosition = $clubMember ? $clubMember->position : null;
            }
            
            // Leader, Vice President và Treasurer có quyền quản lý CLB
            $hasManagementRole = in_array($userPosition, ['leader', 'vice_president', 'treasurer']);

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
            $totalPosts = Post::where('club_id', $clubId)
                ->where('type', 'post')
                ->where('status', '!=', 'deleted')
                ->count();
            $totalAnnouncements = Post::where('club_id', $clubId)
                ->where('type', 'announcement')
                ->where('status', '!=', 'deleted')
                ->count();
            $todayAnnouncements = Post::where('club_id', $clubId)
                ->where('type', 'announcement')
                ->whereDate('created_at', now()->toDateString())
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
                'posts' => $totalPosts,
                'announcements' => ['total' => $totalAnnouncements, 'today' => $todayAnnouncements],
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
        }

        // Truyền thêm $clubId để tránh lỗi undefined variable trong view
        return view(
            'student.club-management.index',
            compact('user', 'hasManagementRole', 'userPosition', 'userClub', 'clubId', 'clubStats', 'clubMembers', 'allPermissions')
        );
    }

    /**
     * Club post & announcement management
     */
    public function clubManagementPosts(Request $request, $clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        if (!$user->clubs->contains('id', $clubId)) {
            return redirect()
                ->route('student.club-management.index')
                ->with('error', 'Bạn không thuộc câu lạc bộ này.');
        }

        $position = $user->getPositionInClub($clubId);
        if (!$position || !in_array($position, ['leader', 'vice_president'])) {
            return redirect()
                ->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền quản lý bài viết của CLB này.');
        }

        $activeTab = $request->get('tab', 'posts');

        $postsQuery = Post::with(['user', 'attachments'])
            ->where('club_id', $clubId)
            ->where('type', 'post');

        if ($search = $request->get('search')) {
            $postsQuery->where('title', 'like', '%' . $search . '%');
        }

        $status = $request->get('status');
        if ($status && $status !== 'all') {
            $postsQuery->where('status', $status);
        }

        $posts = $postsQuery
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->except('page'));

        $announcementsQuery = Post::with('user')
            ->where('club_id', $clubId)
            ->where('type', 'announcement');

        if ($announcementSearch = $request->get('announcement_search')) {
            $announcementsQuery->where('title', 'like', '%' . $announcementSearch . '%');
        }

        $announcementStatus = $request->get('announcement_status');
        if ($announcementStatus && $announcementStatus !== 'all') {
            $announcementsQuery->where('status', $announcementStatus);
        }

        $announcements = $announcementsQuery
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->except('page'));

        $canPostAnnouncement = $user->hasPermission('dang_thong_bao', $clubId);

        return view('student.club-management.posts', [
            'user' => $user,
            'club' => $club,
            'clubId' => $clubId,
            'activeTab' => $activeTab,
            'posts' => $posts,
            'announcements' => $announcements,
            'canPostAnnouncement' => $canPostAnnouncement,
        ]);
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

        // Cho phép tất cả thành viên đã duyệt/hoạt động xem danh sách
        $position = $user->getPositionInClub($clubId);
        $isMember = in_array($position, ['leader', 'vice_president', 'treasurer', 'member']);
        if (!$isMember) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không có quyền truy cập thành viên CLB này.');
        }
        $canManageMembers = in_array($position, ['leader', 'vice_president', 'treasurer']);

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
            'canManageMembers' => $canManageMembers,
        ]);
    }

    /**
     * Show member details
     */
    public function showMember($clubId, $memberId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = (int) $clubId;
        $club = Club::findOrFail($clubId);

        // Kiểm tra quyền truy cập (phải là thành viên của CLB)
        $position = $user->getPositionInClub($clubId);
        $isMember = in_array($position, ['leader', 'vice_president', 'treasurer', 'member']);
        if (!$isMember) {
            return redirect()->route('student.club-management.members', ['club' => $clubId])
                ->with('error', 'Bạn không có quyền truy cập thông tin thành viên CLB này.');
        }

        // Lấy thông tin thành viên
        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->whereIn('status', ['approved', 'active'])
            ->firstOrFail();

        // Lấy quyền của thành viên
        $permissionNames = $clubMember->user
            ? $clubMember->user->getClubPermissions($clubId)
            : [];

        $allPermissions = Permission::orderBy('name')->get();

        // Nhãn tiếng Việt cho quyền
        $permLabels = [
            'dang_thong_bao'      => 'Tạo bài viết',
            'quan_ly_clb'         => 'Quản lý CLB',
            'quan_ly_thanh_vien'  => 'Quản lý thành viên',
            'tao_su_kien'         => 'Tạo sự kiện',
            'xem_bao_cao'         => 'Xem báo cáo',
            'manage_club'         => 'Quản lý CLB',
            'manage_members'      => 'Quản lý thành viên',
            'create_event'        => 'Tạo sự kiện',
            'post_announcement'   => 'Tạo bài viết',
            'evaluate_member'     => 'Đánh giá thành viên',
            'manage_department'   => 'Quản lý phòng ban',
            'manage_documents'    => 'Quản lý tài liệu',
            'view_reports'        => 'Xem báo cáo',
        ];

        $positionLabels = [
            'leader' => 'Trưởng CLB',
            'vice_president' => 'Phó CLB',
            'treasurer' => 'Thủ quỹ',
            'member' => 'Thành viên',
            'owner' => 'Chủ nhiệm',
        ];

        return view('student.club-management.member-show', [
            'user' => $user,
            'club' => $club,
            'clubMember' => $clubMember,
            'permissionNames' => $permissionNames,
            'allPermissions' => $allPermissions,
            'userPosition' => $position,
            'clubId' => $clubId,
            'permLabels' => $permLabels,
            'positionLabels' => $positionLabels,
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
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
            'position' => 'nullable|in:member,treasurer,vice_president',
        ]);

        $clubMember = ClubMember::with('user')
            ->where('club_id', $clubId)
            ->where('id', $memberId)
            ->firstOrFail();

        if (!$clubMember->user || $clubMember->user_id === $user->id) {
            return redirect()->back()->with('error', 'Không hợp lệ.');
        }

        // Không cho phép thay đổi position của leader hoặc owner
        if (in_array($clubMember->position, ['leader', 'owner'])) {
            return redirect()->back()->with('error', 'Không thể thay đổi vai trò của Trưởng CLB hoặc Chủ nhiệm.');
        }

        $requestedPosition = $request->input('position');
        $permissionNames = $request->input('permissions', []);
        
        // KIỂM TRA: Nếu position mới là lãnh đạo (leader, vice_president, treasurer), 
        // kiểm tra xem user đã có vai trò lãnh đạo ở CLB khác chưa
        if (in_array($requestedPosition, ['leader', 'vice_president', 'treasurer'])) {
            $existingLeaderOfficer = ClubMember::where('user_id', $clubMember->user_id)
                ->where('club_id', '!=', $clubId)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'vice_president', 'treasurer'])
                ->whereHas('club', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->first();
                
            if ($existingLeaderOfficer) {
                $existingClub = Club::find($existingLeaderOfficer->club_id);
                $positionNames = [
                    'leader' => 'Trưởng CLB',
                    'vice_president' => 'Phó CLB',
                    'treasurer' => 'Thủ quỹ'
                ];
                $existingPositionName = $positionNames[$existingLeaderOfficer->position] ?? $existingLeaderOfficer->position;
                return redirect()->back()->with('error', "Thành viên này đã là {$existingPositionName} ở CLB '{$existingClub->name}'. Một người chỉ được làm Trưởng/Phó/Thủ quỹ ở 1 CLB.");
            }
        }
        
        // Nếu có position từ form, tự động gán permissions theo position
        if ($requestedPosition) {
            switch ($requestedPosition) {
                case 'leader':
                    // Trưởng CLB: luôn có tất cả quyền, bất kể người dùng chọn gì
                    $permissionNames = ['quan_ly_clb', 'quan_ly_thanh_vien', 'tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'];
                    break;
                case 'vice_president':
                    // Phó CLB: luôn có 4 quyền, bất kể người dùng chọn gì
                    $permissionNames = ['quan_ly_thanh_vien', 'tao_su_kien', 'dang_thong_bao', 'xem_bao_cao'];
                    break;
                case 'treasurer':
                    // Thủ quỹ: luôn có 2 quyền (quan_ly_quy + xem_bao_cao), bất kể người dùng chọn gì
                    $permissionNames = ['quan_ly_quy', 'xem_bao_cao'];
                    break;
                case 'member':
                default:
                    // Thành viên: chỉ có xem_bao_cao, nhưng nếu người dùng đã chọn quyền thì giữ lại
                    if (empty($permissionNames)) {
                        $permissionNames = ['xem_bao_cao'];
                    }
                    break;
            }
        }
        
        // Nếu không có quyền nào được chọn và không có position, tự động gán quyền "xem báo cáo"
        if (empty($permissionNames)) {
            $permissionNames = ['xem_bao_cao'];
        }
        
        $permissionIds = Permission::whereIn('name', (array) $permissionNames)->pluck('id');

        try {
            $requestedPosition = $request->input('position');
            DB::transaction(function () use ($clubMember, $clubId, $permissionIds, $request, $requestedPosition, $user) {
                // Cập nhật permissions
                DB::table('user_permissions_club')
                    ->where('user_id', $clubMember->user_id)
                    ->where('club_id', $clubId)
                    ->delete();
                    
                // Đảm bảo luôn có ít nhất quyền "xem báo cáo"
                $xemBaoCaoId = Permission::where('name', 'xem_bao_cao')->first();
                if ($xemBaoCaoId) {
                    $permissionIdsArray = $permissionIds->toArray();
                    if (!in_array($xemBaoCaoId->id, $permissionIdsArray)) {
                        $permissionIdsArray[] = $xemBaoCaoId->id;
                    }
                    $permissionIds = collect($permissionIdsArray);
                }
                
                foreach ($permissionIds as $pid) {
                    DB::table('user_permissions_club')->insert([
                        'user_id' => $clubMember->user_id,
                        'club_id'  => $clubId,
                        'permission_id' => $pid,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Query lại từ database để đảm bảo tính toán position chính xác
                $permissionCount = DB::table('user_permissions_club')
                    ->where('user_id', $clubMember->user_id)
                    ->where('club_id', $clubId)
                    ->count();
                
                $permissionNames = DB::table('user_permissions_club')
                    ->where('user_id', $clubMember->user_id)
                    ->where('club_id', $clubId)
                    ->join('permissions', 'user_permissions_club.permission_id', '=', 'permissions.id')
                    ->pluck('permissions.name')
                    ->toArray();
                
                $hasOtherPermissions = !empty(array_diff($permissionNames, ['xem_bao_cao']));
                $hasQuanLyQuy = in_array('quan_ly_quy', $permissionNames);
                
                $calculatedPosition = 'member'; // Mặc định
                
                // Xác định vai trò mới dựa trên số quyền và loại quyền
                if ($permissionCount >= 5) {
                    // Có đủ 5 quyền -> Leader (Trưởng CLB)
                    $calculatedPosition = 'leader';
                } elseif ($permissionCount === 4 && $hasOtherPermissions) {
                    // Có 4 quyền và có quyền khác ngoài xem_bao_cao -> Vice President (Phó CLB)
                    $calculatedPosition = 'vice_president';
                } elseif ($hasQuanLyQuy && $permissionCount === 2) {
                    // Có quyền quan_ly_quy và chỉ có 2 quyền (quan_ly_quy + xem_bao_cao) -> Treasurer (Thủ quỹ)
                    $calculatedPosition = 'treasurer';
                } else {
                    // Chỉ có xem_bao_cao hoặc không đủ điều kiện -> Member
                    $calculatedPosition = 'member';
                }
                
                // Kiểm tra giới hạn số lượng vice_president (chỉ 1)
                if ($calculatedPosition === 'vice_president') {
                    $vicePresidentCount = ClubMember::where('club_id', $clubId)
                        ->whereIn('status', ['approved', 'active'])
                        ->where('position', 'vice_president')
                        ->where('id', '!=', $clubMember->id)
                        ->count();
                    
                    if ($vicePresidentCount >= 1) {
                        // Nếu đã có vice president, chuyển về member
                        $calculatedPosition = 'member';
                    }
                }
                
                // Kiểm tra giới hạn số lượng treasurer (chỉ 1) - cho calculatedPosition
                if ($calculatedPosition === 'treasurer') {
                    $treasurerCount = ClubMember::where('club_id', $clubId)
                ->whereIn('status', ['approved', 'active'])
                        ->where('position', 'treasurer')
                        ->where('id', '!=', $clubMember->id)
                        ->count();
                    
                    if ($treasurerCount >= 1) {
                        // Nếu đã có treasurer, chuyển về member
                        $calculatedPosition = 'member';
                    }
                }
                
                // Ưu tiên position từ form nếu có, nếu không thì dùng position được tính toán từ quyền
                // Nếu position từ form là treasurer, luôn ưu tiên position từ form (bỏ qua calculatedPosition)
                if ($requestedPosition === 'treasurer') {
                    $finalPosition = 'treasurer';
                } else {
                    $finalPosition = $requestedPosition ?: $calculatedPosition;
                }
                
                // Đảm bảo finalPosition là string hợp lệ
                $finalPosition = (string) $finalPosition;
                
                // Validate position hợp lệ
                $validPositions = ['member', 'treasurer', 'vice_president', 'leader'];
                if (!in_array($finalPosition, $validPositions)) {
                    $finalPosition = 'member'; // Fallback về member nếu không hợp lệ
                }
                
                // KIỂM TRA: Nếu finalPosition là lãnh đạo (leader, vice_president, treasurer),
                // kiểm tra xem user đã có vai trò lãnh đạo ở CLB khác chưa
                if (in_array($finalPosition, ['leader', 'vice_president', 'treasurer'])) {
                    $existingLeaderOfficer = ClubMember::where('user_id', $clubMember->user_id)
                        ->where('club_id', '!=', $clubId)
                        ->whereIn('status', ['approved', 'active'])
                        ->whereIn('position', ['leader', 'vice_president', 'treasurer'])
                        ->whereHas('club', function($query) {
                            $query->whereNull('deleted_at');
                        })
                        ->first();
                        
                    if ($existingLeaderOfficer) {
                        $existingClub = Club::find($existingLeaderOfficer->club_id);
                        $positionNames = [
                            'leader' => 'Trưởng CLB',
                            'vice_president' => 'Phó CLB',
                            'treasurer' => 'Thủ quỹ'
                        ];
                        $existingPositionName = $positionNames[$existingLeaderOfficer->position] ?? $existingLeaderOfficer->position;
                        throw new \Exception("Thành viên này đã là {$existingPositionName} ở CLB '{$existingClub->name}'. Một người chỉ được làm Trưởng/Phó/Thủ quỹ ở 1 CLB.");
                    }
                }
                
                // Kiểm tra giới hạn cho treasurer (chỉ 1 thủ quỹ mỗi CLB)
                // Chỉ kiểm tra nếu finalPosition là treasurer (sau khi đã xác định)
                if ($finalPosition === 'treasurer') {
                    $existingTreasurer = ClubMember::where('club_id', $clubId)
                        ->whereIn('status', ['approved', 'active'])
                        ->where('position', 'treasurer')
                        ->where('id', '!=', $clubMember->id)
                        ->first();
                    
                    if ($existingTreasurer) {
                        // Nếu đã có treasurer, tự động chuyển thủ quỹ cũ về member
                        // Sử dụng DB::update() với raw SQL để đảm bảo giá trị ENUM được quote đúng cách
                        $sql = "UPDATE club_members SET position = ?, updated_at = ? WHERE id = ?";
                        DB::update($sql, ['member', now(), $existingTreasurer->id]);
                        
                        // Xóa quyền của thủ quỹ cũ, chỉ giữ xem_bao_cao
                        DB::table('user_permissions_club')
                            ->where('user_id', $existingTreasurer->user_id)
                            ->where('club_id', $clubId)
                            ->delete();
                        
                        $xemBaoCaoPerm = Permission::where('name', 'xem_bao_cao')->first();
                        if ($xemBaoCaoPerm) {
                            DB::table('user_permissions_club')->insert([
                                'user_id' => $existingTreasurer->user_id,
                                'club_id' => $clubId,
                                'permission_id' => $xemBaoCaoPerm->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
                
                // Kiểm tra giới hạn cho vice_president từ form
                if ($requestedPosition === 'vice_president') {
                    $vicePresidentCount = ClubMember::where('club_id', $clubId)
                        ->whereIn('status', ['approved', 'active'])
                        ->where('position', 'vice_president')
                        ->where('id', '!=', $clubMember->id)
                        ->count();
                    
                    if ($vicePresidentCount >= 2) {
                        throw new \Exception("CLB này đã có đủ 2 phó CLB. Vui lòng chuyển 1 phó CLB về thành viên trước.");
                    }
                }
                
                // Lấy position cũ trước khi cập nhật
                $oldPosition = $clubMember->position;
                
                // Cập nhật position - sử dụng raw SQL với giá trị được quote thủ công
                // Đảm bảo giá trị là string và nằm trong danh sách ENUM hợp lệ
                $validPosition = in_array($finalPosition, ['leader', 'vice_president', 'treasurer', 'member']) 
                    ? (string) $finalPosition 
                    : 'member';
                
                // Sử dụng DB::update() với raw SQL để đảm bảo giá trị ENUM được quote đúng cách
                // MySQL cần giá trị ENUM được quote bằng dấu nháy đơn
                $sql = "UPDATE club_members SET position = ?, updated_at = ? WHERE id = ?";
                DB::update($sql, [$validPosition, now(), $clubMember->id]);
                
                // Refresh model để đảm bảo dữ liệu mới nhất
                $clubMember->refresh();
                
                // Tạo thông báo cho thành viên khi được phân quyền làm phó CLB hoặc thủ quỹ
                if (in_array($validPosition, ['vice_president', 'treasurer']) && $oldPosition !== $validPosition) {
                    try {
                        $club = Club::find($clubId);
                        $positionLabels = [
                            'vice_president' => 'Phó CLB',
                            'treasurer' => 'Thủ quỹ',
                        ];
                        $positionLabel = $positionLabels[$validPosition] ?? $validPosition;
                        
                        // Tạo notification
                        $notification = \App\Models\Notification::create([
                            'sender_id' => $user->id,
                            'type' => 'club_role_change',
                            'title' => "Bạn đã được phân quyền {$positionLabel}",
                            'message' => "Bạn đã được phân quyền làm {$positionLabel} của CLB \"{$club->name}\". Chúc mừng bạn!",
                            'related_id' => $clubId,
                            'related_type' => 'Club',
                        ]);
                        
                        // Tạo notification target cho thành viên được phân quyền
                        \App\Models\NotificationTarget::create([
                            'notification_id' => $notification->id,
                            'target_type' => 'user',
                            'target_id' => $clubMember->user_id,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Lỗi khi tạo thông báo phân quyền: ' . $e->getMessage());
                    }
                }
                
                    // Log để debug
                \Log::info("Updated position for user {$clubMember->user_id} in club {$clubId}: {$oldPosition} -> {$finalPosition} (requested: {$requestedPosition}, calculated: {$calculatedPosition}, permission count: {$permissionCount}, validPosition: {$validPosition})");
            });

            return redirect()->back()->with('success', 'Đã cập nhật thành công.');
        } catch (\Exception $e) {
            \Log::error("Error updating member permissions: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', $e->getMessage());
        }
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

        // Lấy club từ query parameter hoặc tìm CLB mà user có quyền quản lý quỹ
        $request = request();
        $clubId = $request->input('club');
        
        if ($clubId) {
            $club = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$club) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
        } else {
            // Tìm CLB mà user có quyền quản lý quỹ (treasurer hoặc leader)
            $club = null;
            foreach ($user->clubs as $c) {
                $position = $user->getPositionInClub($c->id);
                if (in_array($position, ['treasurer', 'leader'])) {
                    $club = $c;
                    break;
                }
            }
            
            // Nếu không tìm thấy, lấy CLB đầu tiên
            if (!$club) {
        $club = $user->clubs->first();
            }
        }
        
        $clubId = $club->id;
        $position = $user->getPositionInClub($clubId);

        // Leader và Treasurer có quyền tạo yêu cầu
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB và Thủ quỹ mới có quyền tạo yêu cầu cấp kinh phí.');
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

        // Lấy club từ request hoặc tìm CLB mà user có quyền quản lý quỹ
        $clubId = $request->input('club_id') ?: $request->input('club');
        
        if ($clubId) {
            $club = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$club) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
        } else {
            // Tìm CLB mà user có quyền quản lý quỹ (treasurer hoặc leader)
            $club = null;
            foreach ($user->clubs as $c) {
                $pos = $user->getPositionInClub($c->id);
                if (in_array($pos, ['treasurer', 'leader'])) {
                    $club = $c;
                    break;
                }
            }
            
            // Nếu không tìm thấy, lấy CLB đầu tiên
            if (!$club) {
        $club = $user->clubs->first();
            }
        }
        
        $clubId = $club->id;
        $position = $user->getPositionInClub($clubId);

        // Chỉ Leader và Treasurer có quyền tạo yêu cầu cấp kinh phí
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB và Thủ quỹ mới có quyền tạo yêu cầu cấp kinh phí.');
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
            $fundRequest = FundRequest::create($data);
            
            // Tạo thông báo cho admin
            $admins = \App\Models\User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                $notification = \App\Models\Notification::create([
                    'sender_id' => $user->id,
                    'type' => 'fund_request',
                    'title' => 'Yêu cầu cấp kinh phí mới',
                    'message' => "Có yêu cầu cấp kinh phí mới: \"{$fundRequest->title}\" từ CLB " . ($club->name ?? '') . ". Số tiền: " . number_format($fundRequest->requested_amount, 0, ',', '.') . " VNĐ.",
                    'related_id' => $fundRequest->id,
                    'related_type' => 'FundRequest',
                ]);
                
                \App\Models\NotificationTarget::create([
                    'notification_id' => $notification->id,
                    'target_type' => 'user',
                    'target_id' => $admin->id,
                ]);
            }
            
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

        // Lấy club từ query parameter hoặc tìm CLB mà user có quyền quản lý quỹ
        $clubId = $request->input('club');
        
        if ($clubId) {
            $club = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$club) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
        } else {
            // Tìm CLB mà user có quyền quản lý quỹ (treasurer hoặc leader)
            $club = null;
            foreach ($user->clubs as $c) {
                $position = $user->getPositionInClub($c->id);
                if (in_array($position, ['treasurer', 'leader'])) {
                    $club = $c;
                    break;
                }
            }
            
            // Nếu không tìm thấy, lấy CLB đầu tiên
            if (!$club) {
        $club = $user->clubs->first();
            }
        }
        
        $clubId = $club->id;

        $query = FundRequest::with(['event', 'creator', 'approver', 'settler'])
            ->where('club_id', $clubId);

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
     * Show form to edit fund request
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
        $fundRequest = FundRequest::with(['event', 'club', 'creator', 'approver'])
            ->where('id', $id)
            ->where('club_id', $club->id)
            ->firstOrFail();

        // Chỉ cho phép chỉnh sửa yêu cầu đang chờ duyệt hoặc đã bị từ chối
        if (!in_array($fundRequest->status, ['pending', 'rejected'])) {
            return redirect()->route('student.club-management.fund-requests.show', $fundRequest->id)
                ->with('error', 'Chỉ có thể chỉnh sửa yêu cầu đang chờ duyệt hoặc đã bị từ chối!');
        }

        // Kiểm tra quyền: chỉ leader và treasurer mới được chỉnh sửa
        $position = $user->getPositionInClub($club->id);
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->route('student.club-management.fund-requests.show', $fundRequest->id)
                ->with('error', 'Bạn không có quyền chỉnh sửa yêu cầu này.');
        }

        // Lấy các sự kiện của CLB
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

        // Chỉ cho phép chỉnh sửa yêu cầu đang chờ duyệt hoặc đã bị từ chối
        if (!in_array($fundRequest->status, ['pending', 'rejected'])) {
            return redirect()->route('student.club-management.fund-requests.show', $fundRequest->id)
                ->with('error', 'Chỉ có thể chỉnh sửa yêu cầu đang chờ duyệt hoặc đã bị từ chối!');
        }

        // Kiểm tra quyền
        $position = $user->getPositionInClub($club->id);
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->route('student.club-management.fund-requests.show', $fundRequest->id)
                ->with('error', 'Bạn không có quyền chỉnh sửa yêu cầu này.');
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
        if ($event->club_id != $club->id) {
            return redirect()->back()
                ->with('error', 'Sự kiện không thuộc về CLB của bạn.')
                ->withInput();
        }

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'requested_amount' => $request->requested_amount,
            'event_id' => $request->event_id,
            'expense_items' => $request->expense_items ?? null,
        ];

        // Nếu yêu cầu bị từ chối, reset về pending và xóa lý do từ chối
        if ($fundRequest->status === 'rejected') {
            $data['status'] = 'pending';
            $data['rejection_reason'] = null;
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['approved_amount'] = null;
        }

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
            $data['supporting_documents'] = $documents;
        }

        try {
            $wasRejected = $fundRequest->status === 'rejected';
            $fundRequest->update($data);
            
            // Nếu yêu cầu bị từ chối và được sửa lại, tạo thông báo cho admin
            if ($wasRejected && $fundRequest->status === 'pending') {
                $admins = \App\Models\User::where('is_admin', true)->get();
                foreach ($admins as $admin) {
                    $notification = \App\Models\Notification::create([
                        'sender_id' => $user->id,
                        'type' => 'fund_request',
                        'title' => 'Yêu cầu cấp kinh phí được sửa lại',
                        'message' => "Yêu cầu cấp kinh phí \"{$fundRequest->title}\" từ CLB " . ($club->name ?? '') . " đã được sửa lại và gửi để duyệt.",
                        'related_id' => $fundRequest->id,
                        'related_type' => 'FundRequest',
                    ]);
                    
                    \App\Models\NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type' => 'user',
                        'target_id' => $admin->id,
                    ]);
                }
            }
            
            $message = $wasRejected 
                ? 'Yêu cầu cấp kinh phí đã được sửa lại và gửi để duyệt!' 
                : 'Yêu cầu cấp kinh phí đã được cập nhật thành công!';
            
            return redirect()->route('student.club-management.fund-requests.show', $fundRequest->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Resubmit rejected fund request
     */
    public function fundRequestResubmit(Request $request, $id)
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

        // Chỉ cho phép gửi lại yêu cầu đã bị từ chối
        if ($fundRequest->status !== 'rejected') {
            return redirect()->route('student.club-management.fund-requests.show', $fundRequest->id)
                ->with('error', 'Chỉ có thể gửi lại yêu cầu đã bị từ chối!');
        }

        // Kiểm tra quyền
        $position = $user->getPositionInClub($club->id);
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->route('student.club-management.fund-requests.show', $fundRequest->id)
                ->with('error', 'Bạn không có quyền gửi lại yêu cầu này.');
        }

        try {
            $fundRequest->update([
                'status' => 'pending',
                'rejection_reason' => null,
            ]);
            
            // Tạo thông báo cho admin
            $admins = \App\Models\User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                $notification = \App\Models\Notification::create([
                    'sender_id' => $user->id,
                    'type' => 'fund_request',
                    'title' => 'Yêu cầu cấp kinh phí được gửi lại',
                    'message' => "Yêu cầu cấp kinh phí \"{$fundRequest->title}\" từ CLB " . ($club->name ?? '') . " đã được gửi lại để duyệt.",
                    'related_id' => $fundRequest->id,
                    'related_type' => 'FundRequest',
                ]);
                
                \App\Models\NotificationTarget::create([
                    'notification_id' => $notification->id,
                    'target_type' => 'user',
                    'target_id' => $admin->id,
                ]);
            }
            
            return redirect()->route('student.club-management.fund-requests.show', $fundRequest->id)
                ->with('success', 'Yêu cầu cấp kinh phí đã được gửi lại thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
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
        if (!in_array($position, ['leader', 'vice_president'])) {
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
        if (!in_array($position, ['leader', 'vice_president'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền duyệt đơn.');
        }
        $req = JoinReq::with('club')->where('club_id', $clubId)->findOrFail($requestId);
        if ($req->status === 'pending') {
            $req->approve($user->id);
            
            // Load lại club relationship sau khi approve
            $req->load('club');
            
            // Gửi thông báo cho người dùng về việc đơn được duyệt
            try {
                $notificationData = [
                    'sender_id' => $user->id,
                    'title' => 'Đơn tham gia CLB đã được duyệt',
                    'message' => "Đơn tham gia CLB \"{$req->club->name}\" của bạn đã được duyệt bởi ban quản trị CLB. Chúc mừng bạn đã trở thành thành viên của CLB!",
                ];
                
                // Thêm các trường mới nếu cột tồn tại
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'type')) {
                    $notificationData['type'] = 'club';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_id')) {
                    $notificationData['related_id'] = $req->id;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_type')) {
                    $notificationData['related_type'] = 'ClubJoinRequest';
                }
                
                $notification = \App\Models\Notification::create($notificationData);
                
                // Tạo target cho notification
                if ($notification) {
                    \App\Models\NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type' => 'user',
                        'target_id' => $req->user_id,
                    ]);
                }
                
                // Tạo NotificationRead nếu có notification
                if ($notification) {
                    \App\Models\NotificationRead::create([
                        'notification_id' => $notification->id,
                        'user_id' => $req->user_id,
                        'is_read' => false,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error creating notification: ' . $e->getMessage());
                // Tiếp tục xử lý dù có lỗi thông báo
                $notification = null;
            }
        }
        return redirect()->back()->with('success', 'Đã duyệt đơn tham gia.');
    }

    public function rejectClubJoinRequest($clubId, $requestId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        $clubId = (int) $clubId;
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền từ chối đơn.');
        }
        $req = JoinReq::with('club')->where('club_id', $clubId)->findOrFail($requestId);
        if ($req->status === 'pending') {
            $req->reject($user->id);
            
            // Load lại club relationship sau khi reject
            $req->load('club');
            
            // Gửi thông báo cho người dùng về việc đơn bị từ chối
            try {
                $notificationData = [
                    'sender_id' => $user->id,
                    'title' => 'Đơn tham gia CLB đã bị từ chối',
                    'message' => "Rất tiếc, đơn tham gia CLB \"{$req->club->name}\" của bạn đã bị từ chối bởi ban quản trị CLB. Vui lòng liên hệ với ban quản trị để biết thêm chi tiết.",
                ];
                
                // Thêm các trường mới nếu cột tồn tại
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'type')) {
                    $notificationData['type'] = 'club';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_id')) {
                    $notificationData['related_id'] = $req->id;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_type')) {
                    $notificationData['related_type'] = 'ClubJoinRequest';
                }
                
                $notification = \App\Models\Notification::create($notificationData);
                
                // Tạo target cho notification
                if ($notification) {
                    \App\Models\NotificationTarget::create([
                        'notification_id' => $notification->id,
                        'target_type' => 'user',
                        'target_id' => $req->user_id,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error creating notification: ' . $e->getMessage());
                // Tiếp tục xử lý dù có lỗi thông báo
                $notification = null;
            }
            
            // Tạo NotificationRead nếu có notification
            if ($notification) {
                \App\Models\NotificationRead::create([
                    'notification_id' => $notification->id,
                    'user_id' => $req->user_id,
                    'is_read' => false,
                ]);
            }
        }
        return redirect()->back()->with('success', 'Đã từ chối đơn tham gia.');
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

        // Lấy club từ query parameter hoặc lấy CLB đầu tiên nếu không có
        $clubId = $request->input('club');
        
        if ($clubId) {
            // Kiểm tra xem user có phải là thành viên của CLB này không
            $club = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$club) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
        } else {
            // Nếu không có query parameter, lấy CLB đầu tiên
            if ($user->clubs->isEmpty()) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn chưa tham gia CLB nào.');
            }
            $club = $user->clubs->first();
        $clubId = $club->id;
        }

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

        // Fund stats
        $fund = Fund::where('club_id', $clubId)->first();
        $fundId = $fund?->id ?? null;
        $totalIncome = 0;
        $totalExpense = 0;
        $balance = 0;
        $expenseByCategory = [];

        if ($fundId) {
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
                // breakdown by category (if field exists)
                $expenseByCategory = FundTransaction::where('fund_id', $fundId)
                    ->where('type', 'expense')
                    ->where('status', 'approved')
                    ->select('category', DB::raw('SUM(amount) as total'))
                    ->groupBy('category')
                    ->pluck('total', 'category')
                    ->toArray();
                
                // Thống kê quỹ theo tuần (7 ngày gần nhất)
                $fundStatsByWeek = [
                    'labels' => [],
                    'income' => [],
                    'expense' => []
                ];
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $startOfDay = $date->copy()->startOfDay();
                    $endOfDay = $date->copy()->endOfDay();
                    
                    $fundStatsByWeek['labels'][] = $date->format('d/m');
                    $fundStatsByWeek['income'][] = (int) FundTransaction::where('fund_id', $fundId)
                        ->where('type', 'income')
                        ->where('status', 'approved')
                        ->where(function($query) use ($startOfDay, $endOfDay) {
                            $query->whereBetween('transaction_date', [$startOfDay, $endOfDay])
                                  ->orWhere(function($q) use ($startOfDay, $endOfDay) {
                                      $q->whereNull('transaction_date')
                                        ->whereBetween('created_at', [$startOfDay, $endOfDay]);
                                  });
                        })
                        ->sum('amount');
                    $fundStatsByWeek['expense'][] = (int) FundTransaction::where('fund_id', $fundId)
                        ->where('type', 'expense')
                        ->where('status', 'approved')
                        ->where(function($query) use ($startOfDay, $endOfDay) {
                            $query->whereBetween('transaction_date', [$startOfDay, $endOfDay])
                                  ->orWhere(function($q) use ($startOfDay, $endOfDay) {
                                      $q->whereNull('transaction_date')
                                        ->whereBetween('created_at', [$startOfDay, $endOfDay]);
                                  });
                        })
                        ->sum('amount');
                }
                
                // Thống kê quỹ theo tháng (12 tháng gần nhất)
                $fundStatsByMonth = [
                    'labels' => [],
                    'income' => [],
                    'expense' => []
                ];
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $startOfMonth = $date->copy()->startOfMonth();
                    $endOfMonth = $date->copy()->endOfMonth();
                    
                    $fundStatsByMonth['labels'][] = $date->format('m/Y');
                    $fundStatsByMonth['income'][] = (int) FundTransaction::where('fund_id', $fundId)
                        ->where('type', 'income')
                        ->where('status', 'approved')
                        ->where(function($query) use ($startOfMonth, $endOfMonth) {
                            $query->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                                  ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                                      $q->whereNull('transaction_date')
                                        ->whereYear('created_at', $startOfMonth->year)
                                        ->whereMonth('created_at', $startOfMonth->month);
                                  });
                        })
                        ->sum('amount');
                    $fundStatsByMonth['expense'][] = (int) FundTransaction::where('fund_id', $fundId)
                        ->where('type', 'expense')
                        ->where('status', 'approved')
                        ->where(function($query) use ($startOfMonth, $endOfMonth) {
                            $query->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                                  ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                                      $q->whereNull('transaction_date')
                                        ->whereYear('created_at', $startOfMonth->year)
                                        ->whereMonth('created_at', $startOfMonth->month);
                                  });
                        })
                        ->sum('amount');
                }
                
                // Thống kê quỹ theo năm (5 năm gần nhất)
                $fundStatsByYear = [
                    'labels' => [],
                    'income' => [],
                    'expense' => []
                ];
                for ($i = 4; $i >= 0; $i--) {
                    $date = now()->subYears($i);
                    $startOfYear = $date->copy()->startOfYear();
                    $endOfYear = $date->copy()->endOfYear();
                    
                    $fundStatsByYear['labels'][] = $date->format('Y');
                    $fundStatsByYear['income'][] = (int) FundTransaction::where('fund_id', $fundId)
                        ->where('type', 'income')
                        ->where('status', 'approved')
                        ->where(function($query) use ($startOfYear, $endOfYear) {
                            $query->whereBetween('transaction_date', [$startOfYear, $endOfYear])
                                  ->orWhere(function($q) use ($startOfYear) {
                                      $q->whereNull('transaction_date')
                                        ->whereYear('created_at', $startOfYear->year);
                                  });
                        })
                        ->sum('amount');
                    $fundStatsByYear['expense'][] = (int) FundTransaction::where('fund_id', $fundId)
                        ->where('type', 'expense')
                        ->where('status', 'approved')
                        ->where(function($query) use ($startOfYear, $endOfYear) {
                            $query->whereBetween('transaction_date', [$startOfYear, $endOfYear])
                                  ->orWhere(function($q) use ($startOfYear) {
                                      $q->whereNull('transaction_date')
                                        ->whereYear('created_at', $startOfYear->year);
                                  });
                        })
                        ->sum('amount');
                }
        } else {
            $fundStatsByWeek = ['labels' => [], 'income' => [], 'expense' => []];
            $fundStatsByMonth = ['labels' => [], 'income' => [], 'expense' => []];
            $fundStatsByYear = ['labels' => [], 'income' => [], 'expense' => []];
        }

        // Member structure (leader/vice_president/treasurer/member) simple distribution
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

        // Resources stats
        $totalResources = \App\Models\ClubResource::where('club_id', $clubId)->count();
        $totalFiles = \App\Models\ClubResourceFile::whereHas('clubResource', function($q) use ($clubId) {
            $q->where('club_id', $clubId);
        })->count();

        $stats = [
            'totalMembers' => $totalMembers,
            'newMembersThisMonth' => $newMembersThisMonth,
            'totalEvents' => $totalEvents,
            'upcomingEvents' => $upcomingEvents,
            'fund' => [
                'totalIncome' => (int) $totalIncome,
                'totalExpense' => (int) $totalExpense,
                'balance' => (int) $balance,
                'expenseByCategory' => $expenseByCategory,
                'statsByWeek' => $fundStatsByWeek,
                'statsByMonth' => $fundStatsByMonth,
                'statsByYear' => $fundStatsByYear,
            ],
            'resources' => [
                'total' => $totalResources,
                'files' => $totalFiles,
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

        // Kiểm tra xem user có phải là leader, vice_president hoặc treasurer không
        $position = $user->getPositionInClub($clubId);
        $isLeaderOrOfficer = in_array($position, ['leader', 'vice_president', 'treasurer']);
        
        // Kiểm tra quyền xem báo cáo (có thể xem nếu có quyền xem_bao_cao hoặc là leader/vice_president/treasurer)
        $canViewReports = $user->hasPermission('xem_bao_cao', $clubId) || $isLeaderOrOfficer;

        // Lấy danh sách giao dịch thu chi (chỉ các giao dịch đã được duyệt)
        $transactions = null;
        if ($fundId && $canViewReports) {
            $transactions = FundTransaction::where('fund_id', $fundId)
                ->where('status', 'approved')
                ->with(['creator', 'approver'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('student.club-management.reports', compact('user', 'club', 'stats', 'isLeaderOrOfficer', 'canViewReports', 'transactions'));
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

        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        
        // Lấy club từ query parameter hoặc tìm CLB mà user có quyền quản lý quỹ
        $clubId = $request->input('club');
        
        if ($clubId) {
            $club = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$club) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
        } else {
            // Tìm CLB mà user có quyền quản lý quỹ (treasurer hoặc leader)
            $club = null;
            foreach ($user->clubs as $c) {
                $position = $user->getPositionInClub($c->id);
                if (in_array($position, ['treasurer', 'leader'])) {
                    $club = $c;
                break;
                }
            }
            
            // Nếu không tìm thấy, lấy CLB đầu tiên
            if (!$club) {
                $club = $user->clubs->first();
            }
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
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        
        // Lấy club từ query parameter hoặc tìm CLB mà user có quyền quản lý quỹ
        $clubId = $request->input('club');
        
        if ($clubId) {
            $club = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$club) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
        } else {
            // Tìm CLB mà user có quyền quản lý quỹ (treasurer hoặc leader)
            $club = null;
            foreach ($user->clubs as $c) {
                $position = $user->getPositionInClub($c->id);
                if (in_array($position, ['treasurer', 'leader'])) {
                    $club = $c;
                    break;
                }
            }
            
            // Nếu không tìm thấy, lấy CLB đầu tiên
            if (!$club) {
        $club = $user->clubs->first();
            }
        }
        
        // Chỉ Leader và Treasurer có quyền tạo giao dịch quỹ
        $position = $user->getPositionInClub($club->id);
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->route('student.club-management.fund-transactions', ['club' => $club->id])
                ->with('error', 'Chỉ Trưởng CLB và Thủ quỹ mới có quyền tạo giao dịch quỹ.');
        }
        return view('student.club-management.fund-transaction-create', [
            'user' => $user,
            'club' => $club,
        ]);
    }

    /**
     * Store transaction (leader và treasurer auto-approved)
     */
    public function fundTransactionStore(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        
        // Lấy club từ request hoặc tìm CLB mà user có quyền quản lý quỹ
        $clubId = $request->input('club_id') ?: $request->input('club');
        
        if ($clubId) {
            $club = $user->clubs()->where('clubs.id', $clubId)->first();
            if (!$club) {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn không phải là thành viên của CLB này.');
            }
        } else {
            // Tìm CLB mà user có quyền quản lý quỹ (treasurer hoặc leader)
            $club = null;
            foreach ($user->clubs as $c) {
                $position = $user->getPositionInClub($c->id);
                if (in_array($position, ['treasurer', 'leader'])) {
                    $club = $c;
                    break;
                }
            }
            
            // Nếu không tìm thấy, lấy CLB đầu tiên
            if (!$club) {
        $club = $user->clubs->first();
            }
        }
        
        $fund = Fund::firstOrCreate(['club_id' => $club->id], [
            'name' => 'Quỹ ' . $club->name,
            'current_amount' => 0,
        ]);

        $position = $user->getPositionInClub($club->id);
        // Chỉ Leader và Treasurer có quyền tạo giao dịch quỹ
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->route('student.club-management.fund-transactions', ['club' => $club->id])
                ->with('error', 'Chỉ Trưởng CLB và Thủ quỹ mới có quyền tạo giao dịch quỹ.');
        }

        $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:1',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:5120',
        ]);

        // Leader và Treasurer tự động được duyệt
        $status = 'approved';

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

        // Tạo thông báo cho admin và người quản lý quỹ (nếu chưa được duyệt tự động)
        if ($status === 'pending') {
            try {
                $admins = \App\Models\User::where('is_admin', true)->get();
                $typeText = $tx->type === 'income' ? 'thu' : 'chi';
                
                foreach ($admins as $admin) {
                    $notificationData = [
                        'sender_id' => $user->id,
                        'title' => "Giao dịch quỹ mới ({$typeText})",
                        'message' => "Có giao dịch quỹ mới: \"{$tx->title}\" - " . number_format($tx->amount, 0, ',', '.') . " VNĐ từ CLB {$club->name}. Đang chờ duyệt.",
                    ];
                    
                    // Thêm các trường mới nếu cột tồn tại
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'type')) {
                        $notificationData['type'] = 'fund_transaction';
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_id')) {
                        $notificationData['related_id'] = $tx->id;
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'related_type')) {
                        $notificationData['related_type'] = 'FundTransaction';
                    }
                    
                    $notification = \App\Models\Notification::create($notificationData);
                    
                    if ($notification) {
                        \App\Models\NotificationTarget::create([
                            'notification_id' => $notification->id,
                            'target_type' => 'user',
                            'target_id' => $admin->id,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error creating notification for new fund transaction: ' . $e->getMessage());
            }
        }

        // If approved and type expense, could validate balance; for simplicity we rely on admin reconciliation
        return redirect()->route('student.club-management.fund-transactions')
            ->with('success', $status === 'approved' ? 'Đã tạo giao dịch và duyệt.' : 'Đã tạo giao dịch, chờ duyệt.');
    }

    /**
     * Approve transaction (leader và treasurer)
     */
    public function approveFundTransaction($transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        
        $tx = FundTransaction::findOrFail($transactionId);
        $fund = Fund::find($tx->fund_id);
        if (!$fund) {
            return redirect()->back()->with('error', 'Không tìm thấy quỹ của giao dịch này.');
        }
        
        $club = Club::find($fund->club_id);
        if (!$club || !$user->clubs->contains('id', $club->id)) {
            return redirect()->back()->with('error', 'Bạn không phải là thành viên của CLB này.');
        }
        
        $position = $user->getPositionInClub($club->id);
        // Leader và Treasurer có quyền duyệt giao dịch
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB và Thủ quỹ mới được duyệt giao dịch.');
        }
        if ($tx->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch không ở trạng thái chờ duyệt.');
        }
        
        // Duyệt giao dịch
        $tx->status = 'approved';
        $tx->approved_by = $user->id;
        $tx->approved_at = now();
        $tx->save();
        
        // Cộng tiền vào quỹ nếu là thu nhập (nộp quỹ)
        if ($tx->type === 'income') {
            $fund->updateCurrentAmount();
        }
        
        // Tạo thông báo cho người tạo giao dịch
        try {
            if ($tx->type === 'income') {
                // Thông báo cho người nộp quỹ khi được duyệt
                $notification = Notification::create([
                    'title' => 'Yêu cầu thanh toán quỹ đã được duyệt',
                    'message' => "Yêu cầu nộp quỹ của bạn với số tiền " . number_format($tx->amount, 0, ',', '.') . " VNĐ đã được duyệt bởi " . ($position === 'treasurer' ? 'Thủ quỹ' : 'Trưởng CLB') . " của CLB {$club->name}. Số tiền đã được cộng vào quỹ CLB.",
                    'type' => 'success',
                    'sender_id' => $user->id,
                ]);
            } else {
                // Thông báo cho giao dịch chi
                $notification = Notification::create([
                    'title' => 'Giao dịch quỹ đã được duyệt',
                    'message' => "Giao dịch quỹ \"{$tx->title}\" của bạn đã được duyệt bởi " . ($position === 'treasurer' ? 'Thủ quỹ' : 'Trưởng CLB') . " {$club->name}. Số tiền: " . number_format($tx->amount, 0, ',', '.') . " VNĐ.",
                    'type' => 'success',
                    'sender_id' => $user->id,
                ]);
            }
            
            // Gán notification cho người tạo giao dịch
            NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_id' => $tx->created_by,
                'target_type' => 'user',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating notification for approved fund transaction: ' . $e->getMessage());
        }
        
        return redirect()->back()->with('success', 'Đã duyệt giao dịch.');
    }

    /**
     * Reject transaction (leader và treasurer)
     */
    public function rejectFundTransaction(Request $request, $transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        if ($user->clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn chưa tham gia CLB nào.');
        }
        
        $tx = FundTransaction::findOrFail($transactionId);
        $fund = Fund::find($tx->fund_id);
        if (!$fund) {
            return redirect()->back()->with('error', 'Không tìm thấy quỹ của giao dịch này.');
        }
        
        $club = Club::find($fund->club_id);
        if (!$club || !$user->clubs->contains('id', $club->id)) {
            return redirect()->back()->with('error', 'Bạn không phải là thành viên của CLB này.');
        }
        
        $position = $user->getPositionInClub($club->id);
        // Leader và Treasurer có quyền từ chối giao dịch
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB và Thủ quỹ mới được từ chối giao dịch.');
        }
        if ($tx->status !== 'pending') {
            return redirect()->back()->with('error', 'Giao dịch không ở trạng thái chờ duyệt.');
        }
        $tx->status = 'rejected';
        $tx->rejection_reason = $request->input('rejection_reason', 'Bị từ chối bởi Trưởng CLB');
        $tx->save();
        
        // Tạo thông báo cho người tạo giao dịch
        try {
            $rejectionReason = $request->input('rejection_reason', 'Bị từ chối bởi Trưởng CLB');
            if ($tx->type === 'income') {
                $notification = Notification::create([
                    'title' => 'Yêu cầu thanh toán quỹ đã bị từ chối',
                    'message' => "Yêu cầu nộp quỹ của bạn với số tiền " . number_format($tx->amount, 0, ',', '.') . " VNĐ đã bị từ chối bởi " . ($position === 'treasurer' ? 'Thủ quỹ' : 'Trưởng CLB') . " của CLB {$club->name}. Lý do: {$rejectionReason}",
                    'type' => 'error',
                    'sender_id' => $user->id,
                ]);
            } else {
                $notification = Notification::create([
                    'title' => 'Giao dịch quỹ đã bị từ chối',
                    'message' => "Giao dịch quỹ \"{$tx->title}\" của bạn đã bị từ chối bởi " . ($position === 'treasurer' ? 'Thủ quỹ' : 'Trưởng CLB') . " {$club->name}. Lý do: {$rejectionReason}",
                    'type' => 'error',
                    'sender_id' => $user->id,
                ]);
            }
            
            NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_id' => $tx->created_by,
                'target_type' => 'user',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating notification for rejected fund transaction: ' . $e->getMessage());
        }
        
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
        
        $tx = FundTransaction::findOrFail($transactionId);
        $fund = Fund::find($tx->fund_id);
        if (!$fund) {
            return redirect()->route('student.club-management.fund-transactions')
                ->with('error', 'Không tìm thấy quỹ của giao dịch này.');
        }
        
        $club = Club::find($fund->club_id);
        if (!$club || !$user->clubs->contains('id', $club->id)) {
            return redirect()->route('student.club-management.fund-transactions')
                ->with('error', 'Bạn không phải là thành viên của CLB này.');
        }
        
        return view('student.club-management.fund-transaction-show', [
            'user' => $user,
            'club' => $club,
            'tx' => $tx,
        ]);
    }

    /**
     * Show fund deposit form (trang nộp quỹ)
     */
    public function showFundDeposit(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Kiểm tra club_id
        $clubId = $request->input('club');
        if (!$clubId) {
            return redirect()->route('student.clubs.index')
                ->with('error', 'Vui lòng chọn CLB để nộp quỹ.');
        }

        // Kiểm tra user là thành viên của CLB
        $club = $user->clubs()->where('clubs.id', $clubId)->first();
        if (!$club) {
            return redirect()->route('student.clubs.index')
                ->with('error', 'Bạn không phải là thành viên của CLB này.');
        }

        // Lấy QR code primary của CLB
        $paymentQr = ClubPaymentQr::where('club_id', $club->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->first();

        // Nếu chưa có QR primary, lấy QR đầu tiên đang active
        if (!$paymentQr) {
            $paymentQr = ClubPaymentQr::where('club_id', $club->id)
                ->where('is_active', true)
                ->first();
        }

        // Lấy số tiền từ request (nếu có)
        $amount = $request->input('amount', 50000); // Mặc định 50,000 VNĐ

        // Generate QR code URL nếu có payment QR
        $qrCodeUrl = null;
        if ($paymentQr) {
            $description = "NOP QUY " . strtoupper(str_replace(' ', '', $user->name ?? $user->student_id)) . " " . $club->id;
            $qrCodeUrl = $paymentQr->generateVietQR($amount, $description);
        }

        // Lấy quỹ của CLB
        $fund = Fund::where('club_id', $club->id)->first();
        
        // Lấy danh sách bill nộp quỹ của user cho CLB này
        $status = $request->input('status', 'all'); // all, pending, approved, rejected
        
        $billsQuery = FundTransaction::where('type', 'income')
            ->where('created_by', $user->id)
            ->with(['fund.club', 'approver']);
        
        // Lọc theo fund_id nếu có fund
        if ($fund) {
            $billsQuery->where('fund_id', $fund->id);
        }
        
        // Lọc theo status
        if ($status !== 'all') {
            $billsQuery->where('status', $status);
        }
        
        // Sắp xếp theo mới nhất
        $bills = $billsQuery->orderBy('created_at', 'desc')->paginate(10);

        // Thống kê
        $billStats = [
            'total' => FundTransaction::where('type', 'income')
                ->where('created_by', $user->id)
                ->when($fund, function($q) use ($fund) {
                    return $q->where('fund_id', $fund->id);
                })
                ->count(),
            'pending' => FundTransaction::where('type', 'income')
                ->where('created_by', $user->id)
                ->where('status', 'pending')
                ->when($fund, function($q) use ($fund) {
                    return $q->where('fund_id', $fund->id);
                })
                ->count(),
            'approved' => FundTransaction::where('type', 'income')
                ->where('created_by', $user->id)
                ->where('status', 'approved')
                ->when($fund, function($q) use ($fund) {
                    return $q->where('fund_id', $fund->id);
                })
                ->count(),
            'rejected' => FundTransaction::where('type', 'income')
                ->where('created_by', $user->id)
                ->where('status', 'rejected')
                ->when($fund, function($q) use ($fund) {
                    return $q->where('fund_id', $fund->id);
                })
                ->count(),
        ];

        return view('student.club-management.fund-deposit', [
            'user' => $user,
            'club' => $club,
            'fund' => $fund,
            'paymentQr' => $paymentQr,
            'qrCodeUrl' => $qrCodeUrl,
            'amount' => $amount,
            'paymentMethods' => FundTransaction::$paymentMethods,
            'bills' => $bills,
            'billStatus' => $status,
            'billStats' => $billStats,
        ]);
    }

    /**
     * Submit fund deposit (xử lý nộp quỹ)
     */
    public function submitFundDeposit(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        // Validate
        $request->validate([
            'club_id' => 'required|exists:clubs,id',
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|string|in:VietQR,Momo,ZaloPay,BankTransfer,Cash',
            'transaction_code' => 'nullable|string|max:255',
            'payer_name' => 'nullable|string|max:255',
            'payer_phone' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:1000',
        ]);

        // Kiểm tra user là thành viên của CLB
        $club = $user->clubs()->where('clubs.id', $request->club_id)->first();
        if (!$club) {
            return redirect()->route('student.clubs.index')
                ->with('error', 'Bạn không phải là thành viên của CLB này.');
        }

        // Lấy hoặc tạo fund của CLB
        $fund = Fund::firstOrCreate(
            ['club_id' => $club->id],
            [
                'name' => 'Quỹ ' . $club->name,
                'current_amount' => 0,
                'initial_amount' => 0,
                'status' => 'active',
                'created_by' => $club->leader_id ?? $user->id,
            ]
        );

        // Tạo giao dịch nộp quỹ với status pending (chờ leader/treasurer duyệt)
        $transaction = new FundTransaction();
        $transaction->fund_id = $fund->id;
        $transaction->type = 'income'; // Nộp quỹ là thu nhập
        $transaction->amount = $request->amount;
        $transaction->title = 'Nộp quỹ từ ' . ($request->payer_name ?: $user->name);
        $transaction->description = $request->note;
        $transaction->category = 'Nộp quỹ từ thành viên';
        $transaction->payment_method = $request->payment_method;
        $transaction->transaction_code = $request->transaction_code;
        $transaction->payer_name = $request->payer_name ?: $user->name;
        $transaction->payer_phone = $request->payer_phone;
        $transaction->transaction_date = now();
        $transaction->status = 'pending'; // Chờ duyệt
        $transaction->created_by = $user->id;
        $transaction->save();

        // Gửi thông báo cho Leader và Treasurer
        $leaders = ClubMember::where('club_id', $club->id)
            ->whereIn('position', ['leader', 'treasurer'])
            ->where('status', 'active')
            ->pluck('user_id')
            ->unique();

        foreach ($leaders as $leaderId) {
            // Thông báo cho Leader/Treasurer
            $notification = Notification::create([
                'title' => 'Yêu cầu thanh toán quỹ mới',
                'message' => "{$transaction->payer_name} đã thanh toán quỹ số tiền " . number_format($request->amount, 0, ',', '.') . " VNĐ qua {$request->payment_method} cho CLB {$club->name}. Vui lòng kiểm tra và duyệt tại trang Quản lý quỹ > Yêu cầu nộp quỹ.",
                'type' => 'info',
                'sender_id' => $user->id,
            ]);

            // Gán notification cho leader/treasurer
            NotificationTarget::create([
                'notification_id' => $notification->id,
                'target_id' => $leaderId,
                'target_type' => 'user',
            ]);
        }

        // Gửi thông báo cho Admin
        $admins = User::where('is_admin', true)->pluck('id');
        foreach ($admins as $adminId) {
            $adminNotification = Notification::create([
                'title' => 'Yêu cầu thanh toán quỹ mới từ CLB ' . $club->name,
                'message' => "{$transaction->payer_name} từ CLB {$club->name} đã thanh toán quỹ số tiền " . number_format($request->amount, 0, ',', '.') . " VNĐ. Đang chờ xác nhận.",
                'type' => 'info',
                'sender_id' => $user->id,
            ]);

            NotificationTarget::create([
                'notification_id' => $adminNotification->id,
                'target_id' => $adminId,
                'target_type' => 'user',
            ]);
        }

        return redirect()->route('student.club-management.fund-deposit', ['club' => $club->id])
            ->with('success', 'Yêu cầu thanh toán đã được gửi. Vui lòng chờ Thủ quỹ hoặc Trưởng CLB xác nhận.');
    }

    /**
     * Hiển thị danh sách yêu cầu nộp quỹ (cho Leader và Treasurer)
     */
    public function fundDepositRequests(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $clubId = $request->input('club');
        
        if (!$clubId) {
            // Nếu không có club_id, lấy CLB đầu tiên của user
            $userClub = $user->clubs()->first();
            if ($userClub) {
                $clubId = $userClub->id;
            } else {
                return redirect()->route('student.club-management.index')
                    ->with('error', 'Bạn chưa tham gia CLB nào.');
            }
        }

        $club = Club::find($clubId);
        if (!$club || !$user->clubs->contains('id', $club->id)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không phải là thành viên của CLB này.');
        }

        $position = $user->getPositionInClub($club->id);
        if (!in_array($position, ['leader', 'treasurer'])) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB và Thủ quỹ mới có quyền xem danh sách yêu cầu nộp quỹ.');
        }

        // Lấy quỹ của CLB
        $fund = Fund::where('club_id', $club->id)->first();
        if (!$fund) {
            $fund = Fund::create([
                'club_id' => $club->id,
                'name' => 'Quỹ ' . $club->name,
                'current_amount' => 0,
                'initial_amount' => 0,
                'status' => 'active',
                'created_by' => $user->id,
            ]);
        }

        // Lấy danh sách yêu cầu nộp quỹ (income type, pending status)
        $status = $request->input('status', 'pending'); // pending, approved, rejected, all
        
        $query = FundTransaction::where('fund_id', $fund->id)
            ->where('type', 'income')
            ->with(['creator', 'approver']);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Thống kê
        $stats = [
            'pending' => FundTransaction::where('fund_id', $fund->id)
                ->where('type', 'income')
                ->where('status', 'pending')
                ->count(),
            'approved' => FundTransaction::where('fund_id', $fund->id)
                ->where('type', 'income')
                ->where('status', 'approved')
                ->count(),
            'rejected' => FundTransaction::where('fund_id', $fund->id)
                ->where('type', 'income')
                ->where('status', 'rejected')
                ->count(),
            'total_pending_amount' => FundTransaction::where('fund_id', $fund->id)
                ->where('type', 'income')
                ->where('status', 'pending')
                ->sum('amount') ?? 0,
        ];

        return view('student.club-management.fund-deposit-requests', [
            'user' => $user,
            'club' => $club,
            'fund' => $fund,
            'requests' => $requests,
            'status' => $status,
            'stats' => $stats,
        ]);
    }

    /**
     * Xem bill/receipt của giao dịch nộp quỹ
     */
    public function fundDepositBill($transactionId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $transaction = FundTransaction::with(['fund.club', 'creator', 'approver'])
            ->findOrFail($transactionId);

        // Kiểm tra quyền: chỉ creator hoặc leader/treasurer của CLB đó mới xem được
        $club = $transaction->fund->club;
        $isCreator = $transaction->created_by == $user->id;
        $isLeader = false;
        
        if ($club && $user->clubs->contains('id', $club->id)) {
            $position = $user->getPositionInClub($club->id);
            $isLeader = in_array($position, ['leader', 'treasurer']);
        }

        if (!$isCreator && !$isLeader) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem bill này.');
        }

        return view('student.club-management.fund-deposit-bill', [
            'transaction' => $transaction,
            'club' => $club,
            'user' => $user,
        ]);
    }

    /**
     * Manage payment QR codes (quản lý QR code thanh toán - chỉ Leader)
     */
    public function managePaymentQr(Request $request, $clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = $user->clubs()->where('clubs.id', $clubId)->first();
        if (!$club) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không phải là thành viên của CLB này.');
        }

        // Chỉ Leader mới có quyền quản lý QR code
        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Chỉ Trưởng CLB mới có quyền quản lý QR code thanh toán.');
        }

        $paymentQrs = ClubPaymentQr::where('club_id', $club->id)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.club-management.payment-qr', [
            'user' => $user,
            'club' => $club,
            'paymentQrs' => $paymentQrs,
        ]);
    }

    /**
     * Store payment QR code (Thêm QR code - chỉ Leader)
     */
    public function storePaymentQr(Request $request, $clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = $user->clubs()->where('clubs.id', $clubId)->first();
        if (!$club) {
            return redirect()->back()->with('error', 'Bạn không phải là thành viên của CLB này.');
        }

        // Chỉ Leader mới có quyền
        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới có quyền thêm QR code.');
        }

        $request->validate([
            'account_number' => 'required|string|max:255',
            'bank_code' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'qr_code_image' => 'required|image|max:2048',
        ]);

        // Xóa QR code cũ nếu có (chỉ cho phép 1 QR code mỗi CLB)
        ClubPaymentQr::where('club_id', $club->id)->delete();

        // Upload ảnh QR code
        $dir = public_path('uploads/qr-codes');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = time() . '_' . $club->id . '_' . $user->id . '.' . $request->file('qr_code_image')->getClientOriginalExtension();
        $request->file('qr_code_image')->move($dir, $filename);
        $qrCodeImage = 'uploads/qr-codes/' . $filename;

        // Tạo QR code mới
        ClubPaymentQr::create([
            'club_id' => $club->id,
            'payment_method' => 'VietQR',
            'account_number' => $request->account_number,
            'bank_code' => $request->bank_code,
            'account_name' => $request->account_name,
            'qr_code_image' => $qrCodeImage,
            'is_primary' => true,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        return redirect()->route('student.club-management.payment-qr', ['club' => $club->id])
            ->with('success', 'QR code thanh toán đã được thêm thành công.');
    }

    /**
     * Update payment QR code (Cập nhật QR code - chỉ Leader)
     */
    public function updatePaymentQr(Request $request, $clubId, $qrId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = $user->clubs()->where('clubs.id', $clubId)->first();
        if (!$club) {
            return redirect()->back()->with('error', 'Bạn không phải là thành viên của CLB này.');
        }

        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới có quyền cập nhật QR code.');
        }

        $paymentQr = ClubPaymentQr::where('club_id', $club->id)
            ->where('id', $qrId)
            ->firstOrFail();

        $request->validate([
            'account_number' => 'required|string|max:255',
            'bank_code' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'qr_code_image' => 'nullable|image|max:2048',
        ]);

        // Upload ảnh QR code mới nếu có
        if ($request->hasFile('qr_code_image')) {
            // Xóa ảnh cũ nếu có
            if ($paymentQr->qr_code_image && file_exists(public_path($paymentQr->qr_code_image))) {
                unlink(public_path($paymentQr->qr_code_image));
            }

            $dir = public_path('uploads/qr-codes');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $filename = time() . '_' . $club->id . '_' . $user->id . '.' . $request->file('qr_code_image')->getClientOriginalExtension();
            $request->file('qr_code_image')->move($dir, $filename);
            $paymentQr->qr_code_image = 'uploads/qr-codes/' . $filename;
        }

        // Cập nhật thông tin
        $paymentQr->account_number = $request->account_number;
        $paymentQr->bank_code = $request->bank_code;
        $paymentQr->account_name = $request->account_name;
        $paymentQr->save();

        return redirect()->route('student.club-management.payment-qr', ['club' => $club->id])
            ->with('success', 'QR code thanh toán đã được cập nhật thành công.');
    }

    /**
     * Delete payment QR code (Xóa QR code - chỉ Leader)
     */
    public function deletePaymentQr(Request $request, $clubId, $qrId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = $user->clubs()->where('clubs.id', $clubId)->first();
        if (!$club) {
            return redirect()->back()->with('error', 'Bạn không phải là thành viên của CLB này.');
        }

        $position = $user->getPositionInClub($club->id);
        if ($position !== 'leader') {
            return redirect()->back()->with('error', 'Chỉ Trưởng CLB mới có quyền xóa QR code.');
        }

        $paymentQr = ClubPaymentQr::where('club_id', $club->id)
            ->where('id', $qrId)
            ->firstOrFail();

        // Xóa ảnh nếu có
        if ($paymentQr->qr_code_image && file_exists(public_path($paymentQr->qr_code_image))) {
            unlink(public_path($paymentQr->qr_code_image));
        }

        $paymentQr->delete();

        return redirect()->route('student.club-management.payment-qr', ['club' => $club->id])
            ->with('success', 'QR code thanh toán đã được xóa thành công.');
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
        $baseQuery = Post::with(['club', 'user', 'attachments']);
        
        // Chỉ thêm withCount('likes') nếu bảng post_likes đã tồn tại
        if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
            $baseQuery->withCount('likes');
        }
        
        $baseQuery->where(function($q) use ($userClubIds) {
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

        $posts = $postsQuery->paginate(5);
        $announcements = $announcementsQuery->limit(5)->get();
        $clubs = Club::where('status', 'active')->get();

        return view('student.posts.index', compact('posts', 'clubs', 'user', 'announcements'));
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

        $postQuery = Post::with(['club', 'user', 'comments.user', 'attachments']);
        
        // Chỉ load likes nếu bảng post_likes đã tồn tại
        if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
            $postQuery->with('likes');
        }
        
        $post = $postQuery->findOrFail($id);
        
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

        // Bài viết liên quan: cùng CLB, đã xuất bản (hoặc members_only nếu là thành viên CLB), chỉ loại post (không bao gồm announcement)
        $relatedQuery = Post::with(['club', 'user'])
            ->where('id', '!=', $post->id)
            ->where('club_id', $post->club_id)
            ->where('type', 'post') // Chỉ lấy bài viết, không lấy thông báo
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

        // Chỉ kiểm tra like nếu bảng post_likes đã tồn tại
        $isLiked = false;
        $likesCount = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
            $isLiked = $post->isLikedBy($user->id);
            $likesCount = $post->likes()->count();
        }

        return view('student.posts.show', compact('post', 'user', 'relatedPosts', 'isLiked', 'likesCount'));
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
     * Toggle like on a post
     */
    public function toggleLike($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Kiểm tra xem bảng post_likes có tồn tại không
        if (!\Illuminate\Support\Facades\Schema::hasTable('post_likes')) {
            return response()->json([
                'success' => false, 
                'message' => 'Tính năng lượt thích chưa được kích hoạt. Vui lòng chạy migration để tạo bảng post_likes.'
            ], 503);
        }

        $post = Post::findOrFail($id);

        // Kiểm tra quyền like (phải xem được bài viết)
        $canLike = false;
        if ($post->status === 'published') {
            $canLike = true;
        } elseif ($post->status === 'members_only') {
            $userClubIds = $user->clubs->pluck('id')->toArray();
            $canLike = in_array($post->club_id, $userClubIds);
        }

        if (!$canLike) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thích bài viết này.'], 403);
        }

        try {
            $like = \App\Models\PostLike::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->first();

            if ($like) {
                // Unlike
                $like->delete();
                $liked = false;
            } else {
                // Like
                \App\Models\PostLike::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]);
                $liked = true;
            }

            $likesCount = $post->likes()->count();

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likesCount' => $likesCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling like: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thực hiện thao tác: ' . $e->getMessage()
            ], 500);
        }
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
        
        // Chỉ lấy các CLB mà user có quyền đăng thông báo
        $userClubs = $user->clubs()->where('clubs.status', 'active')->get();
        $clubs = collect();
        
        foreach ($userClubs as $club) {
            if ($user->hasPermission('dang_thong_bao', $club->id)) {
                $clubs->push($club);
            }
        }
        
        if ($clubs->isEmpty()) {
            return redirect()->route('student.posts')->with('error', 'Bạn không có quyền tạo bài viết cho bất kỳ CLB nào.');
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
        
        // Kiểm tra quyền đăng thông báo/bài viết
        $clubId = $request->club_id;
        if (!$user->hasPermission('dang_thong_bao', $clubId)) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo bài viết cho CLB này.')->withInput();
        }
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
        
        // Tạo thông báo cho admin khi có bài viết mới
        try {
            $club = Club::find($post->club_id);
            $postType = $post->type === 'announcement' ? 'thông báo' : 'bài viết';
            
            $notification = Notification::create([
                'sender_id' => $user->id,
                'title' => 'Bài viết mới được đăng',
                'message' => "{$user->name} đã đăng một {$postType} mới: \"{$post->title}\" trong CLB {$club->name}",
            ]);
            
            $this->notifyAdmins([
                'sender_id' => $user->id,
                'title' => 'Bài viết mới cần duyệt',
                'message' => "Bài viết \"{$post->title}\" của CLB \"{$post->club->name}\" đang chờ quản trị viên duyệt.",
                'related_id' => $post->id,
                'related_type' => 'Post',
                'type' => 'announcement',
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi khi tạo thông báo cho admin: ' . $e->getMessage());
        }
        
        // Tạo thông báo cho tất cả thành viên CLB nếu status là members_only
        if ($data['status'] === 'members_only') {
            try {
                $club = \App\Models\Club::find($clubId);
                
                // Lấy tất cả thành viên đang hoạt động của CLB
                $clubMembers = \App\Models\ClubMember::where('club_id', $clubId)
                    ->whereIn('status', ['active', 'approved'])
                    ->whereNull('deleted_at')
                    ->get();
                
                if ($clubMembers->count() > 0) {
                    // Tạo notification
                    $notification = \App\Models\Notification::create([
                        'sender_id' => $user->id,
                        'type' => $post->type === 'announcement' ? 'announcement' : 'post',
                        'title' => $post->type === 'announcement' ? "Thông báo từ CLB {$club->name}" : "Bài viết mới từ CLB {$club->name}",
                        'message' => "{$user->name} đã đăng " . ($post->type === 'announcement' ? 'thông báo' : 'bài viết') . " mới: \"{$post->title}\"",
                        'related_id' => $post->id,
                        'related_type' => 'Post',
                    ]);
                    
                    // Tạo notification targets cho tất cả thành viên (trừ người đăng)
                    foreach ($clubMembers as $member) {
                        if ($member->user_id != $user->id) {
                            \App\Models\NotificationTarget::create([
                                'notification_id' => $notification->id,
                                'target_type' => 'user',
                                'target_id' => $member->user_id,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Lỗi khi tạo thông báo cho thành viên CLB: ' . $e->getMessage());
            }
        }

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
        
        // Kiểm tra quyền đăng thông báo/bài viết
        if (!$user->hasPermission('dang_thong_bao', $club->id)) {
            return redirect()->route('student.clubs.show', $club->id)->with('error', 'Bạn không có quyền tạo bài viết cho CLB này.');
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
     * Show create announcement form
     */
    public function createAnnouncement(Request $request)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        
        $clubId = $request->get('club_id');
        
        // Get clubs where user has permission to post announcements
        // Fix ambiguous column by specifying table name
        $userClubs = $user->clubs()->where('clubs.status', 'active')->get();
        $clubs = collect();
        
        foreach ($userClubs as $club) {
            if ($user->hasPermission('dang_thong_bao', $club->id)) {
                $clubs->push($club);
            }
        }
        
        if ($clubs->isEmpty()) {
            return redirect()->route('student.club-management.index')->with('error', 'Bạn không có quyền đăng thông báo ở bất kỳ CLB nào.');
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

        // Check permission
        $clubId = $request->club_id;
        if (!$user->hasPermission('dang_thong_bao', $clubId)) {
            return redirect()->back()->with('error', 'Bạn không có quyền đăng thông báo cho CLB này.')->withInput();
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:published,members_only,hidden',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096'
        ]);
        
        $data = $request->only(['title','content','club_id','status']);
        $data['type'] = 'announcement';
        $data['user_id'] = $user->id;
        
        // Generate unique slug
        $baseSlug = \Illuminate\Support\Str::slug($data['title']);
        $slug = $baseSlug;
        $suffix = 1;
        while (\App\Models\Post::where('slug', $slug)->exists()) {
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
        
        // Tạo thông báo cho tất cả thành viên CLB nếu status là members_only
        if ($data['status'] === 'members_only') {
            try {
                $club = \App\Models\Club::find($clubId);
                
                // Lấy tất cả thành viên đang hoạt động của CLB
                $clubMembers = \App\Models\ClubMember::where('club_id', $clubId)
                    ->whereIn('status', ['active', 'approved'])
                    ->whereNull('deleted_at')
                    ->get();
                
                if ($clubMembers->count() > 0) {
                    // Tạo notification
                    $notification = \App\Models\Notification::create([
                        'sender_id' => $user->id,
                        'type' => 'announcement',
                        'title' => "Thông báo từ CLB {$club->name}",
                        'message' => "{$user->name} đã đăng thông báo mới: \"{$post->title}\"",
                        'related_id' => $post->id,
                        'related_type' => 'Post',
                    ]);
                    
                    // Tạo notification targets cho tất cả thành viên (trừ người đăng)
                    foreach ($clubMembers as $member) {
                        if ($member->user_id != $user->id) {
                            \App\Models\NotificationTarget::create([
                                'notification_id' => $notification->id,
                                'target_type' => 'user',
                                'target_id' => $member->user_id,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Lỗi khi tạo thông báo cho thành viên CLB: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('student.club-management.posts', ['club' => $clubId, 'tab' => 'announcements'])->with('success', 'Tạo thông báo thành công!');
    }

    /**
     * Show edit announcement form
     */
    public function editAnnouncement($id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $post = \App\Models\Post::findOrFail($id);
        
        // Check if it's an announcement
        if ($post->type !== 'announcement') {
            return redirect()->route('student.posts.show', $id)->with('error', 'Đây không phải là thông báo.');
        }

        // Check permission: user must be owner, leader, or have permission to post announcements
        $isOwner = $post->user_id === $user->id;
        $isLeader = $user->getPositionInClub($post->club_id) === 'leader';
        $hasPermission = $user->hasPermission('dang_thong_bao', $post->club_id);
        
        if (!$isOwner && !$isLeader && !$hasPermission) {
            return redirect()->route('student.posts.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa thông báo này.');
        }

        // Get clubs where user has permission
        // Fix ambiguous column by specifying table name
        $userClubs = $user->clubs()->where('clubs.status', 'active')->get();
        $clubs = collect();
        
        foreach ($userClubs as $club) {
            if ($user->hasPermission('dang_thong_bao', $club->id)) {
                $clubs->push($club);
            }
        }

        if ($clubs->isEmpty()) {
            return redirect()->route('student.posts.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa thông báo.');
        }

        return view('student.announcements.edit', compact('user', 'post', 'clubs'));
    }

    /**
     * Update announcement
     */
    public function updateAnnouncement(Request $request, $id)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $post = \App\Models\Post::findOrFail($id);

        // Check if it's an announcement
        if ($post->type !== 'announcement') {
            return redirect()->route('student.posts.show', $id)->with('error', 'Đây không phải là thông báo.');
        }

        // Check permission: user must be owner, leader, or have permission to post announcements
        $isOwner = $post->user_id === $user->id;
        $isLeader = $user->getPositionInClub($post->club_id) === 'leader';
        $hasPermission = $user->hasPermission('dang_thong_bao', $post->club_id);
        
        if (!$isOwner && !$isLeader && !$hasPermission) {
            return redirect()->route('student.posts.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa thông báo này.');
        }

        $clubId = $request->club_id;
        
        // Kiểm tra nếu user đổi CLB, phải có quyền ở CLB mới
        if ($post->club_id != $clubId && !$user->hasPermission('dang_thong_bao', $clubId)) {
            return redirect()->back()->with('error', 'Bạn không có quyền đăng thông báo cho CLB này.')->withInput();
        }
        
        // Kiểm tra quyền ở CLB hiện tại (nếu không đổi CLB)
        if ($post->club_id == $clubId && !$isOwner && !$isLeader && !$hasPermission) {
            return redirect()->back()->with('error', 'Bạn không có quyền đăng thông báo cho CLB này.')->withInput();
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'club_id' => 'required|exists:clubs,id',
            'status' => 'required|in:published,members_only,hidden',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_image' => 'nullable|in:0,1'
        ]);

        $data = $request->only(['title','content','club_id','status']);
        $data['type'] = 'announcement';

        // Regenerate slug if title changed or slug missing
        if ($post->title !== $data['title'] || empty($post->slug)) {
            $baseSlug = \Illuminate\Support\Str::slug($data['title']);
            $slug = $baseSlug;
            $suffix = 1;
            while (\App\Models\Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
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

        // Remove featured image(s) from content if present
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
        
        return redirect()->route('student.club-management.posts', ['club' => $clubId, 'tab' => 'announcements'])->with('success', 'Cập nhật thông báo thành công!');
    }

    /**
     * Display a single club's details page.
     */
    public function showClub(Club $club)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $isMember = $user->clubs()->where('club_id', $club->id)->exists();
        
        // Load relationships
        $club->load(['members', 'field']);
        
        // Load posts với filter phù hợp
        // - Nếu là thành viên: hiển thị bài viết có status 'published' hoặc 'members_only'
        // - Nếu không phải thành viên: chỉ hiển thị bài viết có status 'published'
        $postsQuery = \App\Models\Post::with(['user', 'attachments'])
            ->where('club_id', $club->id)
            ->where('type', 'post')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'deleted');
        
        if ($isMember) {
            // Thành viên có thể xem published và members_only
            $postsQuery->whereIn('status', ['published', 'members_only']);
        } else {
            // Không phải thành viên chỉ xem published
            $postsQuery->where('status', 'published');
        }
        
        $posts = $postsQuery->orderBy('created_at', 'desc')->limit(5)->get();
        
        // Load events với filter visibility
        // Logic: 
        // - public hoặc NULL: Tất cả mọi người đều thấy (kể cả không phải thành viên)
        // - internal: CHỈ thành viên của CLB tạo sự kiện đó (CLB này) mới thấy
        $eventsQuery = Event::with(['club', 'creator', 'images'])
            ->where('club_id', $club->id)
            ->where('status', 'approved')
            ->where('end_time', '>=', now());
        
        // Nếu user không phải thành viên CLB này, chỉ hiển thị events công khai hoặc NULL
        // Nếu là thành viên, hiển thị cả public, NULL và internal (vì là CLB của họ)
        if (!$isMember) {
            $eventsQuery->where(function($query) {
                $query->where('visibility', 'public')
                    ->orWhereNull('visibility'); // Coi NULL là public
            });
        } else {
            // Thành viên CLB có thể xem tất cả events của CLB (cả public, NULL và internal)
            $eventsQuery->where(function($query) {
                $query->where('visibility', 'public')
                    ->orWhereNull('visibility') // Coi NULL là public
                    ->orWhere('visibility', 'internal');
            });
        }
        
        $clubEvents = $eventsQuery->orderBy('start_time', 'asc')->get();
        
        // Gán events vào club object để view có thể dùng
        $club->setRelation('events', $clubEvents);
        
        // Lấy thông tin thành viên nếu user là thành viên
        $clubMember = null;
        if ($isMember) {
            $clubMember = \App\Models\ClubMember::where('user_id', $user->id)
                ->where('club_id', $club->id)
                ->first();
        }
        
        // Kiểm tra xem có yêu cầu tham gia đang chờ duyệt không
        $joinRequest = \App\Models\ClubJoinRequest::where('user_id', $user->id)
            ->where('club_id', $club->id)
            ->where('status', 'pending')
            ->first();

        // Tính toán số liệu thống kê
        $membersCount = \App\Models\ClubMember::where('club_id', $club->id)
            ->whereIn('status', ['active', 'approved'])
            ->count();
        
        $eventsCount = $clubEvents->count();
        
        // Lấy danh sách announcements (chỉ thành viên mới thấy)
        $announcements = collect();
        if ($isMember) {
            $announcements = \App\Models\Post::with('user')
                ->where('club_id', $club->id)
                ->where('type', 'announcement')
                ->whereNull('deleted_at')
                ->where(function($query) {
                    $query->where('status', 'published')
                          ->orWhere('status', 'members_only');
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        
        $announcementsCount = \App\Models\Post::where('club_id', $club->id)
            ->where('type', 'announcement')
            ->whereNull('deleted_at')
            ->where(function($query) {
                $query->where('status', 'published')
                      ->orWhere('status', 'members_only');
            })
            ->count();

        return view('student.clubs.show', compact('user', 'club', 'isMember', 'clubMember', 'joinRequest', 'membersCount', 'eventsCount', 'announcementsCount', 'announcements', 'posts'));
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

        // 1. Kiểm tra xem user đã là thành viên đang hoạt động chưa (chưa rời)
        // Chỉ kiểm tra các membership chưa bị soft delete và có status active/approved
        $isActiveMember = ClubMember::where('user_id', $user->id)
            ->where('club_id', $club->id)
            ->whereIn('status', ['active', 'approved'])
            ->whereNull('deleted_at')
            ->exists();
        
        if ($isActiveMember) {
            return redirect()->back()->with('error', 'Bạn đã là thành viên của câu lạc bộ này.');
        }

        // 2. Kiểm tra xem user đã có yêu cầu đang chờ xử lý chưa
        $hasPendingRequest = $club->joinRequests()->where('user_id', $user->id)->where('status', 'pending')->exists();
        if ($hasPendingRequest) {
            return redirect()->back()->with('info', 'Bạn đã gửi yêu cầu tham gia câu lạc bộ này rồi. Vui lòng chờ duyệt.');
        }

        // 3. Tạo yêu cầu tham gia mới
        $joinRequest = ClubJoinRequest::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'message' => $request->input('message'), // Tùy chọn: có thể thêm ô lời nhắn
        ]);

        // 4. Gửi thông báo cho tất cả admin về yêu cầu tham gia mới
        $admins = \App\Models\User::where(function($query) {
                $query->where('is_admin', true)
                      ->orWhere('role', 'admin');
            })
            ->get();
        
        if ($admins->count() > 0) {
            $notification = \App\Models\Notification::create([
                'sender_id' => $user->id,
                'title' => 'Yêu cầu tham gia CLB mới',
                'message' => "Người dùng {$user->name} đã gửi yêu cầu tham gia CLB \"{$club->name}\". Vui lòng xem xét và duyệt đơn.",
            ]);
            
            // Tạo notification_targets và notification_reads cho từng admin
            foreach ($admins as $admin) {
                \App\Models\NotificationTarget::create([
                    'notification_id' => $notification->id,
                    'target_type' => 'user',
                    'target_id' => $admin->id,
                ]);
                
                // Tạo notification_read record với is_read = false
                \App\Models\NotificationRead::create([
                    'notification_id' => $notification->id,
                    'user_id' => $admin->id,
                    'is_read' => false,
                ]);
            }
        }

        // 5. Gửi thông báo cho Trưởng CLB (nếu có)
        $leaderIds = [];
        if ($club->leader_id) {
            $leaderIds[] = $club->leader_id;
        } else {
            $leaders = \App\Models\ClubMember::where('club_id', $club->id)
                ->whereIn('status', ['approved', 'active'])
                ->whereIn('position', ['leader', 'owner'])
                ->pluck('user_id')
                ->toArray();
            $leaderIds = array_merge($leaderIds, $leaders);
        }
        $leaderIds = array_unique(array_filter($leaderIds));

        if (!empty($leaderIds)) {
            $notificationLeader = \App\Models\Notification::create([
                'sender_id' => $user->id,
                'type' => 'club_join_request',
                'title' => 'Có yêu cầu tham gia CLB mới',
                'message' => "{$user->name} đã gửi yêu cầu tham gia CLB \"{$club->name}\". Vui lòng xem xét và duyệt.",
                'related_id' => $joinRequest->id,
                'related_type' => \App\Models\ClubJoinRequest::class,
            ]);

            foreach ($leaderIds as $leaderId) {
                \App\Models\NotificationTarget::create([
                    'notification_id' => $notificationLeader->id,
                    'target_type' => 'user',
                    'target_id' => $leaderId,
                ]);
                \App\Models\NotificationRead::create([
                    'notification_id' => $notificationLeader->id,
                    'user_id' => $leaderId,
                    'is_read' => false,
                ]);
            }
        }

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

    /**
     * Display resources management page for a club
     */
    public function clubManagementResources(Request $request, $clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail((int) $clubId);
        
        // Check if user is member and has management role
        if (!$user->clubs->contains('id', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không phải thành viên của CLB này.');
        }

        // Cho phép tất cả thành viên xem tài nguyên (chỉ cần là thành viên)
        // Không cần kiểm tra quyền ở đây

        $query = \App\Models\ClubResource::with(['user', 'images', 'files'])
            ->where('club_id', $clubId)
            ->where('status', '!=', 'deleted');

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $resources = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('student.club-management.resources', compact('user', 'club', 'clubId', 'resources'));
    }

    /**
     * Show form to create a new resource
     */
    public function clubManagementResourcesCreate($clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail((int) $clubId);
        
        if (!$user->clubs->contains('id', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không phải thành viên của CLB này.');
        }

        // Chỉ Leader và Vice President mới có quyền tạo tài nguyên
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president'])) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Chỉ Trưởng CLB và Phó CLB mới có quyền tạo tài nguyên.');
        }

        return view('student.club-management.resources.create', compact('user', 'club', 'clubId'));
    }

    /**
     * Store a new resource
     */
    public function clubManagementResourcesStore(Request $request, $clubId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail((int) $clubId);
        
        if (!$user->clubs->contains('id', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không phải thành viên của CLB này.');
        }

        // Chỉ Leader và Vice President mới có quyền tạo tài nguyên
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president'])) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Chỉ Trưởng CLB và Phó CLB mới có quyền tạo tài nguyên.');
        }

        $request->validate([
            'title' => 'required|string|min:5|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,archived',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|mimes:doc,docx,xls,xlsx,pdf|max:20480',
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,avi,mov|max:102400',
            'external_link' => 'nullable|url|max:500',
            'tags' => 'nullable|string'
        ]);

        $resource = \App\Models\ClubResource::create([
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . time(),
            'description' => $request->description,
            'resource_type' => 'other',
            'club_id' => $clubId,
            'user_id' => $user->id,
            'status' => $request->status,
            'external_link' => $request->external_link,
            'tags' => $request->tags ? explode(',', $request->tags) : null
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            $this->handleResourceFileUpload($resource, $request->file('files'));
        }

        if ($request->hasFile('images')) {
            $this->handleResourceImageUpload($resource, $request->file('images'));
        }

        return redirect()->route('student.club-management.resources', ['club' => $clubId])
            ->with('success', 'Tài nguyên đã được tạo thành công!');
    }

    /**
     * Show a specific resource
     */
    public function clubManagementResourcesShow($clubId, $resourceId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail((int) $clubId);
        $resource = \App\Models\ClubResource::with(['user', 'images', 'files'])
            ->where('club_id', $clubId)
            ->findOrFail($resourceId);

        if (!$user->clubs->contains('id', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không phải thành viên của CLB này.');
        }

        $resource->incrementViewCount();

        return view('student.club-management.resources.show', compact('user', 'club', 'clubId', 'resource'));
    }

    /**
     * Show form to edit a resource
     */
    public function clubManagementResourcesEdit($clubId, $resourceId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail((int) $clubId);
        $resource = \App\Models\ClubResource::with(['images', 'files'])
            ->where('club_id', $clubId)
            ->findOrFail($resourceId);

        if (!$user->clubs->contains('id', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không phải thành viên của CLB này.');
        }

        // Chỉ Leader và Vice President mới có quyền chỉnh sửa tài nguyên
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president'])) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Chỉ Trưởng CLB và Phó CLB mới có quyền chỉnh sửa tài nguyên.');
        }

        return view('student.club-management.resources.edit', compact('user', 'club', 'clubId', 'resource'));
    }

    /**
     * Update a resource
     */
    public function clubManagementResourcesUpdate(Request $request, $clubId, $resourceId)
    {
        $user = $this->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $club = Club::findOrFail((int) $clubId);
        $resource = \App\Models\ClubResource::where('club_id', $clubId)->findOrFail($resourceId);

        if (!$user->clubs->contains('id', $clubId)) {
            return redirect()->route('student.club-management.index')
                ->with('error', 'Bạn không phải thành viên của CLB này.');
        }

        // Chỉ Leader và Vice President mới có quyền chỉnh sửa tài nguyên
        $position = $user->getPositionInClub($clubId);
        if (!in_array($position, ['leader', 'vice_president'])) {
            return redirect()->route('student.club-management.resources', ['club' => $clubId])
                ->with('error', 'Chỉ Trưởng CLB và Phó CLB mới có quyền chỉnh sửa tài nguyên.');
        }

        $request->validate([
            'title' => 'required|string|min:5|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,archived',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|mimes:doc,docx,xls,xlsx,pdf|max:20480',
            'deleted_files' => 'nullable|array',
            'deleted_files.*' => 'integer|exists:club_resource_files,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,avi,mov|max:102400',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'integer|exists:club_resource_images,id',
            'external_link' => 'nullable|url|max:500',
            'tags' => 'nullable|string'
        ]);

        $resource->update([
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . $resource->id,
            'description' => $request->description,
            'status' => $request->status,
            'external_link' => $request->external_link,
            'tags' => $request->tags ? explode(',', $request->tags) : null
        ]);

        // Handle file and image updates
        $this->handleResourceFileAlbumUpdate($resource, $request);
        $this->handleResourceImageAlbumUpdate($resource, $request);

        return redirect()->route('student.club-management.resources', ['club' => $clubId])
            ->with('success', 'Tài nguyên đã được cập nhật thành công!');
    }

    /**
     * Handle resource file upload
     */
    private function handleResourceFileUpload($resource, $files)
    {
        $uploadPath = 'club-resources/' . $resource->club_id . '/files';
        
        foreach ($files as $index => $file) {
            $filename = time() . '_' . $index . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $fullPath = $uploadPath . '/' . $filename;
            
            $file->storeAs('public/' . $uploadPath, $filename);
            
            $resource->files()->create([
                'file_path' => $fullPath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'sort_order' => $index,
                'is_primary' => $index === 0
            ]);
        }
    }

    /**
     * Handle resource image upload
     */
    private function handleResourceImageUpload($resource, $images)
    {
        $uploadPath = 'club-resources/' . $resource->club_id . '/images';
        
        foreach ($images as $index => $image) {
            $filename = time() . '_' . $index . '_' . \Illuminate\Support\Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $image->getClientOriginalExtension();
            $fullPath = $uploadPath . '/' . $filename;
            
            $image->storeAs('public/' . $uploadPath, $filename);
            
            $resource->images()->create([
                'image_path' => $fullPath,
                'image_name' => $image->getClientOriginalName(),
                'image_type' => $image->getMimeType(),
                'image_size' => $image->getSize(),
                'sort_order' => $index,
                'is_primary' => $index === 0
            ]);
        }
    }

    /**
     * Handle resource file album update
     */
    private function handleResourceFileAlbumUpdate($resource, $request)
    {
        // Delete files if requested
        if ($request->filled('deleted_files')) {
            foreach ($request->deleted_files as $fileId) {
                $file = \App\Models\ClubResourceFile::find($fileId);
                if ($file && $file->club_resource_id == $resource->id) {
                    if ($file->file_path) {
                        \Illuminate\Support\Facades\Storage::delete('public/' . $file->file_path);
                    }
                    $file->delete();
                }
            }
        }

        // Add new files
        if ($request->hasFile('files')) {
            $this->handleResourceFileUpload($resource, $request->file('files'));
        }

        // Update primary file
        if ($request->filled('primary_file_id')) {
            \App\Models\ClubResourceFile::where('club_resource_id', $resource->id)
                ->update(['is_primary' => false]);
            $primaryFile = \App\Models\ClubResourceFile::find($request->primary_file_id);
            if ($primaryFile && $primaryFile->club_resource_id == $resource->id) {
                $primaryFile->update(['is_primary' => true]);
            }
        }
    }

    /**
     * Handle resource image album update
     */
    private function handleResourceImageAlbumUpdate($resource, $request)
    {
        // Delete images if requested
        if ($request->filled('deleted_images')) {
            foreach ($request->deleted_images as $imageId) {
                $image = \App\Models\ClubResourceImage::find($imageId);
                if ($image && $image->club_resource_id == $resource->id) {
                    if ($image->image_path) {
                        \Illuminate\Support\Facades\Storage::delete('public/' . $image->image_path);
                    }
                    $image->delete();
                }
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            $this->handleResourceImageUpload($resource, $request->file('images'));
        }

        // Update primary image
        if ($request->filled('primary_image_id')) {
            \App\Models\ClubResourceImage::where('club_resource_id', $resource->id)
                ->update(['is_primary' => false]);
            $primaryImage = \App\Models\ClubResourceImage::find($request->primary_image_id);
            if ($primaryImage && $primaryImage->club_resource_id == $resource->id) {
                $primaryImage->update(['is_primary' => true]);
            }
        }
    }

}

