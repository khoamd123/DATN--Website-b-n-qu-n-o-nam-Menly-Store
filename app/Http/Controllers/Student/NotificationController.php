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
}



