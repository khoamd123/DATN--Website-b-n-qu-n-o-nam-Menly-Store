<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index()
    {
        return $this->oldController->dashboard();
    }

    public function contact()
    {
        return $this->oldController->contact();
    }
}



