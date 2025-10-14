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
           Route::get('/permissions-demo', [App\Http\Controllers\PermissionController::class, 'demo'])->name('admin.permissions.demo');
           Route::post('/permissions/update', [App\Http\Controllers\PermissionController::class, 'updateUserPermissions'])->name('admin.permissions.update');
           Route::get('/permissions/user-permissions', [App\Http\Controllers\PermissionController::class, 'getUserPermissions'])->name('admin.permissions.user-permissions');
    
    // Quản lý câu lạc bộ
    Route::get('/clubs', [AdminController::class, 'clubs'])->name('admin.clubs');
    Route::patch('/clubs/{id}/status', [AdminController::class, 'updateClubStatus'])->name('admin.clubs.status');
    
    // Tài nguyên CLB - CRUD
    Route::get('/club-resources', [App\Http\Controllers\ClubResourceController::class, 'index'])->name('admin.club-resources.index');
    Route::get('/club-resources/create', [App\Http\Controllers\ClubResourceController::class, 'create'])->name('admin.club-resources.create');
    Route::post('/club-resources', [App\Http\Controllers\ClubResourceController::class, 'store'])->name('admin.club-resources.store');
    Route::get('/club-resources/{id}', [App\Http\Controllers\ClubResourceController::class, 'show'])->name('admin.club-resources.show');
    Route::get('/club-resources/{id}/edit', [App\Http\Controllers\ClubResourceController::class, 'edit'])->name('admin.club-resources.edit');
    Route::put('/club-resources/{id}', [App\Http\Controllers\ClubResourceController::class, 'update'])->name('admin.club-resources.update');
    Route::delete('/club-resources/{id}', [App\Http\Controllers\ClubResourceController::class, 'destroy'])->name('admin.club-resources.destroy');
    Route::get('/club-resources/{id}/download', [App\Http\Controllers\ClubResourceController::class, 'download'])->name('admin.club-resources.download');
    Route::post('/club-resources/{id}/restore', [App\Http\Controllers\ClubResourceController::class, 'restore'])->name('admin.club-resources.restore');
    
    // Quản lý quỹ
    Route::get('/fund-management', [AdminController::class, 'fundManagement'])->name('admin.fund-management');
    
    // Kế hoạch
    Route::get('/plans-schedule', [AdminController::class, 'plansSchedule'])->name('admin.plans-schedule');
    
    // Bài viết - CRUD operations
    Route::get('/posts', [App\Http\Controllers\PostController::class, 'index'])->name('admin.posts');
    Route::get('/posts/create', [App\Http\Controllers\PostController::class, 'create'])->name('admin.posts.create');
    Route::post('/posts', [App\Http\Controllers\PostController::class, 'store'])->name('admin.posts.store');
    Route::get('/posts/{id}', [App\Http\Controllers\PostController::class, 'show'])->name('admin.posts.show');
    Route::get('/posts/{id}/edit', [App\Http\Controllers\PostController::class, 'edit'])->name('admin.posts.edit');
    Route::put('/posts/{id}', [App\Http\Controllers\PostController::class, 'update'])->name('admin.posts.update');
    Route::delete('/posts/{id}', [App\Http\Controllers\PostController::class, 'destroy'])->name('admin.posts.destroy');
    Route::patch('/posts/{id}/status', [App\Http\Controllers\PostController::class, 'updateStatus'])->name('admin.posts.status');
    Route::post('/posts/{id}/restore', [App\Http\Controllers\PostController::class, 'restore'])->name('admin.posts.restore');
    
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
        Route::get('/members', [App\Http\Controllers\ClubManagementController::class, 'manageMembers'])->name('club.members.manage');
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
    
    // Quản lý phân quyền
    Route::get('/clubs/{club}/permissions', [ClubManagerController::class, 'permissions'])->name('club-manager.clubs.permissions');
    Route::post('/clubs/{club}/members/{member}/permissions', [ClubManagerController::class, 'updateMemberPermissions'])->name('club-manager.clubs.members.permissions');
});

// Test Routes cho hệ thống phân quyền (tạm thời bỏ middleware để test)
Route::get('/club-test', [App\Http\Controllers\ClubTestController::class, 'testPage'])->name('club.test');
Route::post('/club/test/leader', [App\Http\Controllers\ClubTestController::class, 'testLeader'])->name('club.test.leader');
Route::post('/club/test/officer', [App\Http\Controllers\ClubTestController::class, 'testOfficer'])->name('club.test.officer');
Route::post('/club/test/member', [App\Http\Controllers\ClubTestController::class, 'testMember'])->name('club.test.member');
Route::get('/club/info/{club}', [App\Http\Controllers\ClubTestController::class, 'getClubInfo'])->name('club.info');

// Trang test tổng hợp
Route::get('/test-final', function () {
    return view('test-final');
})->name('test.final');
