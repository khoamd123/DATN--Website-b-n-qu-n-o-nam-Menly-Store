<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FundController as OldFundController;
use Illuminate\Http\Request;

class FundController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldFundController();
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

    public function show($fund)
    {
        return $this->oldController->show($fund);
    }

    public function edit($fund)
    {
        return $this->oldController->edit($fund);
    }

    public function update(Request $request, $fund)
    {
        return $this->oldController->update($request, $fund);
    }

    public function destroy($fund)
    {
        return $this->oldController->destroy($fund);
    }

    public function fixAmount($fund)
    {
        $fundModel = \App\Models\Fund::find($fund);
        $fundModel->updateCurrentAmount();
        return redirect()->route('admin.funds.show', $fund)->with('success', 'Đã cập nhật số tiền!');
    }
}



