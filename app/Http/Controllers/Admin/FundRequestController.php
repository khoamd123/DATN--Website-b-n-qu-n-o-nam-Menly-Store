<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FundRequestController as OldController;
use Illuminate\Http\Request;

class FundRequestController extends Controller
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

    public function show($fundRequest)
    {
        return $this->oldController->show($fundRequest);
    }

    public function edit($fundRequest)
    {
        return $this->oldController->edit($fundRequest);
    }

    public function update(Request $request, $fundRequest)
    {
        return $this->oldController->update($request, $fundRequest);
    }

    public function destroy($fundRequest)
    {
        return $this->oldController->destroy($fundRequest);
    }

    public function approve(Request $request, $fundRequest)
    {
        return $this->oldController->approve($request, $fundRequest);
    }

    public function reject(Request $request, $fundRequest)
    {
        return $this->oldController->reject($request, $fundRequest);
    }

    public function resetStatus($fundRequest)
    {
        $fundRequestModel = \App\Models\FundRequest::find($fundRequest);
        if ($fundRequestModel) {
            $fundRequestModel->update(['status' => 'pending']);
            return redirect()->route('admin.fund-requests.show', $fundRequest)->with('success', 'Đã reset trạng thái về "Chờ duyệt"');
        }
        return redirect()->back()->with('error', 'Không tìm thấy yêu cầu');
    }

    public function batchApproval()
    {
        return $this->oldController->batchApproval();
    }

    public function processBatchApproval(Request $request)
    {
        return $this->oldController->processBatchApproval($request);
    }
}



