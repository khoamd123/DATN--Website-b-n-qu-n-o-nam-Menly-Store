<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentProfileController as OldController;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index()
    {
        return $this->oldController->index();
    }

    public function edit()
    {
        return $this->oldController->edit();
    }

    public function update(Request $request)
    {
        return $this->oldController->update($request);
    }
}



