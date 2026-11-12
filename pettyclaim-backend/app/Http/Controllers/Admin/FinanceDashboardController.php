<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Journal;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    /**
     * Tampilan Ringkasan Utama Dashboard Admin Keuangan
     */
    public function index()
    {
        $journals = Journal::with(['claim', 'claim.user'])->orderBy('created_at', 'desc')->get();
        $claims = Claim::with(['user'])->orderBy('created_at', 'desc')->get();
        $users = User::orderBy('created_at', 'desc')->get();

        $claimLimitSetting = Setting::where('key', 'claim_limit')->first();
        $claimLimit = $claimLimitSetting ? (double)$claimLimitSetting->value : 5000000;

        // Bulan & Tahun Berjalan untuk Auto-Reset Setiap Bulan Baru
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Metrik Real-time dari Database (Dibatasi pada Bulan Berjalan)
        $totalApprovedAmount = Claim::where('status', 'APPROVED')
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNotNull('transaction_date')
                        ->whereMonth('transaction_date', $currentMonth)
                        ->whereYear('transaction_date', $currentYear);
                })->orWhere(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNull('transaction_date')
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                });
            })
            ->sum('amount');

        $approvedClaimsCount = Claim::where('status', 'APPROVED')
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNotNull('transaction_date')
                        ->whereMonth('transaction_date', $currentMonth)
                        ->whereYear('transaction_date', $currentYear);
                })->orWhere(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNull('transaction_date')
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                });
            })
            ->count();

        $totalPendingAmount = Claim::whereIn('status', ['PENDING', 'PENDING_VERIFICATION', 'VERIFIED'])
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNotNull('transaction_date')
                        ->whereMonth('transaction_date', $currentMonth)
                        ->whereYear('transaction_date', $currentYear);
                })->orWhere(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNull('transaction_date')
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                });
            })
            ->sum('amount');

        $pendingClaimsCount = Claim::whereIn('status', ['PENDING', 'PENDING_VERIFICATION', 'VERIFIED'])
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNotNull('transaction_date')
                        ->whereMonth('transaction_date', $currentMonth)
                        ->whereYear('transaction_date', $currentYear);
                })->orWhere(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNull('transaction_date')
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                });
            })
            ->count();

        $totalRejectedAmount = Claim::where('status', 'REJECTED')
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNotNull('transaction_date')
                        ->whereMonth('transaction_date', $currentMonth)
                        ->whereYear('transaction_date', $currentYear);
                })->orWhere(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNull('transaction_date')
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                });
            })
            ->sum('amount');

        $rejectedClaimsCount = Claim::where('status', 'REJECTED')
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNotNull('transaction_date')
                        ->whereMonth('transaction_date', $currentMonth)
                        ->whereYear('transaction_date', $currentYear);
                })->orWhere(function ($sub) use ($currentMonth, $currentYear) {
                    $sub->whereNull('transaction_date')
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                });
            })
            ->count();

        return view('admin.dashboard', compact(
            'journals', 
            'claims', 
            'users', 
            'claimLimit',
            'totalApprovedAmount', 
            'approvedClaimsCount', 
            'totalPendingAmount',
            'pendingClaimsCount',
            'totalRejectedAmount',
            'rejectedClaimsCount'
        ));
    }

    /**
     * Endpoint API Data Klaim Real-time untuk Live Reload Table
     */
    public function getClaimsApi()
    {
        $claims = Claim::with(['user'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $claims
        ], 200);
    }
}