<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Claim;
use App\Services\FraudDetectionService;
use App\Jobs\ProcessPhashFraudCheck;
use Illuminate\Support\Facades\Storage;

class AuditReceiptFraud extends Command
{
    protected $signature = 'receipt:audit-fraud';
    protected $description = 'Hitung ulang pHash dan audit urutan duplikat visual struk';

    public function handle(FraudDetectionService $fraudService): int
    {
        $this->info('Memulai audit visual struk berdasarkan urutan waktu pengajuan...');

        // Urutkan dari yang pertama kali masuk ke sistem
        $claims = Claim::orderBy('created_at', 'asc')->get();

        if ($claims->isEmpty()) {
            $this->warn('Tidak ada data klaim.');
            return 0;
        }

        // 1. Pastikan hash terisi untuk semua struk
        foreach ($claims as $claim) {
            $rawPath = ltrim(preg_replace('/^storage\//', '', $claim->receipt_image_path), '/');
            
            $localPath = null;
            if (Storage::disk('public')->exists($rawPath)) {
                $localPath = Storage::disk('public')->path($rawPath);
            } elseif (file_exists(storage_path('app/public/' . $rawPath))) {
                $localPath = storage_path('app/public/' . $rawPath);
            } elseif (file_exists(public_path('storage/' . $rawPath))) {
                $localPath = public_path('storage/' . $rawPath);
            }

            if ($localPath && file_exists($localPath)) {
                $hash = $fraudService->calculatePHash($localPath);
                $claim->phash_value = $hash;
                $claim->is_flagged_duplicate = false; // Reset awal
                $claim->save();
            }
        }

        // 2. Evaluasi status dari yang terlama ke terbaru
        $this->info('Mengevaluasi keaslian dokumen...');
        foreach ($claims as $claim) {
            ProcessPhashFraudCheck::dispatchSync($claim);
            $claim->refresh();

            $statusText = $claim->is_flagged_duplicate ? '[DUPLIKAT]' : '[OTENTIK]';
            $this->line("{$statusText} Klaim {$claim->id} ({$claim->merchant_name}) - Dibuat: {$claim->created_at}");
        }

        $this->info('Audit selesai: Struk pertama berstatus Otentik, pengajuan tiruan berikutnya ditandai Duplikat.');
        return 0;
    }
}