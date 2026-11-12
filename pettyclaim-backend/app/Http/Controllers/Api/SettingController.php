<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingController extends Controller
{
    /**
     * Helper Static untuk Mendapatkan Limit Kas Aktif
     */
    public static function getMaxClaimLimit(): float
    {
        $limit = DB::table('settings')->where('key', 'claim_limit')->value('value') 
              ?? DB::table('settings')->where('key', 'max_claim_limit')->value('value');

        return $limit ? (float) $limit : 5000000.0;
    }

    /**
     * Endpoint API Sinkronisasi Limit & Nama Periode ke Flutter Mobile
     */
    public function getLimit()
    {
        // 1. Logika asli milikmu (tetap dipertahankan)
        $limit = self::getMaxClaimLimit();

        Carbon::setLocale('id');
        $savedPeriod = DB::table('settings')->where('key', 'claim_limit_period')->value('value');
        $period = $savedPeriod ?: Carbon::now()->translatedFormat('F Y');

        // 2. TAMBAHAN: Kalkulasi Sisa Anggaran (Sinkron dengan Dashboard Admin)
        // Menjumlahkan klaim yang sudah disetujui (APPROVED / DISBURSED) di bulan ini
        $terpakai = DB::table('claims')
            ->whereIn('status', ['APPROVED', 'DISBURSED'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        // Sisa anggaran = Limit awal dikurangi yang terpakai
        $sisaAnggaran = $limit - $terpakai;
        if ($sisaAnggaran < 0) {
            $sisaAnggaran = 0; // Memastikan limit tidak menjadi minus di aplikasi Karyawan
        }

        return response()->json([
            'success'             => true,
            'limit'               => $limit,             // Nilai asli tetap dikirim
            'remaining_limit'     => $sisaAnggaran,      // Nilai baru hasil pengurangan (Rp 24.977.000)
            'terpakai'            => $terpakai,
            'period'              => $period,
            'formatted_limit'     => 'Rp ' . number_format($limit, 0, ',', '.'),
            'formatted_remaining' => 'Rp ' . number_format($sisaAnggaran, 0, ',', '.')
        ], 200);
    }
}