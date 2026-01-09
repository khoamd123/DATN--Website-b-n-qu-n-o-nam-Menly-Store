<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
use App\Models\NotificationRead;
use App\Models\Event;
use App\Models\Post;
use App\Models\ClubJoinRequest;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index()
    {
        return $this->oldController->notifications();
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

    public function view(Request $request, $notification)
    {
        $user = $this->oldController->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $notificationModel = \App\Models\Notification::findOrFail($notification);

        \App\Models\NotificationRead::updateOrCreate(
            [
                'notification_id' => $notificationModel->id,
                'user_id' => $user->id,
            ],
            ['is_read' => 1]
        );

        $route = route('student.notifications.index');
        $relatedType = $notificationModel->related_type;
        if ($relatedType === 'App\\Models\\Event' || $relatedType === Event::class || strtolower($relatedType) === 'event') {
            if ($notificationModel->related_id) {
                $route = route('student.events.show', $notificationModel->related_id);
            }
        } elseif ($relatedType === 'App\\Models\\Post' || $relatedType === Post::class || strtolower($relatedType) === 'post') {
            if ($notificationModel->related_id) {
                $route = route('student.posts.show', $notificationModel->related_id);
            }
        } elseif ($relatedType === 'ClubJoinRequest') {
            $joinReq = \App\Models\ClubJoinRequest::find($notificationModel->related_id);
            if ($joinReq) {
                $route = route('student.club-management.join-requests', $joinReq->club_id);
            }
        }

        return redirect($route);
    }

    public function markAllRead(Request $request)
    {
        $user = $this->oldController->checkStudentAuth();
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $userClubIds = $user->clubs->pluck('id')->toArray();
        $notifications = \App\Models\Notification::whereHas('targets', function($query) use ($user, $userClubIds) {
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
            ->whereNull('deleted_at')
            ->get();

        foreach ($notifications as $notification) {
            NotificationRead::updateOrCreate(
                ['notification_id' => $notification->id, 'user_id' => $user->id],
                ['is_read' => 1]
            );
        }

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}



