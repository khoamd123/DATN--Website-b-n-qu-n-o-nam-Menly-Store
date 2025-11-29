<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController as OldAdminController;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldAdminController();
    }

    public function index(Request $request)
    {
        return $this->oldController->dashboard($request);
    }

    public function search(Request $request)
    {
        return $this->oldController->search($request);
    }

    public function profile(Request $request)
    {
        return $this->oldController->profile($request);
    }

    public function settings(Request $request)
    {
        return $this->oldController->settings($request);
    }

    public function plansSchedule(Request $request)
    {
        return $this->oldController->plansSchedule($request);
    }
}



