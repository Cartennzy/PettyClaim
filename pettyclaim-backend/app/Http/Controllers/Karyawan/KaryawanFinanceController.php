<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanFinanceController extends Controller
{
    /**
     * Ambil Rekap Saldo, Statistik Klaim & Histori Mutasi Dompet
     */
    public function getFinanceSummary(Request $request)
    {
        $user = null;
        if ($request->filled('email')) {
            $user = User::where('email', $request->email)->first();
        }
        if (!$user) {
            $user = auth('sanctum')->user() ?? auth()->user();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.'
            ], 404);
        }

        // Ambil riwayat klaim user untuk summary
        $claims = $user->claims()->get();

        $totalApproved = (float) $claims->where('status', 'APPROVED')->sum('amount');
        $totalPending  = (float) $claims->whereIn('status', ['PENDING', 'PENDING_VERIFICATION', 'VERIFIED'])->sum('amount');
        $totalRejected = (float) $claims->where('status', 'REJECTED')->sum('amount');

        // Ambil histori mutasi wallet
        $mutations = WalletMutation::where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(function ($m) {
                return [
                    'id'               => $m->id,
                    'type'             => $m->type, // IN atau OUT
                    'title'            => $m->title,
                    'description'      => $m->description ?? '-',
                    'amount'           => (float) $m->amount,
                    'balance_after'    => (float) $m->balance_after,
                    'channel'          => $m->channel ?? 'DOMPET',
                    'account_number'   => $m->account_number ?? '-',
                    'transaction_date' => $m->transaction_date->format('Y-m-d H:i:s'),
                    'formatted_date'   => $m->transaction_date->translatedFormat('d M Y, H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'wallet_balance' => (float) ($user->wallet_balance ?? 0.0),
                'total_approved' => $totalApproved,
                'total_pending'  => $totalPending,
                'total_rejected' => $totalRejected,
                'mutations'      => $mutations,
            ]
        ], 200);
    }

    /**
     * Proses Penarikan Saldo Dompet & Catat Mutasi
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1000',
            'channel'        => 'required|string',
            'account_number' => 'required|string',
            'account_name'   => 'required|string',
            'email'          => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun karyawan tidak ditemukan.'
            ], 404);
        }

        $withdrawAmount = (float) $request->amount;
        $currentBalance = (float) $user->wallet_balance;

        if ($withdrawAmount > $currentBalance) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo dompet tidak mencukupi untuk penarikan ini.'
            ], 400);
        }

        return DB::transaction(function () use ($user, $withdrawAmount, $currentBalance, $request) {
            $newBalance = $currentBalance - $withdrawAmount;
            $user->wallet_balance = $newBalance;
            $user->save();

            // Simpan riwayat mutasi
            WalletMutation::create([
                'user_id'          => $user->id,
                'type'             => 'OUT',
                'amount'           => $withdrawAmount,
                'balance_before'   => $currentBalance,
                'balance_after'    => $newBalance,
                'title'            => 'Penarikan Dana ke ' . $request->channel,
                'description'      => 'Transfer rekening a.n ' . $request->account_name . ' (' . $request->account_number . ')',
                'channel'          => $request->channel,
                'account_number'   => $request->account_number,
                'account_name'     => $request->account_name,
                'transaction_date' => now(),
            ]);

            return response()->json([
                'success'           => true,
                'message'           => 'Penarikan dana sebesar Rp ' . number_format($withdrawAmount, 0, ',', '.') . ' berhasil diproses.',
                'remaining_balance' => $newBalance,
            ], 200);
        });
    }
}