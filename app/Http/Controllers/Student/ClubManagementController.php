<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController as OldController;
use Illuminate\Http\Request;

class ClubManagementController extends Controller
{
    protected $oldController;

    public function __construct()
    {
        $this->oldController = new OldController();
    }

    public function index()
    {
        return $this->oldController->clubManagement();
    }

    public function reports()
    {
        return $this->oldController->clubReports();
    }

    public function joinRequests($club)
    {
        return $this->oldController->clubJoinRequests($club);
    }

    public function approveJoinRequest(Request $request, $club, $requestId)
    {
        return $this->oldController->approveClubJoinRequest($request, $club, $requestId);
    }

    public function rejectJoinRequest(Request $request, $club, $requestId)
    {
        return $this->oldController->rejectClubJoinRequest($request, $club, $requestId);
    }

    public function manageMembers($club)
    {
        return $this->oldController->manageMembers($club);
    }

    public function showMember($club, $member)
    {
        return $this->oldController->showMember($club, $member);
    }

    public function updateMemberPermissions(Request $request, $club, $member)
    {
        return $this->oldController->updateMemberPermissions($request, $club, $member);
    }

    public function removeMember($club, $member)
    {
        return $this->oldController->removeMember($club, $member);
    }

    public function settings($club)
    {
        return $this->oldController->clubSettings($club);
    }

    public function updateSettings(Request $request, $club)
    {
        return $this->oldController->updateClubSettings($request, $club);
    }

    public function posts($club)
    {
        return $this->oldController->clubManagementPosts($club);
    }

    public function resources($club)
    {
        return $this->oldController->clubManagementResources($club);
    }

    public function createResource($club)
    {
        return $this->oldController->createResource($club);
    }

    public function storeResource(Request $request, $club)
    {
        return $this->oldController->storeResource($request, $club);
    }

    public function showResource($club, $resource)
    {
        return $this->oldController->showResource($club, $resource);
    }

    public function editResource($club, $resource)
    {
        return $this->oldController->editResource($club, $resource);
    }

    public function updateResource(Request $request, $club, $resource)
    {
        return $this->oldController->updateResource($request, $club, $resource);
    }

    public function fundTransactions()
    {
        return $this->oldController->fundTransactions();
    }

    public function fundTransactionCreate()
    {
        return $this->oldController->fundTransactionCreate();
    }

    public function fundTransactionStore(Request $request)
    {
        return $this->oldController->fundTransactionStore($request);
    }

    public function fundTransactionShow($transaction)
    {
        return $this->oldController->fundTransactionShow($transaction);
    }

    public function approveFundTransaction(Request $request, $transaction)
    {
        // approveFundTransaction trong StudentController nhận $transactionId
        return $this->oldController->approveFundTransaction($transaction);
    }

    public function rejectFundTransaction(Request $request, $transaction)
    {
        return $this->oldController->rejectFundTransaction($request, $transaction);
    }

    public function fundRequests()
    {
        return $this->oldController->fundRequests();
    }

    public function fundRequestCreate()
    {
        return $this->oldController->fundRequestCreate();
    }

    public function fundRequestStore(Request $request)
    {
        return $this->oldController->fundRequestStore($request);
    }

    public function fundRequestShow($id)
    {
        return $this->oldController->fundRequestShow($id);
    }

    public function fundRequestEdit($fundRequest)
    {
        // Convert FundRequest model to ID if needed
        $id = $fundRequest instanceof \App\Models\FundRequest ? $fundRequest->id : $fundRequest;
        return $this->oldController->fundRequestEdit($id);
    }

    public function fundRequestUpdate(Request $request, $fundRequest)
    {
        // Convert FundRequest model to ID if needed
        $id = $fundRequest instanceof \App\Models\FundRequest ? $fundRequest->id : $fundRequest;
        return $this->oldController->fundRequestUpdate($request, $id);
    }

    public function fundRequestResubmit(Request $request, $fundRequest)
    {
        // Convert FundRequest model to ID if needed
        $id = $fundRequest instanceof \App\Models\FundRequest ? $fundRequest->id : $fundRequest;
        return $this->oldController->fundRequestResubmit($request, $id);
    }

    // Fund Deposit methods
    public function showFundDeposit(Request $request)
    {
        return $this->oldController->showFundDeposit($request);
    }

    public function submitFundDeposit(Request $request)
    {
        return $this->oldController->submitFundDeposit($request);
    }

    // Payment QR management methods (for leaders)
    public function managePaymentQr(Request $request, $club)
    {
        return $this->oldController->managePaymentQr($request, $club);
    }

    public function storePaymentQr(Request $request, $club)
    {
        return $this->oldController->storePaymentQr($request, $club);
    }

    public function updatePaymentQr(Request $request, $club, $qr)
    {
        return $this->oldController->updatePaymentQr($request, $club, $qr);
    }

    public function deletePaymentQr(Request $request, $club, $qr)
    {
        return $this->oldController->deletePaymentQr($request, $club, $qr);
    }

    // Fund Deposit Requests (Danh sách yêu cầu nộp quỹ - cho Leader và Treasurer)
    public function fundDepositRequests(Request $request)
    {
        return $this->oldController->fundDepositRequests($request);
    }

    // Fund Deposit Bill (Xem bill chuyển khoản)
    public function fundDepositBill($transaction)
    {
        return $this->oldController->fundDepositBill($transaction);
    }
}



