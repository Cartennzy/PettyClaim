<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TextParsingService
{
    /**
     * Memproses gambar struk via Microservice PaddleOCR Python (Port 5000)
     */
    public function parseReceiptImage(string $absoluteImagePath, ?string $rawText = null): array
    {
        $pythonUrl = env('OCR_SERVICE_URL', 'http://127.0.0.1:5000/extract-receipt');

        if (!empty($absoluteImagePath) && file_exists($absoluteImagePath)) {
            try {
                // Kirim file fisik gambar ke Python FastAPI via multipart/form-data
                $response = Http::timeout(20)->attach(
                    'file',
                    file_get_contents($absoluteImagePath),
                    basename($absoluteImagePath)
                )->post($pythonUrl);

                if ($response->successful()) {
                    $result = $response->json();
                    
                    return [
                        'merchant_name'    => !empty($result['merchant']) ? trim($result['merchant']) : 'STRUK PEMBELIAN',
                        'total_amount'     => (float) ($result['total'] ?? 0.0),
                        'amount'           => (float) ($result['total'] ?? 0.0),
                        'transaction_date' => !empty($result['date']) ? $result['date'] : now()->toDateString(),
                        'category'         => !empty($result['category']) ? $result['category'] : '1101 - Beban Operasional / Dapur',
                    ];
                }

                Log::warning('PaddleOCR Microservice HTTP Status: ' . $response->status() . ' - Body: ' . $response->body());
            } catch (\Throwable $e) {
                Log::error('Koneksi ke Microservice PaddleOCR Python Gagal: ' . $e->getMessage());
            }
        }

        // Fallback jika Python service tidak merespons atau sedang mati
        if (!empty($rawText)) {
            return $this->parseFallbackRawText($rawText);
        }

        return $this->defaultFallback('Struk Pembelian');
    }

    /**
     * Parser Fallback Regex jika microservice tidak terhubung
     */
    private function parseFallbackRawText(string $rawText): array
    {
        $lines = collect(explode("\n", str_replace(["\r\n", "\r"], "\n", $rawText)))
            ->map(fn($line) => trim($line))
            ->filter(fn($line) => !empty($line))
            ->values()
            ->toArray();

        $merchant = "STRUK PEMBELIAN";
        $blacklist = ['jl', 'jalan', 'telp', 'nota', 'struk', 'kasir', 'shift', 'npwp', 'waktu', 'order', 'cashier', 'table', 'no.', 'nomor', 'selamat', 'terima', 'kasih'];

        foreach (array_slice($lines, 0, 6) as $line) {
            $clean = preg_replace('/[^a-zA-Z0-9\s\.\&\-]/', '', $line);
            $lower = strtolower($clean);

            if (strlen($clean) < 3 || preg_match('/^\d+$/', $clean)) continue;

            $skip = false;
            foreach ($blacklist as $b) {
                if (str_starts_with($lower, $b) || str_contains($lower, 'npwp') || str_contains($lower, 'kec.')) {
                    $skip = true;
                    break;
                }
            }

            if (!$skip) {
                $merchant = strtoupper($clean);
                break;
            }
        }

        $detectedTotal = 0.0;
        $totalKeywords = ['total', 'grand', 'bayar', 'jumlah', 'tagihan', 'subtotal'];

        foreach ($lines as $index => $line) {
            $lower = strtolower($line);
            if (str_contains($lower, 'kembali') || str_contains($lower, 'change') || str_contains($lower, 'tunai') || str_contains($lower, 'cash')) {
                continue;
            }

            foreach ($totalKeywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $searchBlock = $line . ' ' . ($lines[$index + 1] ?? '');
                    preg_match_all('/\b\d{1,3}(?:[.,]\d{3})+\b|\b\d{4,8}\b/', $searchBlock, $matches);
                    if (!empty($matches[0])) {
                        $valStr = str_replace(['.', ','], '', end($matches[0]));
                        $val = (float) $valStr;
                        if ($val >= 1000) {
                            $detectedTotal = $val;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($detectedTotal == 0.0) {
            foreach (array_reverse($lines) as $line) {
                if (str_contains(strtolower($line), 'kembali') || str_contains(strtolower($line), 'tunai')) continue;
                preg_match_all('/\b\d{1,3}(?:[.,]\d{3})+\b|\b\d{4,8}\b/', $line, $matches);
                if (!empty($matches[0])) {
                    $valStr = str_replace(['.', ','], '', end($matches[0]));
                    $val = (float) $valStr;
                    if ($val >= 1000 && $val <= 50000000) {
                        $detectedTotal = $val;
                        break;
                    }
                }
            }
        }

        $date = now()->toDateString();
        foreach ($lines as $line) {
            if (preg_match('/\b(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{2,4})\b/', $line, $m)) {
                $d = (int)$m[1];
                $mo = (int)$m[2];
                $y = strlen($m[3]) == 2 ? (int)('20' . $m[3]) : (int)$m[3];
                if (checkdate($mo, $d, $y)) {
                    $date = sprintf('%04d-%02d-%02d', $y, $mo, $d);
                    break;
                }
            }
        }

        $category = '1101 - Beban Operasional / Dapur';
        $fullText = strtolower($rawText);
        if (preg_match('/(bensin|spbu|pertamina|shell|bp|pertalite|pertamax|solar|parkir|toll)/', $fullText)) {
            $category = '1103 - Beban Bensin & Transportasi';
        } elseif (preg_match('/(atk|kertas|print|fotocopy|pulpen|lakban|spidol)/', $fullText)) {
            $category = '1102 - Beban Alat Tulis Kantor (ATK)';
        } elseif (preg_match('/(jne|j&t|sicepat|anteraja|tiki|kurir|ongkir)/', $fullText)) {
            $category = '1104 - Beban Kurir & Logistik';
        } elseif (preg_match('/(bengkel|servis|service|oli|ban|sparepart)/', $fullText)) {
            $category = '1105 - Beban Perbaikan & Pemeliharaan';
        }

        return [
            'merchant_name'    => $merchant,
            'total_amount'     => $detectedTotal,
            'amount'           => $detectedTotal,
            'transaction_date' => $date,
            'category'         => $category,
        ];
    }

    private function defaultFallback(string $fallbackMerchant): array
    {
        return [
            'merchant_name'    => $fallbackMerchant,
            'total_amount'     => 0.0,
            'amount'           => 0.0,
            'transaction_date' => now()->toDateString(),
            'category'         => '1101 - Beban Operasional / Dapur',
        ];
    }
}