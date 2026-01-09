<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController as OldAdminController;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldAdminController();
    }

    public function index(Request $request)
    {
        return $this->oldController->eventsIndex($request);
    }

    public function create()
    {
        return $this->oldController->eventsCreate();
    }

    public function store(Request $request)
    {
        return $this->oldController->eventsStore($request);
    }

    public function show($id)
    {
        return $this->oldController->eventsShow($id);
    }

    public function edit($id)
    {
        return $this->oldController->eventsEdit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->oldController->eventsUpdate($request, $id);
    }

    public function destroy($id)
    {
        return $this->oldController->deleteEvent($id);
    }

    public function approve(Request $request, $event)
    {
        return $this->oldController->eventsApprove($event);
    }

    public function cancel(Request $request, $event)
    {
        return $this->oldController->eventsCancel($request, $event);
    }
}



