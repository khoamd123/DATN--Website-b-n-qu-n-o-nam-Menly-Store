<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TrashController as OldController;
use Illuminate\Http\Request;

class TrashController extends Controller
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

    public function restore(Request $request)
    {
        return $this->oldController->restore($request);
    }

    public function forceDelete(Request $request)
    {
        return $this->oldController->forceDelete($request);
    }

    public function restoreAll(Request $request)
    {
        return $this->oldController->restoreAll($request);
    }

    public function forceDeleteAll(Request $request)
    {
        return $this->oldController->forceDeleteAll($request);
    }
}



