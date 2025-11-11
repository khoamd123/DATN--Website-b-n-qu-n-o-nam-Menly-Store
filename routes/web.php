<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubManagerController;
use App\Http\Controllers\ClubResourceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Debug route for date filter
Route::get('/admin/debug-date-filter', [AdminController::class, 'debugDateFilter'])->name('admin.debug-date-filter');


Route::get('/admin/test-date-filter', [AdminController::class, 'testDateFilter'])->name('admin.test-date-filter');

// Quick test routes
Route::get('/admin/test-today', function() {
    return redirect()->route('admin.dashboard', ['date_range' => 'today']);
})->name('admin.test-today');
Route::get('/admin/test-yesterday', function() {
    return redirect()->route('admin.dashboard', ['date_range' => 'yesterday']);
})->name('admin.test-yesterday');
Route::get('/admin/test-yesterday-debug', function() {
    return redirect()->route('admin.test-date-filter', ['date_range' => 'yesterday']);
})->name('admin.test-yesterday-debug');

// Route đăng nhập đơn giản cho admin
// Simple Login Routes
Route::get('/admin-login', [App\Http\Controllers\SimpleLoginController::class, 'showLogin'])->name('simple.login');
Route::post('/admin-login', [App\Http\Controllers\SimpleLoginController::class, 'login'])->name('simple.login.submit');
Route::post('/logout', [App\Http\Controllers\SimpleLoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Routes for user authentication
|
*/

// Routes without guest middleware for now
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Quick login route for testing
Route::get('/quick-login-student', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    
    if (!$user) {
        return redirect()->route('login')->with('error', 'User not found');
    }
    
    // Set session với session_start() để đảm bảo session hoạt động
    session_start();
    session([
        'logged_in' => true,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
        'student_id' => $user->student_id,
        'is_admin' => false,
        'club_roles' => []
    ]);
    
    // Update club roles
    if ($user->clubs->count() > 0) {
        $clubRoles = [];
        foreach ($user->clubs as $club) {
            $position = $user->getPositionInClub($club->id);
            $clubRoles[$club->id] = $position;
        }
        session(['club_roles' => $clubRoles]);
    }
    
    // Force save session
    session()->save();
    
    return redirect()->route('student.dashboard')->with('success', 'Đăng nhập thành công!');
})->name('quick.login.student');

// Student Routes - BYPASS SESSION FOR TESTING
Route::get('/student/dashboard', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    if (!$user) return 'User not found';
    return view('student.dashboard', compact('user'));
})->name('student.dashboard');

Route::get('/student/clubs', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    if (!$user) return 'User not found';
    return view('student.clubs.index', compact('user'));
})->name('student.clubs.index');

Route::get('/student/events', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    if (!$user) return 'User not found';
    return view('student.events.index', compact('user'));
})->name('student.events.index');

Route::get('/student/profile', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    if (!$user) return 'User not found';
    return view('student.profile.index', compact('user'));
})->name('student.profile.index');

Route::get('/student/notifications', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    if (!$user) return 'User not found';
    return view('student.notifications.index', compact('user'));
})->name('student.notifications.index');

Route::get('/student/contact', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    if (!$user) return 'User not found';
    return view('student.contact', compact('user'));
})->name('student.contact.index');

// Student Posts Routes
Route::get('/student/posts', [\App\Http\Controllers\StudentController::class, 'posts'])->name('student.posts');
// Create must be before {id}
Route::get('/student/posts/create', [\App\Http\Controllers\StudentController::class, 'createPost'])->name('student.posts.create');
Route::post('/student/posts', [\App\Http\Controllers\StudentController::class, 'storePost'])->name('student.posts.store');
// Edit must be before {id}
Route::get('/student/posts/{id}/edit', [\App\Http\Controllers\StudentController::class, 'editPost'])->whereNumber('id')->name('student.posts.edit');
Route::put('/student/posts/{id}', [\App\Http\Controllers\StudentController::class, 'updatePost'])->whereNumber('id')->name('student.posts.update');
// Manage list and delete
Route::get('/student/my-posts', [\App\Http\Controllers\StudentController::class, 'myPosts'])->name('student.posts.manage');
Route::delete('/student/posts/{id}', [\App\Http\Controllers\StudentController::class, 'deletePost'])->whereNumber('id')->name('student.posts.delete');
// Show and comments with numeric constraint
Route::get('/student/posts/{id}', [\App\Http\Controllers\StudentController::class, 'showPost'])->whereNumber('id')->name('student.posts.show');
Route::post('/student/posts/{id}/comments', [\App\Http\Controllers\StudentController::class, 'addPostComment'])->whereNumber('id')->name('student.posts.comment');

