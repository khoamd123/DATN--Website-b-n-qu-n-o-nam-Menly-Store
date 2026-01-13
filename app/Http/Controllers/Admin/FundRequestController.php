<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FundRequestController as OldController;
use App\Models\FundRequest;
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

    public function show(FundRequest $fundRequest)
    {
        return $this->oldController->show($fundRequest);
    }

    public function edit(FundRequest $fundRequest)
    {
        return $this->oldController->edit($fundRequest);
    }

    public function update(Request $request, FundRequest $fundRequest)
    {
        return $this->oldController->update($request, $fundRequest);
    }

    public function destroy(FundRequest $fundRequest)
    {
        // Admin không được phép xóa yêu cầu cấp kinh phí
        return redirect()->back()->with('error', 'Bạn không có quyền xóa yêu cầu cấp kinh phí!');
    }

    public function approve(Request $request, FundRequest $fundRequest)
    {
        return $this->oldController->approve($request, $fundRequest);
    }

    public function reject(Request $request, FundRequest $fundRequest)
    {
        return $this->oldController->reject($request, $fundRequest);
    }

    public function resetStatus(FundRequest $fundRequest)
    {
        $fundRequest->update(['status' => 'pending']);
        return redirect()->route('admin.fund-requests.show', $fundRequest)->with('success', 'Đã reset trạng thái về "Chờ duyệt"');
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



