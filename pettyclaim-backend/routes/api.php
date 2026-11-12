<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Karyawan\KaryawanClaimController;
use App\Http\Controllers\Karyawan\KaryawanFinanceController;
use App\Http\Controllers\Manager\ManagerClaimController;

/*
|--------------------------------------------------------------------------
| API Routes - PettyClaim Mobile Engine (Flutter)
|--------------------------------------------------------------------------
*/

// 1. Endpoint Publik
Route::post('/login', [AuthController::class, 'login']);
Route::get('/limit-kas', [SettingController::class, 'getLimit']);

// Pindahkan ke Endpoint Publik agar bisa diakses Flutter via ?email=...
Route::get('/profile', [AuthController::class, 'getProfile']);
Route::post('/change-password', [AuthController::class, 'changePassword']);

// Endpoint scan struk, submit klaim, dan ambil riwayat klaim
Route::post('/claims/process-scan', [KaryawanClaimController::class, 'processScan']);
Route::post('/claims', [KaryawanClaimController::class, 'store']);
Route::get('/claims', [KaryawanClaimController::class, 'index']);

// Endpoint wallet & manager publik
Route::get('/wallet/summary', [KaryawanFinanceController::class, 'getFinanceSummary']);
Route::get('/manager/claims', [ManagerClaimController::class, 'index']);
Route::post('/claims/{id}/verify', [ManagerClaimController::class, 'verifyByFinance']);
Route::post('/claims/{id}/approve', [ManagerClaimController::class, 'approveOrReject']);
Route::post('/claims/approve', [ManagerClaimController::class, 'syncApprove']);


// 2. Endpoint Terproteksi Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Autentikasi & Cek User ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // --- Firebase Cloud Messaging (FCM Token) ---
    Route::post('/user/fcm-token', function (Request $request) {
        $request->validate(['fcm_token' => 'required|string']);
        
        $user = $request->user(); 
        if ($user) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }
        return response()->json(['success' => true, 'message' => 'FCM Token berhasil disimpan.'], 200);
    });

    // --- Modul Karyawan - Keuangan & Dompet ---
    Route::post('/wallet/withdraw', [KaryawanFinanceController::class, 'withdraw']);

});