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
    Route::get('/users-simple', [AdminController::class, 'usersSimple'])->name('admin.users.simple');
    Route::patch('/users/{id}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');
    
        // Phân quyền
        Route::get('/permissions', [AdminController::class, 'permissionsSimple'])->name('admin.permissions');
        Route::get('/permissions-detailed', [App\Http\Controllers\PermissionController::class, 'index'])->name('admin.permissions.detailed');
        Route::post('/permissions/update', [App\Http\Controllers\PermissionController::class, 'updateUserPermissions'])->name('admin.permissions.update');
        Route::get('/permissions/user-permissions', [App\Http\Controllers\PermissionController::class, 'getUserPermissions'])->name('admin.permissions.user-permissions');
    
    // Quản lý câu lạc bộ
    Route::get('/clubs', [AdminController::class, 'clubs'])->name('admin.clubs');
    Route::patch('/clubs/{id}/status', [AdminController::class, 'updateClubStatus'])->name('admin.clubs.status');
    
    // Tài liệu học tập
    Route::get('/learning-materials', [AdminController::class, 'learningMaterials'])->name('admin.learning-materials');
    
    // Quản lý quỹ
    Route::get('/fund-management', [AdminController::class, 'fundManagement'])->name('admin.fund-management');
    
    // Kế hoạch
    Route::get('/plans-schedule', [AdminController::class, 'plansSchedule'])->name('admin.plans-schedule');
    
    // Bài viết
    Route::get('/posts', [AdminController::class, 'postsManagement'])->name('admin.posts');
    Route::patch('/posts/{id}/status', [AdminController::class, 'updatePostStatus'])->name('admin.posts.status');
    
    // Bình luận
    Route::get('/comments', [AdminController::class, 'commentsManagement'])->name('admin.comments');
    Route::delete('/comments/{type}/{id}', [AdminController::class, 'deleteComment'])->name('admin.comments.delete');
    
    // Phân quyền
    Route::get('/permissions', [AdminController::class, 'permissionsManagement'])->name('admin.permissions');
    Route::get('/permissions-simple', [AdminController::class, 'permissionsSimple'])->name('admin.permissions.simple');
    Route::patch('/permissions/{id}/user', [AdminController::class, 'updateUserPermissions'])->name('admin.permissions.user');
    
    
    // Quản lý CLB cho Admin
    Route::get('/clubs-management', [App\Http\Controllers\ClubManagementController::class, 'index'])->name('admin.clubs.management');
    Route::get('/clubs/create', [App\Http\Controllers\ClubManagementController::class, 'create'])->name('admin.clubs.create');
    Route::post('/clubs', [App\Http\Controllers\ClubManagementController::class, 'store'])->name('admin.clubs.store');
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