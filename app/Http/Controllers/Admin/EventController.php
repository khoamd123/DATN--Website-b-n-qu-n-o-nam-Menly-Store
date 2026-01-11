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
        // Extract ID from event (could be model or ID)
        $eventId = is_object($event) ? $event->id : $event;
        
        // Validate ID is numeric
        if (!is_numeric($eventId)) {
            return redirect()->back()->with('error', 'ID sự kiện không hợp lệ.');
        }
        
        return $this->oldController->eventsApprove($eventId);
    }

    public function cancel(Request $request, $event)
    {
        // Extract ID from event (could be model or ID)
        $eventId = is_object($event) ? $event->id : $event;
        return $this->oldController->eventsCancel($request, $eventId);
    }
}



