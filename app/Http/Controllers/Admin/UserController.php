<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController as OldAdminController;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldAdminController();
    }

    public function index(Request $request)
    {
        return $this->oldController->users($request);
    }

    public function create()
    {
        return $this->oldController->createUser();
    }

    public function store(Request $request)
    {
        return $this->oldController->storeUser($request);
    }

    public function show($id)
    {
        return $this->oldController->showUser($id);
    }

    public function edit($id)
    {
        return $this->oldController->createUser($id);
    }

    public function update(Request $request, $id)
    {
        return $this->oldController->updateUser($request, $id);
    }

    public function destroy($id)
    {
        return $this->oldController->deleteUser($id);
    }

    public function nextStudentId()
    {
        return $this->oldController->nextStudentId();
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->oldController->updateUserStatus($request, $id);
    }

    public function resetPassword(Request $request, $id)
    {
        return $this->oldController->resetUserPassword($request, $id);
    }
}



