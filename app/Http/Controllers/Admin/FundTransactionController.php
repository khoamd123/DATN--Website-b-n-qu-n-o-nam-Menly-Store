<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FundTransactionController as OldController;
use Illuminate\Http\Request;

class FundTransactionController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index($fund)
    {
        return $this->oldController->index($fund);
    }

    public function create($fund)
    {
        return $this->oldController->create($fund);
    }

    public function store(Request $request, $fund)
    {
        return $this->oldController->store($request, $fund);
    }

    public function show($fund, $transaction)
    {
        return $this->oldController->show($fund, $transaction);
    }

    public function edit($fund, $transaction)
    {
        return $this->oldController->edit($fund, $transaction);
    }

    public function update(Request $request, $fund, $transaction)
    {
        return $this->oldController->update($request, $fund, $transaction);
    }

    public function destroy($fund, $transaction)
    {
        return $this->oldController->destroy($fund, $transaction);
    }

    public function approve(Request $request, $fund, $transaction)
    {
        return $this->oldController->approve($request, $fund, $transaction);
    }

    public function approvePartial(Request $request, $fund, $transaction)
    {
        return $this->oldController->approvePartial($request, $fund, $transaction);
    }

    public function reject(Request $request, $fund, $transaction)
    {
        return $this->oldController->reject($request, $fund, $transaction);
    }

    public function cancel(Request $request, $fund, $transaction)
    {
        return $this->oldController->cancel($request, $fund, $transaction);
    }

    public function exportInvoice($fund, $transaction)
    {
        return $this->oldController->exportInvoice($fund, $transaction);
    }
}



