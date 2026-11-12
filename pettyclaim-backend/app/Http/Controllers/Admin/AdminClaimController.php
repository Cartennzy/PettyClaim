<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\User;
use App\Models\WalletMutation;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminClaimController extends Controller
{
    /**
     * Verifikasi Struk oleh Tim Finance & Penerusan ke Manager Divisi
     */
    public function verifyClaim(Request $request, $id, FCMService $fcmService)
    {
        $claim = Claim::with('user')->findOrFail($id);
        $claim->status = 'VERIFIED';
        if ($request->filled('notes')) {
            $claim->notes = $request->notes;
        }
        $claim->save();

        // 1. Kirim Push Notifikasi ke Manager Sesuai Divisi Karyawan
        $userDept = $claim->user ? $claim->user->department : null;
        $managerQuery = User::where('role', 'manager');
        if ($userDept) {
            $managerQuery->where('department', $userDept);
        }
        $managers = $managerQuery->get();
        if ($managers->isEmpty()) {
            $managers = User::where('role', 'manager')->get();
        }

        foreach ($managers as $manager) {
            if ($manager->fcm_token) {
                $fcmService->sendNotification(
                    $manager->fcm_token,
                    "Pemberitahuan: Antrean Klaim Baru Divisi " . ($userDept ?? ''),
                    "Klaim senilai Rp " . number_format($claim->amount, 0, ',', '.') . " di {$claim->merchant_name} oleh {$claim->user->name} telah diverifikasi Finance dan menunggu persetujuan Anda.",
                    ['claim_id' => (string) $claim->id, 'status' => 'VERIFIED']
                );
            }
        }

        // 2. Kirim Notifikasi Update ke Karyawan Pemohon
        if ($claim->user && $claim->user->fcm_token) {
            $fcmService->sendNotification(
                $claim->user->fcm_token,
                "Status Pengajuan: Lolos Validasi Finance",
                "Klaim Anda di {$claim->merchant_name} sebesar Rp " . number_format($claim->amount, 0, ',', '.') . " telah disetujui oleh Finance dan diteruskan ke Manager Divisi untuk otorisasi.",
                ['claim_id' => (string) $claim->id, 'status' => 'VERIFIED']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Klaim berhasil diverifikasi dan diteruskan ke Manager divisi terkait.',
            'data'    => $claim
        ], 200);
    }

    /**
     * Transfer Langsung Reimbursement ke Dompet Karyawan (Disbursement oleh Admin Finance)
     */
    public function transferReimbursement(Request $request, $id, FCMService $fcmService)
    {
        return DB::transaction(function () use ($request, $id, $fcmService) {
            $claim = Claim::with('user')->findOrFail($id);

            // Cek apakah sudah pernah dicairkan sebelumnya berdasarkan tag di notes
            if (str_contains($claim->notes ?? '', '[DISBURSEMENT FINANCE]')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dana klaim ini sudah pernah dicairkan sebelumnya.'
                ], 422);
            }

            // Status tetap APPROVED agar lolos check constraint PostgreSQL
            $claim->status = 'APPROVED';
            $claim->notes = ($claim->notes ? $claim->notes . " | " : "") . "[DISBURSEMENT FINANCE: Rp " . number_format($claim->amount, 0, ',', '.') . "]";
            $claim->save();

            if ($claim->user) {
                $balanceBefore = (double) ($claim->user->wallet_balance ?? 0);
                $balanceAfter = $balanceBefore + (double) $claim->amount;

                $claim->user->wallet_balance = $balanceAfter;
                $claim->user->save();

                WalletMutation::create([
                    'user_id'          => $claim->user->id,
                    'claim_id'         => $claim->id,
                    'type'             => 'IN',
                    'amount'           => $claim->amount,
                    'balance_before'   => $balanceBefore,
                    'balance_after'    => $balanceAfter,
                    'title'            => 'Disbursement Finance: ' . $claim->merchant_name,
                    'description'      => $request->notes ?? 'Pencairan dana reimbursement kas kecil disetujui Admin Finance',
                    'channel'          => 'DISBURSEMENT',
                    'account_number'   => '-',
                    'account_name'     => $claim->user->name,
                    'transaction_date' => now(),
                ]);

                if ($claim->user->fcm_token) {
                    $fcmService->sendNotification(
                        $claim->user->fcm_token,
                        "Saldo Dompet Bertambah",
                        "Dana klaim sebesar Rp " . number_format($claim->amount, 0, ',', '.') . " di {$claim->merchant_name} telah berhasil dicairkan ke saldo dompet digital akun Anda.",
                        ['claim_id' => (string) $claim->id, 'status' => 'APPROVED']
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Saldo Rp " . number_format($claim->amount, 0, ',', '.') . " berhasil dicairkan ke dompet karyawan.",
                'data'    => $claim
            ], 200);
        });
    }

    /**
     * Penolakan Pengajuan Klaim
     */
    public function rejectClaim(Request $request, $id, FCMService $fcmService)
    {
        $claim = Claim::with('user')->findOrFail($id);
        $claim->status = 'REJECTED';
        $claim->notes = $request->notes ?? 'Pengajuan klaim ditolak oleh Tim Finance.';
        $claim->approved_by = auth()->id();
        $claim->approved_at = now();
        $claim->save();

        if ($claim->user && $claim->user->fcm_token) {
            $fcmService->sendNotification(
                $claim->user->fcm_token,
                "Status Pengajuan: Klaim Ditolak",
                "Pengajuan klaim di {$claim->merchant_name} sebesar Rp " . number_format($claim->amount, 0, ',', '.') . " ditolak oleh Tim Finance.",
                ['claim_id' => (string) $claim->id, 'status' => 'REJECTED']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Klaim berhasil ditolak.',
            'data'    => $claim
        ], 200);
    }
}