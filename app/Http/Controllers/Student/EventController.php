<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index()
    {
        return $this->oldController->events();
    }

    public function create()
    {
        return $this->oldController->createEvent();
    }

    public function store(Request $request)
    {
        return $this->oldController->storeEvent($request);
    }

    public function show($event)
    {
        return $this->oldController->showEvent($event);
    }

    public function manage()
    {
        return $this->oldController->manageEvents();
    }

    public function register(Request $request, $event)
    {
        return $this->oldController->registerEvent($request, $event);
    }

    public function cancelRegistration($event)
    {
        return $this->oldController->cancelRegistration($event);
    }

    public function restore(Request $request, $event)
    {
        return $this->oldController->restoreEvent($request, $event);
    }

    public function delete($event)
    {
        return $this->oldController->deleteEvent($event);
    }
}



