<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\FundController;
use App\Http\Controllers\Admin\FundTransactionController;
use App\Http\Controllers\Admin\FundRequestController;
use App\Http\Controllers\Admin\FundSettlementController;
use App\Http\Controllers\Admin\ClubResourceController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\NotificationController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes for admin panel management
| All routes are prefixed with /admin
|
*/

Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\SimpleAuth::class, \App\Http\Middleware\SimpleAdmin::class])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Users Management
    Route::resource('users', UserController::class);
    Route::get('/users/next-student-id', [UserController::class, 'nextStudentId'])->name('users.next-student-id');
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Clubs Management
    Route::resource('clubs', ClubController::class);
    Route::patch('/clubs/{club}/status', [ClubController::class, 'updateStatus'])->name('clubs.status');
    Route::get('/clubs/{club}/members', [ClubController::class, 'members'])->name('clubs.members');
    Route::post('/clubs/{club}/members', [ClubController::class, 'addMember'])->name('clubs.members.add');
    Route::post('/clubs/{club}/members/{member}/approve', [ClubController::class, 'approveMember'])->name('clubs.members.approve');
    Route::delete('/clubs/{club}/members/{member}/reject', [ClubController::class, 'rejectMember'])->name('clubs.members.reject');
    Route::delete('/clubs/{club}/members/{member}/remove', [ClubController::class, 'removeMember'])->name('clubs.members.remove');
    Route::patch('/clubs/{club}/members/{member}/role', [ClubController::class, 'updateMemberRole'])->name('clubs.members.update-role');
    Route::post('/clubs/{club}/members/bulk-update', [ClubController::class, 'bulkUpdateMembers'])->name('clubs.members.bulk-update');
    Route::post('/clubs/{club}/generate-posts', [ClubController::class, 'generateSamplePosts'])->name('clubs.generate-posts');
    
    // Events Management
    Route::resource('events', EventController::class);
    Route::post('/events/{event}/approve', [EventController::class, 'approve'])->name('events.approve');
    Route::post('/events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');
    
    // Posts Management
    Route::resource('posts', PostController::class);
    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus'])->name('posts.status');
    Route::get('/posts-trash', [PostController::class, 'trash'])->name('posts.trash');
    Route::post('/posts/{post}/restore', [PostController::class, 'restore'])->name('posts.restore');
    Route::delete('/posts/{post}/force-delete', [PostController::class, 'forceDelete'])->name('posts.force-delete');
    Route::post('/posts/upload-image', [PostController::class, 'uploadEditorImage'])->name('posts.upload-image');
    Route::post('/posts/generate-sample', [PostController::class, 'generateSamplePosts'])->name('posts.generate-sample');
    
    // Comments Management
    Route::get('/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::get('/comments/{type}/{id}', [CommentController::class, 'show'])->name('comments.show');
    Route::delete('/comments/{type}/{id}', [CommentController::class, 'delete'])->name('comments.delete');
    
    // Permissions Management
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions-detailed', [PermissionController::class, 'detailed'])->name('permissions.detailed');
    Route::post('/permissions/add-to-club', [PermissionController::class, 'addToClub'])->name('permissions.add-to-club');
    Route::post('/permissions/update', [PermissionController::class, 'updateUserPermissions'])->name('permissions.update');
    Route::get('/permissions/user-permissions', [PermissionController::class, 'getUserPermissions'])->name('permissions.user-permissions');
    Route::get('/permissions/user-position', [PermissionController::class, 'getUserPosition'])->name('permissions.user-position');
    Route::patch('/permissions/{id}/user', [PermissionController::class, 'updateUser'])->name('permissions.user');
    
    // Funds Management
    Route::resource('funds', FundController::class);
    Route::get('/funds/{fund}/fix-amount', [FundController::class, 'fixAmount'])->name('funds.fix-amount');
    
    // Fund Transactions
    Route::prefix('funds/{fund}/transactions')->name('funds.transactions.')->group(function () {
        Route::get('/', [FundTransactionController::class, 'index'])->name('index');
        Route::get('/create', [FundTransactionController::class, 'create'])->name('create');
        Route::post('/', [FundTransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}', [FundTransactionController::class, 'show'])->name('show');
        Route::get('/{transaction}/edit', [FundTransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [FundTransactionController::class, 'update'])->name('update');
        Route::post('/{transaction}/approve', [FundTransactionController::class, 'approve'])->name('approve');
        Route::post('/{transaction}/approve-partial', [FundTransactionController::class, 'approvePartial'])->name('approve-partial');
        Route::post('/{transaction}/reject', [FundTransactionController::class, 'reject'])->name('reject');
        Route::post('/{transaction}/cancel', [FundTransactionController::class, 'cancel'])->name('cancel');
        Route::delete('/{transaction}', [FundTransactionController::class, 'destroy'])->name('destroy');
        Route::get('/{transaction}/invoice', [FundTransactionController::class, 'exportInvoice'])->name('invoice');
    });
    
    // Fund Requests
    Route::resource('fund-requests', FundRequestController::class);
    Route::post('/fund-requests/{fundRequest}/approve', [FundRequestController::class, 'approve'])->name('fund-requests.approve');
    Route::post('/fund-requests/{fundRequest}/reject', [FundRequestController::class, 'reject'])->name('fund-requests.reject');
    Route::get('/fund-requests/{fundRequest}/reset-status', [FundRequestController::class, 'resetStatus'])->name('fund-requests.reset-status');
    Route::get('/fund-requests/batch-approval', [FundRequestController::class, 'batchApproval'])->name('fund-requests.batch-approval');
    Route::post('/fund-requests/batch-approval/process', [FundRequestController::class, 'processBatchApproval'])->name('fund-requests.batch-approval.process');
    
    // Fund Settlements
    Route::get('/fund-settlements', [FundSettlementController::class, 'index'])->name('fund-settlements.index');
    Route::get('/fund-settlements/{fundRequest}/create', [FundSettlementController::class, 'create'])->name('fund-settlements.create');
    Route::post('/fund-settlements/{fundRequest}', [FundSettlementController::class, 'store'])->name('fund-settlements.store');
    Route::get('/fund-settlements/{fundRequest}/show', [FundSettlementController::class, 'show'])->name('fund-settlements.show');
    
    // Club Resources
    Route::resource('club-resources', ClubResourceController::class);
    Route::get('/club-resources/trash', [ClubResourceController::class, 'trash'])->name('club-resources.trash');
    Route::get('/club-resources/{id}/download', [ClubResourceController::class, 'download'])->name('club-resources.download');
    Route::post('/club-resources/{id}/restore', [ClubResourceController::class, 'restore'])->name('club-resources.restore');
    Route::delete('/club-resources/{id}/force-delete', [ClubResourceController::class, 'forceDelete'])->name('club-resources.force-delete');
    Route::post('/club-resources/restore-all', [ClubResourceController::class, 'restoreAll'])->name('club-resources.restore-all');
    Route::delete('/club-resources/force-delete-all', [ClubResourceController::class, 'forceDeleteAll'])->name('club-resources.force-delete-all');
    
    // Trash Management
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::post('/trash/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::post('/trash/force-delete', [TrashController::class, 'forceDelete'])->name('trash.force-delete');
    Route::post('/trash/restore-all', [TrashController::class, 'restoreAll'])->name('trash.restore-all');
    Route::post('/trash/force-delete-all', [TrashController::class, 'forceDeleteAll'])->name('trash.force-delete-all');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    
    // Search
    Route::get('/search', [DashboardController::class, 'search'])->name('search');
    
    // Profile & Settings
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    
    // Plans & Schedule
    Route::get('/plans-schedule', [DashboardController::class, 'plansSchedule'])->name('plans-schedule');
    
    // Club Management
    Route::get('/clubs-management', [ClubController::class, 'management'])->name('clubs.management');
    Route::post('/clubs-management', [ClubController::class, 'storeManagement'])->name('clubs.management.store');
    Route::post('/create-student', [ClubController::class, 'createStudentAccount'])->name('create.student');
});

