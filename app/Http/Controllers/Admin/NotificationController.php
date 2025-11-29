<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController as OldAdminController;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldAdminController();
    }

    public function index(Request $request)
    {
        return $this->oldController->notifications($request);
    }
}



