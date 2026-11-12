<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPhashFraudCheck;
use App\Models\Claim;
use App\Models\User;
use App\Services\FraudDetectionService;
use App\Services\TextParsingService;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KaryawanClaimController extends Controller
{
    public function __construct(
        protected TextParsingService $textParsingService,
        protected FraudDetectionService $fraudService
    ) {}

    public function processScan(Request $request)
    {
        $request->validate([
            'receipt_image' => 'required|file|image|max:10240',
            'raw_text'      => 'nullable|string',
        ]);

        $savedPath = null;
        $tempPath = null;

        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $filename = 'scan_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $savedPath = $file->storeAs('temp_receipts', $filename, 'public');
            $tempPath = Storage::disk('public')->path($savedPath);
        }

        if (!$tempPath || !file_exists($tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File gambar struk gagal diunggah ke server.',
            ], 422);
        }

        // Tembak file gambar langsung ke PaddleOCR Microservice
        $rawText = $request->input('raw_text');
        $result = $this->textParsingService->parseReceiptImage($tempPath, $rawText);

        if ($savedPath) {
            $result['receipt_image_path'] = $savedPath;
            $result['receipt_image_url'] = url('storage/' . $savedPath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Struk berhasil diproses secara otomatis oleh PaddleOCR.',
            'data'    => $result
        ], 200);
    }

    public function index(Request $request)
    {
        $emailParam = $request->query('email') ?? $request->input('email');
        $user = null;

        if (!empty($emailParam)) {
            $cleanEmail = trim($emailParam);
            $user = User::where('email', $cleanEmail)->first();
        }

        if (!$user) {
            $user = auth('sanctum')->user() ?? auth()->user();
        }

        $claimsQuery = Claim::with(['journal', 'user'])->orderBy('created_at', 'desc');

        if ($user) {
            $claimsQuery->where('user_id', $user->id);
        }

        $claims = $claimsQuery->get();

        if ($claims->isEmpty()) {
            $claims = Claim::with(['journal', 'user'])->orderBy('created_at', 'desc')->get();
        }

        $formattedClaims = $claims->map(function ($c) {
            $filename = basename($c->receipt_image_path);
            $c->receipt_url = url('storage/receipts/' . $filename);
            return $c;
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedClaims
        ], 200);
    }

    public function store(Request $request, ?FCMService $fcmService = null)
    {
        $validated = $request->validate([
            'merchant_name'      => 'required|string',
            'amount'             => 'required|numeric',
            'transaction_date'   => 'required|date',
            'notes'              => 'nullable|string',
            'employee_email'     => 'nullable|string',
            'receipt_image'      => 'nullable|file|image|max:10240',
            'receipt_image_path' => 'nullable|string',
        ]);

        $user = null;
        if (!empty($validated['employee_email'])) {
            $cleanEmail = trim($validated['employee_email']);
            $user = User::where('email', $cleanEmail)->first();
        }

        if (!$user) {
            $user = auth('sanctum')->user() ?? auth()->user();
        }

        if (!$user) {
            $user = User::where('role', 'karyawan')->first() ?? User::find(3) ?? User::first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User karyawan tidak ditemukan.',
            ], 422);
        }

        $receiptPath = $validated['receipt_image_path'] ?? null;
        $localPhysicalPath = null;

        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $filename = 'receipt_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $receiptPath = $file->storeAs('receipts', $filename, 'public');
            $localPhysicalPath = Storage::disk('public')->path($receiptPath);
        }

        if (!$receiptPath) {
            $receiptPath = 'receipts/default.jpg';
        }

        // --- PENGECEKAN FRAUD REALTIME ---
        $calculatedHash = null;
        $isDuplicate = false;

        if ($localPhysicalPath && file_exists($localPhysicalPath)) {
            $calculatedHash = $this->fraudService->calculatePHash($localPhysicalPath);
        }

        $existingClaims = Claim::whereNotNull('receipt_image_path')->orderBy('created_at', 'asc')->get();

        foreach ($existingClaims as $oldClaim) {
            if ($calculatedHash && !empty($oldClaim->phash_value)) {
                $similarity = $this->fraudService->calculateSimilarityPercentage($calculatedHash, $oldClaim->phash_value);
                if ($similarity >= 75.0) {
                    $isDuplicate = true;
                    Log::warning("FRAUD INSTAN (pHash): {$similarity}% mirip dengan Claim ID {$oldClaim->id}");
                    break;
                }
            }

            $oldRaw = ltrim(preg_replace('/^storage\//', '', $oldClaim->receipt_image_path), '/');
            $oldPhysical = Storage::disk('public')->path($oldRaw);
            if ($localPhysicalPath && file_exists($oldPhysical) && file_exists($localPhysicalPath)) {
                if (md5_file($localPhysicalPath) === md5_file($oldPhysical)) {
                    $isDuplicate = true;
                    Log::warning("FRAUD INSTAN (MD5 Identik) dengan Claim ID {$oldClaim->id}");
                    break;
                }
            }

            if (
                strcasecmp(trim($oldClaim->merchant_name), trim($validated['merchant_name'])) === 0 &&
                (float) $oldClaim->amount === (float) $validated['amount'] &&
                $oldClaim->transaction_date->toDateString() === $validated['transaction_date']
            ) {
                $isDuplicate = true;
                Log::warning("FRAUD INSTAN (Data Identik) dengan Claim ID {$oldClaim->id}");
                break;
            }
        }

        $claim = Claim::create([
            'id'                   => (string) Str::uuid(),
            'user_id'              => $user->id,
            'merchant_name'        => $validated['merchant_name'],
            'amount'               => $validated['amount'],
            'transaction_date'     => $validated['transaction_date'],
            'notes'                => $validated['notes'] ?? 'Pengajuan Struk Kasir',
            'receipt_image_path'   => $receiptPath,
            'phash_value'          => $calculatedHash,
            'status'               => 'PENDING_VERIFICATION',
            'is_flagged_duplicate' => $isDuplicate,
        ]);

        try {
            ProcessPhashFraudCheck::dispatch($claim);
        } catch (\Throwable $e) {
            Log::error("Queue dispatch error: " . $e->getMessage());
        }

        $filename = basename($claim->receipt_image_path);
        $claim->receipt_url = url('storage/receipts/' . $filename);

        return response()->json([
            'success' => true,
            'message' => $isDuplicate
                ? 'Klaim berhasil diajukan namun terindikasi duplikasi struk.'
                : 'Klaim berhasil diajukan dan masuk ke antrean validasi Finance.',
            'data'    => $claim
        ], 201);
    }
}