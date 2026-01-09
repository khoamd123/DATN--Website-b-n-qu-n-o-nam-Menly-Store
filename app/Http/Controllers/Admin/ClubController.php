<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController as OldAdminController;
use App\Http\Controllers\ClubManagementController as OldClubManagementController;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    protected $oldController;
    protected $oldClubManagementController;

    public function __construct()
    {
        $this->oldController = new OldAdminController();
        $this->oldClubManagementController = new OldClubManagementController();
    }

    public function index(Request $request)
    {
        return $this->oldController->clubs($request);
    }

    public function create()
    {
        return $this->oldController->clubsCreate();
    }

    public function store(Request $request)
    {
        return $this->oldController->clubsStore($request);
    }

    public function show($club)
    {
        return $this->oldController->showClub($club);
    }

    public function edit($club)
    {
        return $this->oldController->editClub($club);
    }

    public function update(Request $request, $club)
    {
        return $this->oldController->updateClub($request, $club);
    }

    public function destroy($id)
    {
        return $this->oldController->deleteClub($id);
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->oldController->updateClubStatus($request, $id);
    }

    public function members($club)
    {
        return $this->oldController->clubMembers($club);
    }

    public function addMember(Request $request, $club)
    {
        return $this->oldClubManagementController->addMember($request, $club);
    }

    public function approveMember(Request $request, $club, $member)
    {
        return $this->oldController->approveMember($request, $club, $member);
    }

    public function rejectMember(Request $request, $club, $member)
    {
        return $this->oldController->rejectMember($request, $club, $member);
    }

    public function removeMember(Request $request, $club, $member)
    {
        return $this->oldController->removeMember($request, $club, $member);
    }

    public function updateMemberRole(Request $request, $club, $member)
    {
        return $this->oldController->updateMemberRole($request, $club, $member);
    }

    public function bulkUpdateMembers(Request $request, $club)
    {
        return $this->oldController->bulkUpdateMembers($request, $club);
    }

    public function generateSamplePosts(Request $request, $club)
    {
        return $this->oldController->generateSamplePostsForClub($request, $club);
    }

    public function management()
    {
        return $this->oldClubManagementController->index();
    }

    public function storeManagement(Request $request)
    {
        return $this->oldClubManagementController->store($request);
    }

    public function createStudentAccount(Request $request)
    {
        return $this->oldClubManagementController->createStudentAccount($request);
    }
}



