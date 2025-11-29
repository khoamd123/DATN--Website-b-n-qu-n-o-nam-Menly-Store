<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController as OldAdminController;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldAdminController();
    }

    public function index()
    {
        return $this->oldController->commentsManagement();
    }

    public function show($type, $id)
    {
        return $this->oldController->commentsShow($type, $id);
    }

    public function delete($type, $id)
    {
        return $this->oldController->deleteComment($type, $id);
    }
}



