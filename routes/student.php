<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ClubController;
use App\Http\Controllers\Student\EventController;
use App\Http\Controllers\Student\PostController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\ClubManagementController;

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
|
| Routes for student interface
| All routes are prefixed with /student
|
*/

Route::prefix('student')->name('student.')->middleware([\App\Http\Middleware\SimpleAuth::class])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Clubs
    Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
    Route::get('/clubs/ajax-search', [ClubController::class, 'ajaxSearch'])->name('clubs.ajax-search');
    Route::get('/clubs/create', [ClubController::class, 'create'])->name('clubs.create');
    Route::post('/clubs', [ClubController::class, 'store'])->name('clubs.store');
    Route::get('/clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');
    Route::post('/clubs/{club}/join', [ClubController::class, 'join'])->name('clubs.join');
    Route::delete('/clubs/{club}/leave', [ClubController::class, 'leave'])->name('clubs.leave');
    Route::delete('/clubs/{club}/cancel-join-request', [ClubController::class, 'cancelJoinRequest'])->name('clubs.cancel-join-request');
    
    // Events
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/manage', [EventController::class, 'manage'])->name('events.manage');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{event}/register', [EventController::class, 'register'])->name('events.register');
    Route::delete('/events/{event}/cancel-registration', [EventController::class, 'cancelRegistration'])->name('events.cancel-registration');
    Route::post('/events/{event}/restore', [EventController::class, 'restore'])->name('events.restore');
    Route::delete('/events/{event}', [EventController::class, 'delete'])->name('events.delete');
    
    // Posts
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'delete'])->name('posts.delete');
    Route::get('/my-posts', [PostController::class, 'myPosts'])->name('posts.my-posts');
    Route::post('/posts/{post}/comments', [PostController::class, 'addComment'])->name('posts.comment');
    Route::post('/posts/mark-announcement-viewed', [PostController::class, 'markAnnouncementViewed'])->name('posts.mark-announcement-viewed');
    Route::post('/posts/upload-image', [PostController::class, 'uploadEditorImage'])->name('posts.upload-image');
    Route::get('/clubs/{club}/posts/create', [PostController::class, 'createClubPost'])->name('clubs.posts.create');
    
    // Announcements
    Route::get('/announcements/create', [PostController::class, 'createAnnouncement'])->name('announcements.create');
    Route::post('/announcements', [PostController::class, 'storeAnnouncement'])->name('announcements.store');
    Route::get('/announcements/{announcement}/edit', [PostController::class, 'editAnnouncement'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [PostController::class, 'updateAnnouncement'])->name('announcements.update');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/{notification}/view', [NotificationController::class, 'view'])->name('notifications.view');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    
    // Contact
    Route::get('/contact', [DashboardController::class, 'contact'])->name('contact.index');
    
    // Club Management (for club leaders)
    Route::prefix('club-management')->name('club-management.')->middleware('club_role:leader,vice_president,treasurer')->group(function () {
        Route::get('/', [ClubManagementController::class, 'index'])->name('index');
        Route::get('/reports', [ClubManagementController::class, 'reports'])->name('reports');
        
        // Join Requests
        Route::get('/{club}/join-requests', [ClubManagementController::class, 'joinRequests'])->name('join-requests');
        Route::post('/{club}/join-requests/{request}/approve', [ClubManagementController::class, 'approveJoinRequest'])->name('join-requests.approve');
        Route::post('/{club}/join-requests/{request}/reject', [ClubManagementController::class, 'rejectJoinRequest'])->name('join-requests.reject');
        
        // Members
        Route::get('/{club}/members', [ClubManagementController::class, 'manageMembers'])->name('members');
        Route::post('/{club}/members/{member}/permissions', [ClubManagementController::class, 'updateMemberPermissions'])->name('permissions.update');
        Route::delete('/{club}/members/{member}', [ClubManagementController::class, 'removeMember'])->name('members.remove');
        
        // Settings
        Route::get('/{club}/settings', [ClubManagementController::class, 'settings'])->name('settings');
        Route::put('/{club}/settings', [ClubManagementController::class, 'updateSettings'])->name('settings.update');
        
        // Posts
        Route::get('/{club}/posts', [ClubManagementController::class, 'posts'])->name('posts');
        
        // Resources
        Route::get('/{club}/resources', [ClubManagementController::class, 'resources'])->name('resources');
        Route::get('/{club}/resources/create', [ClubManagementController::class, 'createResource'])->name('resources.create');
        Route::post('/{club}/resources', [ClubManagementController::class, 'storeResource'])->name('resources.store');
        Route::get('/{club}/resources/{resource}', [ClubManagementController::class, 'showResource'])->name('resources.show');
        Route::get('/{club}/resources/{resource}/edit', [ClubManagementController::class, 'editResource'])->name('resources.edit');
        Route::put('/{club}/resources/{resource}', [ClubManagementController::class, 'updateResource'])->name('resources.update');
        
        // Fund Transactions
        Route::get('/fund-transactions', [ClubManagementController::class, 'fundTransactions'])->name('fund-transactions');
        Route::get('/fund-transactions/create', [ClubManagementController::class, 'fundTransactionCreate'])->name('fund-transactions.create');
        Route::post('/fund-transactions', [ClubManagementController::class, 'fundTransactionStore'])->name('fund-transactions.store');
        Route::get('/fund-transactions/{transaction}', [ClubManagementController::class, 'fundTransactionShow'])->name('fund-transactions.show');
        Route::post('/fund-transactions/{transaction}/approve', [ClubManagementController::class, 'approveFundTransaction'])->name('fund-transactions.approve');
        Route::post('/fund-transactions/{transaction}/reject', [ClubManagementController::class, 'rejectFundTransaction'])->name('fund-transactions.reject');
        
        // Fund Requests
        Route::get('/fund-requests', [ClubManagementController::class, 'fundRequests'])->name('fund-requests');
        Route::get('/fund-requests/create', [ClubManagementController::class, 'fundRequestCreate'])->name('fund-requests.create');
        Route::post('/fund-requests', [ClubManagementController::class, 'fundRequestStore'])->name('fund-requests.store');
        Route::get('/fund-requests/{fundRequest}', [ClubManagementController::class, 'fundRequestShow'])->name('fund-requests.show');
        Route::get('/fund-requests/{fundRequest}/edit', [ClubManagementController::class, 'fundRequestEdit'])->name('fund-requests.edit');
        Route::put('/fund-requests/{fundRequest}', [ClubManagementController::class, 'fundRequestUpdate'])->name('fund-requests.update');
        Route::post('/fund-requests/{fundRequest}/resubmit', [ClubManagementController::class, 'fundRequestResubmit'])->name('fund-requests.resubmit');
    });
});

