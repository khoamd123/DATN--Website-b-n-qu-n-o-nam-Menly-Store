<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
use App\Models\NotificationRead;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index(Request $request)
    {
        return $this->oldController->notifications($request);
    }

    public function markAsRead(Request $request, $notification)
    {
        $user = $this->oldController->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }
        
        $notificationModel = \App\Models\Notification::findOrFail($notification);
        
        // Tạo hoặc cập nhật record trong notification_reads
        \App\Models\NotificationRead::updateOrCreate(
            [
                'notification_id' => $notificationModel->id,
                'user_id' => $user->id,
            ],
            [
                'is_read' => 1,
            ]
        );
        
        return redirect()->back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function show($notification)
    {
        $user = $this->oldController->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        try {
            $notificationModel = \App\Models\Notification::with('sender')->findOrFail($notification);
            
            // Kiểm tra quyền truy cập
            $hasAccess = $notificationModel->targets()
                ->where(function($query) use ($user) {
                    $query->where(function($q) use ($user) {
                        $q->where('target_type', 'user')->where('target_id', $user->id);
                    })
                    ->orWhere('target_type', 'all')
                    ->orWhere(function($q) use ($user) {
                        $q->where('target_type', 'club')
                          ->whereIn('target_id', $user->clubs->pluck('id')->toArray());
                    });
                })
                ->exists();

            if (!$hasAccess) {
                return redirect()->route('student.notifications.index')->with('error', 'Bạn không có quyền truy cập thông báo này.');
            }
            
            // Đánh dấu thông báo là đã đọc
            \App\Models\NotificationRead::updateOrCreate(
                [
                    'notification_id' => $notificationModel->id,
                    'user_id' => $user->id,
                ],
                [
                    'is_read' => 1,
                ]
            );
            
            // Load nội dung đầy đủ nếu thông báo liên quan đến Post (announcement)
            $relatedPost = null;
            $relatedType = strtolower($notificationModel->related_type ?? '');
            $type = strtolower($notificationModel->type ?? '');
            
            if ($notificationModel->related_id && 
                (($relatedType === 'app\\models\\post' || $relatedType === 'post' || $type === 'post' || $type === 'announcement'))) {
                try {
                    $relatedPost = \App\Models\Post::with(['user', 'club'])->find($notificationModel->related_id);
                    
                    // Nếu thông báo là về bài viết mới (type = 'post' và related post type = 'post'), redirect đến trang bài viết
                    if ($relatedPost && $type === 'post' && $relatedPost->type === 'post') {
                        return redirect()->route('student.posts.show', $relatedPost->id);
                    }
                } catch (\Exception $e) {
                    // Ignore if post not found
                }
            }

            // Hiển thị trang chi tiết thông báo (không redirect)
            return view('student.notifications.show', compact('notificationModel', 'user', 'relatedPost'));
        } catch (\Exception $e) {
            return redirect()->route('student.notifications.index')->with('error', 'Không tìm thấy thông báo.');
        }
    }
}



