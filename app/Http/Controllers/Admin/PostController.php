<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController as OldAdminController;
use App\Http\Controllers\PostController as OldPostController;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $oldController;
    protected $oldPostController;

    public function __construct()
    {
        $this->oldController = new OldAdminController();
        $this->oldPostController = new OldPostController();
    }

    public function index(Request $request)
    {
        return $this->oldController->postsManagement($request);
    }

    public function create()
    {
        return $this->oldController->postsCreate();
    }

    public function store(Request $request)
    {
        return $this->oldController->postsStore($request);
    }

    public function show($id)
    {
        return $this->oldController->postsShow($id);
    }

    public function edit($id)
    {
        return $this->oldController->postsEdit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->oldController->postsUpdate($request, $id);
    }

    public function destroy($id)
    {
        return $this->oldController->deletePost($id);
    }

    public function updateStatus(Request $request, $id)
    {
        return $this->oldController->updatePostStatus($request, $id);
    }

    public function trash()
    {
        return $this->oldController->postsTrash();
    }

    public function restore(Request $request, $id)
    {
        return $this->oldController->restorePost($request, $id);
    }

    public function forceDelete($id)
    {
        return $this->oldController->forceDeletePost($id);
    }

    public function uploadEditorImage(Request $request)
    {
        return $this->oldPostController->uploadEditorImage($request);
    }

    public function generateSamplePosts(Request $request)
    {
        return $this->oldController->generateSamplePostsForAllClubs($request);
    }
}



