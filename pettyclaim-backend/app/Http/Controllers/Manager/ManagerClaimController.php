<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\User;
use App\Services\JournalService;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ManagerClaimController extends Controller
{
    public function __construct(
        protected JournalService $journalService
    ) {}

    private function formatClaimData($claims)
    {
        $isCollection = $claims instanceof \Illuminate\Support\Collection;
        $items = $isCollection ? $claims : collect([$claims]);

        $formatted = $items->map(function ($claim) {
            $path = $claim->receipt_image_path;

            if (!$path) {
                $claim->receipt_url = null;
                $claim->receipt_base64 = null;
            } else {
                $cleanPath = ltrim($path, '/');
                $filename = basename($cleanPath);

                if (Str::startsWith($cleanPath, ['http://', 'https://'])) {
                    $claim->receipt_url = $cleanPath;
                    $claim->receipt_base64 = null;
                } else {
                    $claim->receipt_url = url('storage/receipts/' . $filename);

                    if (Storage::disk('public')->exists($cleanPath)) {
                        $fileContent = Storage::disk('public')->get($cleanPath);
                        $mimeType = Storage::disk('public')->mimeType($cleanPath) ?? 'image/jpeg';
                        $claim->receipt_base64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
                    } else {
                        $claim->receipt_base64 = null;
                    }
                }
            }

            $claim->journal_number = $claim->journal ? $claim->journal->journal_number : null;
            $claim->account_debit = $claim->journal ? $claim->journal->account_debit : '1101-Beban Operasional';
            $claim->account_credit = $claim->journal ? $claim->journal->account_credit : '1001-Kas Kecil';

            return $claim;
        });

        return $isCollection ? $formatted : $formatted->first();
    }

    /**
     * Menampilkan daftar SELURUH klaim dari divisi terkait untuk direkap oleh Dashboard Manager
     */
    public function index(Request $request)
    {
        $managerEmail = $request->query('email') ?? $request->input('email');
        $manager = null;

        if (!empty($managerEmail)) {
            $manager = User::where('email', trim($managerEmail))->first();
        }

        if (!$manager) {
            $manager = auth('sanctum')->user() ?? auth()->user();
        }

        $query = Claim::with(['user', 'journal'])
            ->orderBy('created_at', 'desc');

        if ($manager && !empty($manager->department)) {
            $dept = trim($manager->department);
            $query->whereHas('user', function ($q) use ($dept) {
                $q->where('department', 'LIKE', '%' . $dept . '%');
            });
        }

        $claims = $query->get();

        // Fallback: Tampilkan seluruh klaim jika spesifik divisi kosong
        if ($claims->isEmpty()) {
            $claims = Claim::with(['user', 'journal'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatClaimData($claims)
        ], 200);
    }

    public function verifyByFinance(Request $request, $id, FCMService $fcmService)
    {
        $claim = Claim::with('user')->findOrFail($id);
        $claim->status = 'VERIFIED';
        $claim->notes = $request->notes ?? $claim->notes;
        $claim->save();

        $userDept = $claim->user ? $claim->user->department : null;
        $managerQuery = User::where('role', 'manager');
        if ($userDept) {
            $managerQuery->where('department', 'LIKE', '%' . trim($userDept) . '%');
        }
        $managers = $managerQuery->get();

        if ($managers->isEmpty()) {
            $managers = User::where('role', 'manager')->get();
        }

        foreach ($managers as $manager) {
            if ($manager->fcm_token) {
                $fcmService->sendNotification(
                    $manager->fcm_token,
                    "Pemberitahuan Antrean Klaim Baru",
                    "Klaim sebesar Rp " . number_format($claim->amount, 0, ',', '.') . " di {$claim->merchant_name} oleh {$claim->user->name} telah diverifikasi Finance dan siap di-review.",
                    ['claim_id' => (string) $claim->id, 'status' => 'VERIFIED']
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Klaim berhasil diverifikasi Finance dan diteruskan ke Manager.',
            'data'    => $this->formatClaimData($claim)
        ], 200);
    }

    public function approveOrReject(Request $request, $id, FCMService $fcmService)
    {
        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED,approved,rejected',
            'notes'  => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $id, $fcmService) {
            $claim = Claim::with('user')->findOrFail($id);
            
            $approver = auth('sanctum')->user() 
                ?? auth()->user() 
                ?? User::where('role', 'manager')->first() 
                ?? User::where('role', 'admin')->first();

            $statusFormatted = strtoupper($request->status);
            $claim->status = $statusFormatted;

            if ($request->filled('notes')) {
                $claim->notes = $request->notes;
            }
            $claim->approved_by = $approver ? $approver->id : null;
            $claim->approved_at = now();
            $claim->save();

            // Manager HANYA membuat draf jurnal jika disetujui, TIDAK MENCAIRKAN SALDO
            if ($claim->status === 'APPROVED') {
                $this->journalService->createJournalForClaim($claim);
            }

            if ($claim->user && $claim->user->fcm_token) {
                $actionText = $claim->status === 'APPROVED' ? 'disetujui' : 'ditolak';
                $title = $claim->status === 'APPROVED' ? 'Pemberitahuan Klaim Disetujui' : 'Pemberitahuan Klaim Ditolak';
                $body = "Pengajuan klaim di {$claim->merchant_name} sebesar Rp " . number_format($claim->amount, 0, ',', '.') . " telah {$actionText} oleh Manager dan menunggu pencairan dana oleh Admin Finance.";

                try {
                    $fcmService->sendNotification(
                        $claim->user->fcm_token,
                        $title,
                        $body,
                        ['claim_id' => (string) $claim->id, 'status' => $claim->status]
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("FCM Send Error: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Status persetujuan klaim berhasil diperbarui oleh Manager.',
                'data'    => $this->formatClaimData($claim)
            ], 200);
        });
    }

    public function syncApprove(Request $request)
    {
        $validated = $request->validate([
            'employee_email' => 'nullable|string',
            'merchant_name'  => 'required|string',
            'amount'         => 'required|numeric',
            'status'         => 'required|string',
            'notes'          => 'nullable|string',
            'receipt_url'    => 'nullable|string',
        ]);

        $statusUpper = strtoupper($validated['status']);

        $user = User::where('email', $request->employee_email)->first();
        if (!$user) {
            $user = User::where('role', 'karyawan')->first() ?? User::first();
        }

        $defaultImage = 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&q=80&w=800';
        $receiptUrl = !empty($validated['receipt_url']) ? $validated['receipt_url'] : $defaultImage;

        return DB::transaction(function () use ($validated, $statusUpper, $user, $receiptUrl) {
            $claim = Claim::create([
                'id'                 => (string) Str::uuid(),
                'user_id'            => $user->id,
                'merchant_name'      => $validated['merchant_name'],
                'amount'             => $validated['amount'],
                'transaction_date'   => now()->toDateString(),
                'receipt_image_path' => $receiptUrl,
                'status'             => $statusUpper,
                'notes'              => $validated['notes'] ?? 'Disetujui melalui Aplikasi Manager Mobile',
                'approved_at'        => now(),
            ]);

            if ($statusUpper === 'APPROVED') {
                $this->journalService->createJournalForClaim($claim);
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Klaim disetujui dan draf jurnal berhasil dibuat.',
                'claim_id' => $claim->id
            ], 201);
        });
    }
}http://127.0.0.1:8000/