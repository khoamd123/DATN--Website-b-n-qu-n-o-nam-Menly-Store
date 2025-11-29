<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FundSettlementController as OldController;
use Illuminate\Http\Request;

class FundSettlementController extends Controller
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

    public function create($fundRequest)
    {
        return $this->oldController->create($fundRequest);
    }

    public function store(Request $request, $fundRequest)
    {
        return $this->oldController->store($request, $fundRequest);
    }

    public function show($fundRequest)
    {
        return $this->oldController->show($fundRequest);
    }
}