Route::get('/student/club-management', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    if (!$user) return 'User not found';
    
    $hasManagementRole = false;
    $userPosition = null;
    $userClub = null;
    
    if ($user->clubs->count() > 0) {
        $userClub = $user->clubs->first();
        $clubId = $userClub->id;
        $userPosition = $user->getPositionInClub($clubId);
        $hasManagementRole = in_array($userPosition, ['leader', 'vice_president', 'officer']);
    }
    
    return view('student.club-management.index', compact('user', 'hasManagementRole', 'userPosition', 'userClub'));
})->name('student.club-management.index');

// Test route without session check - TEMPORARY
Route::get('/test-club-management', function () {
    $user = \App\Models\User::where('email', 'khoamdph31863@fpt.edu.vn')->first();
    
    if (!$user) {
        return 'User not found';
    }
    
    $hasManagementRole = false;
    $userPosition = null;
    $userClub = null;
    
    if ($user->clubs->count() > 0) {
        $userClub = $user->clubs->first();
        $clubId = $userClub->id;
        $userPosition = $user->getPositionInClub($clubId);
        $hasManagementRole = in_array($userPosition, ['leader', 'vice_president', 'officer']);
    }
    
    return view('student.club-management.index', compact('user', 'hasManagementRole', 'userPosition', 'userClub'));
})->name('test.club.management');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes for admin panel management
|
*/

Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
            // Quản lý người dùng
            Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
            Route::get('/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
            Route::get('/users/next-student-id', [AdminController::class, 'nextStudentId'])->name('admin.users.next-student-id');
            Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
            Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('admin.users.show');
            Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
            Route::get('/users-simple', [AdminController::class, 'usersSimple'])->name('admin.users.simple');
            Route::patch('/users/{id}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');
            Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    
            // Quản lý quỹ
            Route::get('/funds', [App\Http\Controllers\FundController::class, 'index'])->name('admin.funds');
            Route::get('/funds/create', [App\Http\Controllers\FundController::class, 'create'])->name('admin.funds.create');
            Route::post('/funds', [App\Http\Controllers\FundController::class, 'store'])->name('admin.funds.store');
            Route::get('/funds/{fund}', [App\Http\Controllers\FundController::class, 'show'])->name('admin.funds.show');
            Route::get('/funds/{fund}/edit', [App\Http\Controllers\FundController::class, 'edit'])->name('admin.funds.edit');
            Route::put('/funds/{fund}', [App\Http\Controllers\FundController::class, 'update'])->name('admin.funds.update');
            Route::delete('/funds/{fund}', [App\Http\Controllers\FundController::class, 'destroy'])->name('admin.funds.destroy');
            
            // Quản lý giao dịch quỹ
            Route::get('/funds/{fund}/transactions', [App\Http\Controllers\FundTransactionController::class, 'index'])->name('admin.funds.transactions');
            Route::get('/funds/{fund}/transactions/create', [App\Http\Controllers\FundTransactionController::class, 'create'])->name('admin.funds.transactions.create');
            Route::post('/funds/{fund}/transactions', [App\Http\Controllers\FundTransactionController::class, 'store'])->name('admin.funds.transactions.store');
            Route::get('/funds/{fund}/transactions/{transaction}', [App\Http\Controllers\FundTransactionController::class, 'show'])->name('admin.funds.transactions.show');
            Route::get('/funds/{fund}/transactions/{transaction}/edit', [App\Http\Controllers\FundTransactionController::class, 'edit'])->name('admin.funds.transactions.edit');
            Route::put('/funds/{fund}/transactions/{transaction}', [App\Http\Controllers\FundTransactionController::class, 'update'])->name('admin.funds.transactions.update');
            Route::post('/funds/{fund}/transactions/{transaction}/approve', [App\Http\Controllers\FundTransactionController::class, 'approve'])->name('admin.funds.transactions.approve');
            Route::post('/funds/{fund}/transactions/{transaction}/approve-partial', [App\Http\Controllers\FundTransactionController::class, 'approvePartial'])->name('admin.funds.transactions.approve-partial');
            Route::post('/funds/{fund}/transactions/{transaction}/reject', [App\Http\Controllers\FundTransactionController::class, 'reject'])->name('admin.funds.transactions.reject');
            Route::post('/funds/{fund}/transactions/{transaction}/cancel', [App\Http\Controllers\FundTransactionController::class, 'cancel'])->name('admin.funds.transactions.cancel');
            Route::delete('/funds/{fund}/transactions/{transaction}', [App\Http\Controllers\FundTransactionController::class, 'destroy'])->name('admin.funds.transactions.destroy');
            Route::get('/funds/{fund}/transactions/{transaction}/invoice', [App\Http\Controllers\FundTransactionController::class, 'exportInvoice'])->name('admin.funds.transactions.invoice');
        
        // Route tạm để sửa số tiền
        Route::get('/funds/{fund}/fix-amount', function($fundId) {
            $fund = App\Models\Fund::find($fundId);
            $fund->updateCurrentAmount();
            return redirect()->route('admin.funds.show', $fundId)->with('success', 'Đã cập nhật số tiền!');
        })->name('admin.funds.fix-amount');
        
        // Quản lý yêu cầu cấp kinh phí
        Route::get('/fund-requests', [App\Http\Controllers\FundRequestController::class, 'index'])->name('admin.fund-requests');
        Route::get('/fund-requests/create', [App\Http\Controllers\FundRequestController::class, 'create'])->name('admin.fund-requests.create');
        Route::post('/fund-requests', [App\Http\Controllers\FundRequestController::class, 'store'])->name('admin.fund-requests.store');
        Route::get('/fund-requests/{fundRequest}', [App\Http\Controllers\FundRequestController::class, 'show'])->name('admin.fund-requests.show');
        Route::get('/fund-requests/{fundRequest}/edit', [App\Http\Controllers\FundRequestController::class, 'edit'])->name('admin.fund-requests.edit');
        Route::put('/fund-requests/{fundRequest}', [App\Http\Controllers\FundRequestController::class, 'update'])->name('admin.fund-requests.update');
        Route::post('/fund-requests/{fundRequest}/approve', [App\Http\Controllers\FundRequestController::class, 'approve'])->name('admin.fund-requests.approve');
        Route::post('/fund-requests/{fundRequest}/reject', [App\Http\Controllers\FundRequestController::class, 'reject'])->name('admin.fund-requests.reject');
        Route::delete('/fund-requests/{fundRequest}', [App\Http\Controllers\FundRequestController::class, 'destroy'])->name('admin.fund-requests.destroy');
        
        // Duyệt hàng loạt
        Route::get('/fund-requests/batch-approval', [App\Http\Controllers\FundRequestController::class, 'batchApproval'])->name('admin.fund-requests.batch-approval');
        Route::post('/fund-requests/batch-approval/process', [App\Http\Controllers\FundRequestController::class, 'processBatchApproval'])->name('admin.fund-requests.batch-approval.process');
        
        // Quyết toán kinh phí
        Route::get('/fund-settlements', [App\Http\Controllers\FundSettlementController::class, 'index'])->name('admin.fund-settlements');
        Route::get('/fund-settlements/{fundRequest}/create', [App\Http\Controllers\FundSettlementController::class, 'create'])->name('admin.fund-settlements.create');
        Route::post('/fund-settlements/{fundRequest}', [App\Http\Controllers\FundSettlementController::class, 'store'])->name('admin.fund-settlements.store');
        Route::get('/fund-settlements/{fundRequest}/show', [App\Http\Controllers\FundSettlementController::class, 'show'])->name('admin.fund-settlements.show');
        
        // Test route để debug
        Route::get('/test-auth', function() {
            return [
                'auth_id' => Auth::id(),
                'auth_user' => Auth::user(),
                'users_count' => App\Models\User::count(),
                'first_user' => App\Models\User::first()
            ];
        });
        
        // Route để reset trạng thái yêu cầu về pending
        Route::get('/fund-requests/{fundRequest}/reset-status', function($fundRequestId) {
            $fundRequest = App\Models\FundRequest::find($fundRequestId);
            if ($fundRequest) {
                $fundRequest->update(['status' => 'pending']);
                return redirect()->route('admin.fund-requests.show', $fundRequestId)->with('success', 'Đã reset trạng thái về "Chờ duyệt"');
            }
            return redirect()->back()->with('error', 'Không tìm thấy yêu cầu');
        })->name('admin.fund-requests.reset-status');
        
        // Test route cho batch approval
        Route::get('/test-batch-approval', function() {
            return 'Batch approval route is working!';
        });
        
        // Test route để tạo yêu cầu đơn giản
        Route::get('/test-create-request', function() {
            try {
                $fundRequest = App\Models\FundRequest::create([
                    'title' => 'Test Request',
                    'description' => 'Test description',
                    'requested_amount' => 1000000,
                    'event_id' => 1,
                    'club_id' => 1,
                    'created_by' => 1,
                    'status' => 'pending'
                ]);
                return 'Fund request created successfully with ID: ' . $fundRequest->id;
            } catch (\Exception $e) {
                return 'Error: ' . $e->getMessage();
            }
        });
    
    // Phân quyền
    Route::get('/permissions', [AdminController::class, 'permissionsSimple'])->name('admin.permissions');
    Route::get('/permissions-detailed', [App\Http\Controllers\PermissionController::class, 'index'])->name('admin.permissions.detailed');
    Route::post('/permissions-detailed/add-to-club', [App\Http\Controllers\PermissionController::class, 'addToClub'])->name('admin.permissions.add-to-club');
    Route::post('/permissions/update', [App\Http\Controllers\PermissionController::class, 'updateUserPermissions'])->name('admin.permissions.update');
    Route::get('/permissions/user-permissions', [App\Http\Controllers\PermissionController::class, 'getUserPermissions'])->name('admin.permissions.user-permissions');
    Route::get('/permissions/user-position', [App\Http\Controllers\PermissionController::class, 'getUserPosition'])->name('admin.permissions.user-position');
    
    // Quản lý thùng rác
    Route::get('/trash', [App\Http\Controllers\TrashController::class, 'index'])->name('admin.trash');
    Route::post('/trash/restore', [App\Http\Controllers\TrashController::class, 'restore'])->name('admin.trash.restore');
    Route::post('/trash/force-delete', [App\Http\Controllers\TrashController::class, 'forceDelete'])->name('admin.trash.force-delete');
    Route::post('/trash/restore-all', [App\Http\Controllers\TrashController::class, 'restoreAll'])->name('admin.trash.restore-all');
    Route::post('/trash/force-delete-all', [App\Http\Controllers\TrashController::class, 'forceDeleteAll'])->name('admin.trash.force-delete-all');
    
    // Tìm kiếm
    Route::get('/search', [AdminController::class, 'search'])->name('admin.search');
    
    // Thông báo
    Route::get('/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');
    
    // Tin nhắn
    Route::get('/messages', [AdminController::class, 'messages'])->name('admin.messages');
    
    // Hồ sơ và cài đặt
    Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    
            // Quản lý câu lạc bộ - Routes của Huy
            Route::get('/clubs', [AdminController::class, 'clubs'])->name('admin.clubs');
            Route::get('/clubs/create', [AdminController::class, 'clubsCreate'])->name('admin.clubs.create');
            Route::post('/clubs', [AdminController::class, 'clubsStore'])->name('admin.clubs.store');
            // Status & Delete routes must come BEFORE {club} route to avoid conflicts
            Route::patch('/clubs/{id}/status', [AdminController::class, 'updateClubStatus'])->name('admin.clubs.status');
            Route::delete('/clubs/{id}', [AdminController::class, 'deleteClub'])->name('admin.clubs.delete');
            Route::get('/clubs/{club}', [AdminController::class, 'showClub'])->name('admin.clubs.show');
            Route::get('/clubs/{club}/edit', [AdminController::class, 'editClub'])->name('admin.clubs.edit');
            Route::put('/clubs/{club}', [AdminController::class, 'updateClub'])->name('admin.clubs.update');
            
            // Club Members
            Route::post('/clubs/{club}/members', [App\Http\Controllers\ClubManagementController::class, 'addMember'])->name('admin.clubs.members.add');
            Route::post('/clubs/{club}/members/{member}/approve', [AdminController::class, 'approveMember'])->name('admin.clubs.members.approve');
            Route::delete('/clubs/{club}/members/{member}/reject', [AdminController::class, 'rejectMember'])->name('admin.clubs.members.reject');
            Route::delete('/clubs/{club}/members/{member}/remove', [AdminController::class, 'removeMember'])->name('admin.clubs.members.remove');
            Route::post('/clubs/{club}/members/bulk-update', [AdminController::class, 'bulkUpdateMembers'])->name('admin.clubs.members.bulk-update');
    

    // Tài Nguyên CLB
    // Trash route must come BEFORE {id} route to avoid conflicts
    Route::get('/club-resources/trash', [ClubResourceController::class, 'trash'])->name('admin.club-resources.trash');
    Route::get('/club-resources/create', [ClubResourceController::class, 'create'])->name('admin.club-resources.create');
    Route::get('/club-resources', [ClubResourceController::class, 'index'])->name('admin.club-resources.index');
    Route::post('/club-resources', [ClubResourceController::class, 'store'])->name('admin.club-resources.store');
    Route::get('/club-resources/{id}', [ClubResourceController::class, 'show'])->name('admin.club-resources.show');
    Route::get('/club-resources/{id}/edit', [ClubResourceController::class, 'edit'])->name('admin.club-resources.edit');
    Route::get('/club-resources/{id}/download', [ClubResourceController::class, 'download'])->name('admin.club-resources.download');
    Route::put('/club-resources/{id}', [ClubResourceController::class, 'update'])->name('admin.club-resources.update');
    Route::delete('/club-resources/{id}', [ClubResourceController::class, 'destroy'])->name('admin.club-resources.destroy');
    Route::post('/club-resources/{id}/restore', [ClubResourceController::class, 'restore'])->name('admin.club-resources.restore');
    Route::delete('/club-resources/{id}/force-delete', [ClubResourceController::class, 'forceDelete'])->name('admin.club-resources.force-delete');
    Route::post('/club-resources/restore-all', [ClubResourceController::class, 'restoreAll'])->name('admin.club-resources.restore-all');
    Route::delete('/club-resources/force-delete-all', [ClubResourceController::class, 'forceDeleteAll'])->name('admin.club-resources.force-delete-all');
    
    // Kế hoạch
    Route::get('/plans-schedule', [AdminController::class, 'plansSchedule'])->name('admin.plans-schedule');
    
    // Events (sự kiện)
    Route::get('/events', [AdminController::class, 'eventsIndex'])->name('admin.events.index');
    Route::get('/events/create', [AdminController::class, 'eventsCreate'])->name('admin.events.create');
    Route::post('/events', [AdminController::class, 'eventsStore'])->name('admin.events.store');
    Route::get('/events/{id}/edit', [AdminController::class, 'eventsEdit'])->name('admin.events.edit');
    Route::put('/events/{id}', [AdminController::class, 'eventsUpdate'])->name('admin.events.update');
    Route::get('/events/{id}', [AdminController::class, 'eventsShow'])->name('admin.events.show');
    Route::post('/events/{id}/approve', [AdminController::class, 'eventsApprove'])->name('admin.events.approve');
    Route::post('/events/{id}/cancel', [AdminController::class, 'eventsCancel'])->name('admin.events.cancel');
    

    // Bài viết
    Route::get('/posts', [AdminController::class, 'postsManagement'])->name('admin.posts');
    Route::get('/posts/create', [AdminController::class, 'postsCreate'])->name('admin.posts.create');
    Route::post('/posts', [AdminController::class, 'postsStore'])->name('admin.posts.store');
    // Tạo nhanh 5 bài viết có ảnh cho 1 CLB hoặc cho tất cả CLB
    Route::post('/clubs/{club}/generate-sample-posts', [AdminController::class, 'generateSamplePostsForClub'])->name('admin.clubs.generate-posts');
    Route::post('/posts/generate-sample-for-all', [AdminController::class, 'generateSamplePostsForAllClubs'])->name('admin.posts.generate-sample-for-all');
    // Upload ảnh từ trình soạn thảo
    Route::post('/posts/upload-image', [PostController::class, 'uploadEditorImage'])->name('admin.posts.upload-image');
        Route::get('/posts/{id}', [AdminController::class, 'postsShow'])->name('admin.posts.show');
        Route::get('/posts/{id}/edit', [AdminController::class, 'postsEdit'])->name('admin.posts.edit');
        Route::put('/posts/{id}', [AdminController::class, 'postsUpdate'])->name('admin.posts.update');
        Route::patch('/posts/{id}/status', [AdminController::class, 'updatePostStatus'])->name('admin.posts.status');
        
        // Thùng rác bài viết
        Route::get('/posts-trash', [AdminController::class, 'postsTrash'])->name('admin.posts.trash');
        Route::post('/posts/{id}/restore', [AdminController::class, 'restorePost'])->name('admin.posts.restore');
        Route::delete('/posts/{id}/force-delete', [AdminController::class, 'forceDeletePost'])->name('admin.posts.force-delete');

    
    // Bình luận
    Route::get('/comments', [AdminController::class, 'commentsManagement'])->name('admin.comments');
    Route::delete('/comments/{type}/{id}', [AdminController::class, 'deleteComment'])->name('admin.comments.delete');
    
    // Phân quyền
    Route::get('/permissions', [AdminController::class, 'permissionsManagement'])->name('admin.permissions');
    Route::get('/permissions-simple', [AdminController::class, 'permissionsSimple'])->name('admin.permissions.simple');
    Route::get('/permissions-detailed', [App\Http\Controllers\PermissionController::class, 'index'])->name('admin.permissions.detailed');
    Route::patch('/permissions/{id}/user', [AdminController::class, 'updateUserPermissions'])->name('admin.permissions.user');
    
    // Test Links
    Route::get('/test-links', function() {
        return view('admin.test-links');
    })->name('admin.test-links');
    
    
    // Data Test
    Route::get('/data-test', function() {
        try {
            $usersCount = \App\Models\User::count();
            $clubsCount = \App\Models\Club::count();
            $postsCount = \App\Models\Post::count();
            $eventsCount = \App\Models\Event::count();
            
            $recentPosts = \App\Models\Post::orderBy('created_at', 'desc')->limit(5)->get();
            $activeClubs = \App\Models\Club::where('status', 'active')->limit(5)->get();
            $recentComments = \App\Models\PostComment::orderBy('created_at', 'desc')->limit(5)->get();
            
            return view('admin.data-test', compact('usersCount', 'clubsCount', 'postsCount', 'eventsCount', 'recentPosts', 'activeClubs', 'recentComments'));
        } catch (Exception $e) {
            return view('admin.data-test', [
                'usersCount' => 0,
                'clubsCount' => 0,
                'postsCount' => 0,
                'eventsCount' => 0,
                'recentPosts' => collect(),
                'activeClubs' => collect(),
                'recentComments' => collect(),
                'error' => $e->getMessage()
            ]);
        }
    })->name('admin.data-test');

// Route test debug
Route::get('/test-clubs-create', function () {
    $fields = \App\Models\Field::all();
    $users = \App\Models\User::where('is_admin', false)->get();
    
    return response()->json([
        'fields_count' => $fields->count(),
        'users_count' => $users->count(),
        'fields' => $fields->toArray(),
        'users' => $users->toArray()
    ]);
});

// Route test view
Route::get('/test-clubs-create-view', function () {
    $fields = \App\Models\Field::all();
    $users = \App\Models\User::where('is_admin', false)->get();
    
    return view('admin.clubs.create-simple', compact('fields', 'users'));
});

// Route test với controller mới
Route::get('/test-new-controller', [App\Http\Controllers\TestController::class, 'clubsCreate']);
    
    
    // Quản lý CLB cho Admin
    Route::get('/clubs-management', [App\Http\Controllers\ClubManagementController::class, 'index'])->name('admin.clubs.management');
    Route::get('/clubs/{club}/members', [AdminController::class, 'clubMembers'])->name('admin.clubs.members');
    Route::post('/clubs-management', [App\Http\Controllers\ClubManagementController::class, 'store'])->name('admin.clubs.management.store');
    Route::post('/clubs/create-student', [App\Http\Controllers\ClubManagementController::class, 'createStudentAccount'])->name('admin.create.student');
});

