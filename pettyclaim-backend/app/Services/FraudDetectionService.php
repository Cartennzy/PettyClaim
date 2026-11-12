<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class FraudDetectionService
{
    /**
     * Menghitung nilai Perceptual Hash (Visual Fingerprint) 256-bit MURNI dari piksel gambar.
     */
    public function calculatePHash(string $imagePath): ?string
    {
        if (!file_exists($imagePath)) {
            Log::error("FraudDetectionService: File tidak ditemukan di {$imagePath}");
            return null;
        }

        try {
            $imageData = @file_get_contents($imagePath);
            if (!$imageData) {
                return md5_file($imagePath);
            }

            $img = @imagecreatefromstring($imageData);
            if (!$img) {
                return md5_file($imagePath);
            }

            // 1. Resize gambar ke ukuran 16x16 (256 piksel visual)
            $width = 16;
            $height = 16;
            $resized = imagecreatetruecolor($width, $height);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $width, $height, imagesx($img), imagesy($img));

            // 2. Ubah ke Grayscale (abu-abu)
            imagefilter($resized, IMG_FILTER_GRAYSCALE);

            // 3. Ekstraksi nilai intensitas cahaya dan hitung rata-rata
            $pixels = [];
            $total = 0;

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $gray = imagecolorat($resized, $x, $y) & 0xFF;
                    $pixels[] = $gray;
                    $total += $gray;
                }
            }

            imagedestroy($img);
            imagedestroy($resized);

            $average = $total / 256;
            $binaryString = '';

            // 4. Bangun string biner: 1 jika piksel >= rata-rata, 0 jika di bawah rata-rata
            foreach ($pixels as $val) {
                $binaryString .= ($val >= $average) ? '1' : '0';
            }

            // Ubah biner 256-bit menjadi 64 karakter Hexadecimal
            $hexHash = '';
            for ($i = 0; $i < 256; $i += 4) {
                $hexHash .= dechex(bindec(substr($binaryString, $i, 4)));
            }

            return $hexHash;
        } catch (Exception $e) {
            Log::error("Error calculatePHash: " . $e->getMessage());
            return md5_file($imagePath);
        }
    }

    /**
     * Menghitung persentase kemiripan visual antara 2 foto menggunakan Hamming Distance.
     * Return: 0.0 - 100.0%
     */
    public function calculateSimilarityPercentage(string $hash1, string $hash2): float
    {
        if ($hash1 === $hash2) {
            return 100.0;
        }

        // Jika hash 64-karakter (256-bit)
        if (strlen($hash1) === 64 && strlen($hash2) === 64) {
            $bin1 = '';
            $bin2 = '';
            for ($i = 0; $i < 64; $i++) {
                $bin1 .= str_pad(base_convert($hash1[$i], 16, 2), 4, '0', STR_PAD_LEFT);
                $bin2 .= str_pad(base_convert($hash2[$i], 16, 2), 4, '0', STR_PAD_LEFT);
            }

            $diff = 0;
            for ($i = 0; $i < 256; $i++) {
                if ($bin1[$i] !== $bin2[$i]) {
                    $diff++;
                }
            }

            return round(((256 - $diff) / 256) * 100.0, 2);
        }

        // Fallback untuk format 16-karakter (64-bit)
        if (strlen($hash1) === 16 && strlen($hash2) === 16) {
            $bin1 = '';
            $bin2 = '';
            for ($i = 0; $i < 16; $i++) {
                $bin1 .= str_pad(base_convert($hash1[$i], 16, 2), 4, '0', STR_PAD_LEFT);
                $bin2 .= str_pad(base_convert($hash2[$i], 16, 2), 4, '0', STR_PAD_LEFT);
            }

            $diff = 0;
            for ($i = 0; $i < 64; $i++) {
                if ($bin1[$i] !== $bin2[$i]) {
                    $diff++;
                }
            }

            return round(((64 - $diff) / 64) * 100.0, 2);
        }

        $lev = levenshtein($hash1, $hash2);
        $maxLen = max(strlen($hash1), strlen($hash2));
        if ($maxLen == 0) return 100.0;

        return round((1 - ($lev / $maxLen)) * 100.0, 2);
    }
}