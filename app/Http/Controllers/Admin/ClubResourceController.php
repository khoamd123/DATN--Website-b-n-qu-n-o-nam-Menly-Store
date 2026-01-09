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

    public function index(Request $request)
    {
        return $this->oldController->index($request);
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

    public function trash(Request $request)
    {
        return $this->oldController->trash($request);
    }

    public function download($id)
    {
        return $this->oldController->download($id);
    }

    public function restore($id)
    {
        return $this->oldController->restore($id);
    }

    public function forceDelete($id)
    {
        return $this->oldController->forceDelete($id);
    }

    public function restoreAll()
    {
        return $this->oldController->restoreAll();
    }

    public function forceDeleteAll()
    {
        return $this->oldController->forceDeleteAll();
    }
}



