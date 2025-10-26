<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

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
    Route::get('/clubs', [App\Http\Controllers\AdminController::class, 'clubs'])->name('admin.clubs');
    Route::get('/clubs/create', [App\Http\Controllers\AdminController::class, 'createClub'])->name('admin.clubs.create');
    Route::post('/clubs', [App\Http\Controllers\AdminController::class, 'storeClub'])->name('admin.clubs.store');
    Route::get('/clubs/{club}', [AdminController::class, 'showClub'])->name('admin.clubs.show');
    Route::get('/clubs/{club}/edit', [App\Http\Controllers\AdminController::class, 'editClub'])->name('admin.clubs.edit');
    Route::put('/clubs/{club}', [App\Http\Controllers\AdminController::class, 'updateClub'])->name('admin.clubs.update');
    Route::get('/clubs/{id}/members', [App\Http\Controllers\AdminController::class, 'clubMembers'])->name('admin.clubs.members');
    Route::patch('/clubs/{id}/status', [App\Http\Controllers\AdminController::class, 'updateClubStatus'])->name('admin.clubs.status');
    Route::delete('/clubs/{id}', [App\Http\Controllers\AdminController::class, 'deleteClub'])->name('admin.clubs.delete');

    // Quản lý thành viên trong CLB
    Route::prefix('clubs/{club}/members')->name('admin.clubs.members.')->group(function () {
        Route::post('/add', [AdminController::class, 'addMember'])->name('add');
        Route::post('/{member}/approve', [AdminController::class, 'approveMember'])->name('approve');
        Route::delete('/{member}/reject', [AdminController::class, 'rejectMember'])->name('reject');
        Route::delete('/{member}/remove', [AdminController::class, 'removeMember'])->name('remove');
        Route::post('/bulk-update', [AdminController::class, 'bulkUpdateMembers'])->name('bulk-update');
    });
    
    // Tài liệu học tập
    Route::get('/learning-materials', [AdminController::class, 'learningMaterials'])->name('admin.learning-materials');
    
    // Quản lý quỹ
    Route::get('/fund-management', [App\Http\Controllers\AdminController::class, 'fundManagement'])->name('admin.fund-management');
    Route::post('/fund-management', [App\Http\Controllers\AdminController::class, 'storeFund'])->name('admin.fund-management.store');
    Route::post('/fund-management/{fund}/approve', [App\Http\Controllers\AdminController::class, 'approveFund'])->name('admin.fund-management.approve');
    Route::get('/fund-management/{fund}/json', [App\Http\Controllers\AdminController::class, 'fundJson'])->name('admin.fund-management.json');
    Route::get('/fund-management/{fund}', [App\Http\Controllers\AdminController::class, 'showFund'])->name('admin.fund-management.show');
    Route::get('/fund-management/{fund}/edit', [App\Http\Controllers\AdminController::class, 'editFund'])->name('admin.fund-management.edit');
    Route::put('/fund-management/{fund}', [App\Http\Controllers\AdminController::class, 'updateFund'])->name('admin.fund-management.update');
    Route::delete('/fund-management/{fund}', [App\Http\Controllers\AdminController::class, 'destroyFund'])->name('admin.fund-management.destroy');

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
