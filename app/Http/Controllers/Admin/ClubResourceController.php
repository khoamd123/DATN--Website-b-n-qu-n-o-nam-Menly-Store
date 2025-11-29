<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ClubResourceController as OldController;
use Illuminate\Http\Request;

class ClubResourceController extends Controller
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

    public function create()
    {
        return $this->oldController->create();
    }

    public function store(Request $request)
    {
        return $this->oldController->store($request);
    }

    public function show($id)
    {
        return $this->oldController->show($id);
    }

    public function edit($id)
    {
        return $this->oldController->edit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->oldController->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->oldController->destroy($id);
    }

    public function trash()
    {
        return $this->oldController->trash();
    }

    public function download($id)
    {
        return $this->oldController->download($id);
    }

    public function restore(Request $request, $id)
    {
        return $this->oldController->restore($request, $id);
    }

    public function forceDelete($id)
    {
        return $this->oldController->forceDelete($id);
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



