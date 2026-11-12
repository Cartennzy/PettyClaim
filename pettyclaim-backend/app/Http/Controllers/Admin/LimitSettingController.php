<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LimitSettingController extends Controller
{
    /**
     * Update Pagu Kas Kecil Bulanan & Simpan Periode Bulan ke PostgreSQL
     */
    public function update(Request $request)
    {
        $request->validate([
            'limit' => 'required|numeric|min:0',
        ]);

        Carbon::setLocale('id');
        $currentMonthName = Carbon::now()->translatedFormat('F Y');
        $currentMonthKey = Carbon::now()->format('Y-m');

        // 1. Simpan Pagu Kas Utama
        Setting::updateOrCreate(
            ['key' => 'claim_limit'],
            ['value' => (string) $request->limit]
        );

        // 2. Simpan Alias Key untuk Sinkronisasi Penuh ke Flutter
        Setting::updateOrCreate(
            ['key' => 'max_claim_limit'],
            ['value' => (string) $request->limit]
        );

        // 3. Simpan Nama Periode Bulan
        Setting::updateOrCreate(
            ['key' => 'claim_limit_period'],
            ['value' => $currentMonthName]
        );

        // 4. Simpan Kode Periode (YYYY-MM)
        Setting::updateOrCreate(
            ['key' => 'claim_limit_month_key'],
            ['value' => $currentMonthKey]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Limit Kas Kecil periode {$currentMonthName} berhasil diperbarui di database!",
                'limit'   => (double) $request->limit,
                'period'  => $currentMonthName
            ], 200);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Limit Kas Kecil periode {$currentMonthName} berhasil diperbarui di database!")
            ->with('activeTab', 'limits');
    }
}