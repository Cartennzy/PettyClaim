<?php

namespace App\Jobs;

use App\Models\Claim;
use App\Services\FraudDetectionService;
use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPhashFraudCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Claim $claim)
    {
    }

    public function handle(FraudDetectionService $fraudService, ?FCMService $fcmService = null): void
    {
        $rawPath = ltrim($this->claim->receipt_image_path, '/');
        $rawPath = preg_replace('/^storage\//', '', $rawPath);

        // Cari file fisik foto struk di berbagai kemungkinan direktori
        $localPath = null;
        if (Storage::disk('public')->exists($rawPath)) {
            $localPath = Storage::disk('public')->path($rawPath);
        } elseif (file_exists(storage_path('app/public/' . $rawPath))) {
            $localPath = storage_path('app/public/' . $rawPath);
        } elseif (file_exists(public_path('storage/' . $rawPath))) {
            $localPath = public_path('storage/' . $rawPath);
        }

        if (!$localPath || !file_exists($localPath)) {
            Log::warning("ProcessPhashFraudCheck: File foto struk tidak ditemukan di {$localPath}");
            return;
        }

        // 1. Hitung Sidik Jari Visual (pHash)
        $hash = $fraudService->calculatePHash($localPath);
        if ($hash) {
            $this->claim->phash_value = $hash;
        }

        // 2. Bandingkan dengan klaim terdahulu (yang lebih lama berdasarkan created_at)
        $isDuplicate = false;

        $olderClaims = Claim::where('id', '!=', $this->claim->id)
            ->where('created_at', '<=', $this->claim->created_at)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($olderClaims as $older) {
            // Cek Kemiripan Visual pHash (>= 75%)
            if ($hash && !empty($older->phash_value)) {
                $similarity = $fraudService->calculateSimilarityPercentage($hash, $older->phash_value);
                if ($similarity >= 75.0) {
                    $isDuplicate = true;
                    Log::warning("FRAUD DETECTED (Visual pHash): Klaim {$this->claim->id} mirip ({$similarity}%) dengan klaim asli {$older->id}");
                    break;
                }
            }

            // Cek Kemiripan File Fisik (MD5 Check)
            $olderRaw = ltrim(preg_replace('/^storage\//', '', $older->receipt_image_path), '/');
            $olderPath = null;
            if (Storage::disk('public')->exists($olderRaw)) {
                $olderPath = Storage::disk('public')->path($olderRaw);
            } elseif (file_exists(storage_path('app/public/' . $olderRaw))) {
                $olderPath = storage_path('app/public/' . $olderRaw);
            }

            if ($olderPath && file_exists($olderPath)) {
                if (md5_file($localPath) === md5_file($olderPath)) {
                    $isDuplicate = true;
                    Log::warning("FRAUD DETECTED (MD5 Identik): Klaim {$this->claim->id} sama persis dengan {$older->id}");
                    break;
                }
            }

            // Cek Transaksi Identik (Merchant Sama, Nominal Sama, Tanggal Sama)
            if (
                strcasecmp(trim($older->merchant_name), trim($this->claim->merchant_name)) === 0 &&
                (float) $older->amount === (float) $this->claim->amount &&
                $older->transaction_date->toDateString() === $this->claim->transaction_date->toDateString()
            ) {
                $isDuplicate = true;
                Log::warning("FRAUD DETECTED (Data Transaksi Identik): Klaim {$this->claim->id} sama transaksi dengan {$older->id}");
                break;
            }
        }

        $this->claim->is_flagged_duplicate = $isDuplicate;
        $this->claim->save();

        // 3. Notifikasi FCM (Opsional)
        try {
            $user = $this->claim->user;
            if ($user && !empty($user->fcm_token) && $fcmService) {
                $title = $isDuplicate
                    ? 'Peringatan: Struk Terindikasi Duplikat'
                    : 'Notifikasi: Struk Berhasil Diverifikasi';

                $body = $isDuplicate
                    ? 'Foto bukti struk yang Anda unggah memiliki kemiripan tinggi dengan struk yang sudah ada.'
                    : 'Foto struk telah diverifikasi otentik oleh sistem.';

                $fcmService->sendNotification($user->fcm_token, $title, $body, [
                    'claim_id'     => (string) $this->claim->id,
                    'is_duplicate' => $isDuplicate ? '1' : '0',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("FCM Notification error: " . $e->getMessage());
        }
    }
}