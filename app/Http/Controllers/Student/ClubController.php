<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index(Request $request)
    {
        return $this->oldController->clubs($request);
    }

    public function ajaxSearch(Request $request)
    {
        return $this->oldController->ajaxSearchClubs($request);
    }

    public function create()
    {
        return $this->oldController->createClub();
    }

    public function store(Request $request)
    {
        return $this->oldController->storeClub($request);
    }

    public function show($club)
    {
        return $this->oldController->showClub($club);
    }

    public function join(Request $request, $club)
    {
        return $this->oldController->joinClub($request, $club);
    }

    public function leave($club)
    {
        return $this->oldController->leaveClub($club);
    }

    public function cancelJoinRequest($club)
    {
        return $this->oldController->cancelJoinRequest($club);
    }
}



