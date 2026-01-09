<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index()
    {
        return $this->oldController->posts();
    }

    public function create()
    {
        return $this->oldController->createPost();
    }

    public function createClubPost($club)
    {
        return $this->oldController->createClubPost($club);
    }

    public function store(Request $request)
    {
        return $this->oldController->storePost($request);
    }

    public function show($id)
    {
        return $this->oldController->showPost($id);
    }

    public function edit($id)
    {
        return $this->oldController->editPost($id);
    }

    public function update(Request $request, $id)
    {
        return $this->oldController->updatePost($request, $id);
    }

    public function delete($id)
    {
        return $this->oldController->deletePost($id);
    }

    public function myPosts()
    {
        return $this->oldController->myPosts();
    }

    public function addComment(Request $request, $id)
    {
        return $this->oldController->addPostComment($request, $id);
    }

    public function markAnnouncementViewed(Request $request)
    {
        return $this->oldController->markAnnouncementViewed($request);
    }

    public function uploadEditorImage(Request $request)
    {
        return $this->oldController->uploadEditorImage($request);
    }

    public function createAnnouncement()
    {
        return $this->oldController->createAnnouncement();
    }

    public function storeAnnouncement(Request $request)
    {
        return $this->oldController->storeAnnouncement($request);
    }

    public function editAnnouncement($id)
    {
        return $this->oldController->editAnnouncement($id);
    }

    public function updateAnnouncement(Request $request, $id)
    {
        return $this->oldController->updateAnnouncement($request, $id);
    }
}