/*
|--------------------------------------------------------------------------
| Club Management Routes (for Club Owners/Executive Board)
|--------------------------------------------------------------------------
*/

Route::prefix('club/{club}')->middleware('simple_auth')->group(function () {
    // Quản lý thành viên (chỉ chủ CLB và ban cán sự)
    Route::middleware('club_role:owner,executive_board')->group(function () {
        
        Route::post('/members', [App\Http\Controllers\ClubManagementController::class, 'addMember'])->name('club.members.add');
        Route::delete('/members/{user}', [App\Http\Controllers\ClubManagementController::class, 'removeMember'])->name('club.members.remove');
    });
});

/*
|--------------------------------------------------------------------------
| Club Manager Routes
|--------------------------------------------------------------------------
|
| Routes for club managers to manage their clubs
|
*/

Route::prefix('club-manager')->middleware(['auth', 'club_manager'])->group(function () {
    Route::get('/', [ClubManagerController::class, 'dashboard'])->name('club-manager.dashboard');
    Route::get('/dashboard', [ClubManagerController::class, 'dashboard'])->name('club-manager.dashboard');
    
    // Quản lý CLB
    Route::get('/clubs', [ClubManagerController::class, 'clubs'])->name('club-manager.clubs');
    Route::get('/clubs/{club}', [ClubManagerController::class, 'showClub'])->name('club-manager.clubs.show');
    
    // Quản lý thành viên
    Route::get('/clubs/{club}/members', [ClubManagerController::class, 'members'])->name('club-manager.clubs.members');
});
