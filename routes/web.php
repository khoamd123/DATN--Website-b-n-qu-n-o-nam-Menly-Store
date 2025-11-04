<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubManagerController;

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
            Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('admin.users.show');
            Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
            Route::get('/users-simple', [AdminController::class, 'usersSimple'])->name('admin.users.simple');
            Route::patch('/users/{id}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');
            Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    
    // Phân quyền
    Route::get('/permissions', [AdminController::class, 'permissionsSimple'])->name('admin.permissions');
    Route::get('/permissions-detailed', [App\Http\Controllers\PermissionController::class, 'index'])->name('admin.permissions.detailed');
    Route::post('/permissions-detailed/add-to-club', [App\Http\Controllers\PermissionController::class, 'addToClub'])->name('admin.permissions.add-to-club');
    Route::post('/permissions/update', [App\Http\Controllers\PermissionController::class, 'updateUserPermissions'])->name('admin.permissions.update');
    Route::get('/permissions/user-permissions', [App\Http\Controllers\PermissionController::class, 'getUserPermissions'])->name('admin.permissions.user-permissions');
    
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
    
            // Quản lý câu lạc bộ
            Route::get('/clubs', [AdminController::class, 'clubs'])->name('admin.clubs');
            Route::get('/clubs/create', [AdminController::class, 'clubsCreate'])->name('admin.clubs.create');
            Route::post('/clubs', [AdminController::class, 'clubsStore'])->name('admin.clubs.store');
            Route::get('/clubs/{id}/edit', [AdminController::class, 'clubsEdit'])->name('admin.clubs.edit');
            Route::put('/clubs/{id}', [AdminController::class, 'clubsUpdate'])->name('admin.clubs.update');
            Route::patch('/clubs/{id}/status', [AdminController::class, 'updateClubStatus'])->name('admin.clubs.status');
            Route::delete('/clubs/{id}', [AdminController::class, 'deleteClub'])->name('admin.clubs.delete');
    
    // Tài liệu học tập
    Route::get('/learning-materials', [AdminController::class, 'learningMaterials'])->name('admin.learning-materials');
    Route::get('/learning-materials/create', [AdminController::class, 'learningMaterialsCreate'])->name('admin.learning-materials.create');
    Route::post('/learning-materials', [AdminController::class, 'learningMaterialsStore'])->name('admin.learning-materials.store');
    Route::get('/learning-materials/{id}/edit', [AdminController::class, 'learningMaterialsEdit'])->name('admin.learning-materials.edit');
    Route::put('/learning-materials/{id}', [AdminController::class, 'learningMaterialsUpdate'])->name('admin.learning-materials.update');
    
    // Quản lý quỹ
    Route::get('/fund-management', [AdminController::class, 'fundManagement'])->name('admin.fund-management');
    Route::post('/fund-management', [AdminController::class, 'fundManagementStore'])->name('admin.fund-management.store');
    
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
    Route::get('/posts/{id}', [AdminController::class, 'postsShow'])->name('admin.posts.show');
    Route::get('/posts/{id}/edit', [AdminController::class, 'postsEdit'])->name('admin.posts.edit');
    Route::put('/posts/{id}', [AdminController::class, 'postsUpdate'])->name('admin.posts.update');
    Route::patch('/posts/{id}/status', [AdminController::class, 'updatePostStatus'])->name('admin.posts.status');
    
    // Bình luận
    Route::get('/comments', [AdminController::class, 'commentsManagement'])->name('admin.comments');
    Route::get('/comments/{type}/{id}', [AdminController::class, 'commentsShow'])->name('admin.comments.show');
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