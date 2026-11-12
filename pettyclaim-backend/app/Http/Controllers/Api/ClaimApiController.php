<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Karyawan\KaryawanClaimController;
use App\Http\Controllers\Manager\ManagerClaimController;
use App\Services\FCMService;
use Illuminate\Http\Request;

class ClaimApiController extends Controller
{
    public function __construct(
        protected KaryawanClaimController $karyawanController,
        protected ManagerClaimController $managerController
    ) {}

    public function processScan(Request $request)
    {
        return $this->karyawanController->processScan($request);
    }

    public function index()
    {
        return $this->karyawanController->index();
    }

    public function store(Request $request, FCMService $fcmService)
    {
        return $this->karyawanController->store($request, $fcmService);
    }

    public function withdrawWallet(Request $request)
    {
        return $this->karyawanController->withdrawWallet($request);
    }

    public function indexForManager()
    {
        return $this->managerController->index();
    }

    public function verifyByFinance(Request $request, $id, FCMService $fcmService)
    {
        return $this->managerController->verifyByFinance($request, $id, $fcmService);
    }

    public function approveOrReject(Request $request, $id, FCMService $fcmService)
    {
        return $this->managerController->approveOrReject($request, $id, $fcmService);
    }

    public function syncApprove(Request $request)
    {
        return $this->managerController->syncApprove($request);
    }

    public function saveFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string']);
        $user = auth('sanctum')->user() ?? auth()->user() ?? \App\Models\User::first();
        if ($user) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }
        return response()->json(['success' => true, 'message' => 'FCM Token berhasil disimpan.'], 200);
    }
}