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
Route::get('/admin-login', function () {
    return view('auth.login');
})->name('admin.login');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Routes for user authentication
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes for admin panel management
|
*/

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Quản lý người dùng
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::patch('/users/{id}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');
    
    // Quản lý câu lạc bộ
    Route::get('/clubs', [AdminController::class, 'clubs'])->name('admin.clubs');
    Route::patch('/clubs/{id}/status', [AdminController::class, 'updateClubStatus'])->name('admin.clubs.status');
    
    // Tài nguyên CLB
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
    Route::patch('/permissions/{id}/user', [AdminController::class, 'updateUserPermissions'])->name('admin.permissions.user');
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
