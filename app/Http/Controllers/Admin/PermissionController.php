<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PermissionController as OldPermissionController;
use App\Http\Controllers\AdminController as OldAdminController;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $oldController;
    protected $oldAdminController;

    public function __construct()
    {
        $this->oldController = new OldPermissionController();
        $this->oldAdminController = new OldAdminController();
    }

    public function index()
    {
        return $this->oldAdminController->permissionsManagement();
    }

    public function detailed()
    {
        return $this->oldController->index();
    }

    public function addToClub(Request $request)
    {
        return $this->oldController->addToClub($request);
    }

    public function updateUserPermissions(Request $request)
    {
        return $this->oldController->updateUserPermissions($request);
    }

    public function getUserPermissions(Request $request)
    {
        return $this->oldController->getUserPermissions($request);
    }

    public function getUserPosition(Request $request)
    {
        return $this->oldController->getUserPosition($request);
    }

    public function updateUser(Request $request, $id)
    {
        return $this->oldAdminController->updateUserPermissions($request, $id);
    }
}



