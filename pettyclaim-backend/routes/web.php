<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Admin\FinanceDashboardController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LimitSettingController;
use App\Http\Controllers\Admin\AdminClaimController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes - PettyClaim Admin Suite
|--------------------------------------------------------------------------
*/

// 1. Landing Page Publik
Route::get('/', function () {
    return view('landing');
})->name('landing');

// 2. Auth Routes untuk Admin Web
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// 3. Fallback Route Khusus Penyajian File Gambar Struk (Solusi Symlink Rusak di Windows)
Route::get('/storage/receipts/{filename}', function ($filename) {
    $path = 'receipts/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

    return Response::make($file, 200, [
        'Content-Type' => $type,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('filename', '.*')->name('receipts.file');

// 4. Route Dashboard Admin Terproteksi Auth Middleware
Route::prefix('admin')->middleware(['auth'])->group(function () {
    
    // Dashboard & Monitoring
    Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/claims/data', [FinanceDashboardController::class, 'getClaimsApi'])->name('admin.claims.data');
    
    // Fitur Pagu Anggaran Kas
    Route::post('/limits', [LimitSettingController::class, 'update'])->name('admin.limits.update');
    
    // Fitur Verifikasi & Pencairan Klaim
    Route::post('/claims/{id}/verify', [AdminClaimController::class, 'verifyClaim'])->name('admin.claims.verify');
    Route::post('/claims/{id}/reject', [AdminClaimController::class, 'rejectClaim'])->name('admin.claims.reject');
    Route::post('/claims/{id}/transfer', [AdminClaimController::class, 'transferReimbursement'])->name('admin.claims.transfer');

    // Fitur Kelola Pengguna
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Fitur Laporan & Ekspor Jurnal
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('admin.reports.export-pdf');
    Route::get('/journals/export', [ReportController::class, 'exportCsv'])->name('admin.journals.export');
    Route::get('/journals/export-pdf', [ReportController::class, 'exportJournalPdf'])->name('admin.journals.export-pdf');
});