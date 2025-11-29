<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
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
        return $this->oldController->markNotificationAsRead($request, $notification);
    }
}



