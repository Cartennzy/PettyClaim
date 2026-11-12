<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Journal;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Ekspor Laporan Rekapitulasi Pengeluaran Kas Kecil ke PDF
     */
    public function exportPdf()
    {
        $approvedClaims = Claim::where('status', 'APPROVED')->orderBy('created_at', 'desc')->get();
        $totalAmount = $approvedClaims->sum('amount');
        $generatedAt = date('d-m-Y H:i:s');

        $pdf = Pdf::loadView('admin.pdf.expense_report', compact('approvedClaims', 'totalAmount', 'generatedAt'));
        
        return $pdf->download('Laporan_Pengeluaran_Kas_Kecil_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Ekspor Draf Jurnal Akuntansi ke PDF
     */
    public function exportJournalPdf()
    {
        $journals = Journal::with('claim')->orderBy('created_at', 'desc')->get();
        $totalAmount = $journals->sum('amount');
        $generatedAt = date('d-m-Y H:i:s');

        $pdf = Pdf::loadView('admin.pdf.journal_report', compact('journals', 'totalAmount', 'generatedAt'))
                  ->setPaper('a4', 'landscape');
        
        return $pdf->download('Draf_Jurnal_Akuntansi_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Ekspor Draf Jurnal Pembukuan ke CSV (Format Standar Excel Akuntansi)
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'draf_jurnal_pettyclaim_' . date('Y-m-d') . '.csv';
        $journals = Journal::with('claim')->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($journals) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Tanggal', 
                'No. Bukti', 
                'Nama Toko / Merchant', 
                'Akun', 
                'Ref', 
                'Debit (Rp)', 
                'Kredit (Rp)', 
                'Keterangan'
            ], ';');

            foreach ($journals as $journal) {
                $debitParts = explode('-', $journal->account_debit, 2);
                $debitRef = count($debitParts) > 1 ? $debitParts[0] : '1101';
                $debitName = count($debitParts) > 1 ? $debitParts[1] : $journal->account_debit;

                $creditParts = explode('-', $journal->account_credit, 2);
                $creditRef = count($creditParts) > 1 ? $creditParts[0] : '1001';
                $creditName = count($creditParts) > 1 ? $creditParts[1] : 'Kas Kecil';

                $merchant = $journal->claim->merchant_name ?? 'Tidak Ada';
                $formattedDate = \Carbon\Carbon::parse($journal->entry_date)->format('d/m/Y');

                fputcsv($file, [
                    $formattedDate,
                    $journal->journal_number,
                    $merchant,
                    $debitName,
                    $debitRef,
                    $journal->amount,
                    0,
                    'Pembelian di ' . $merchant . ' (Scan Struk)'
                ], ';');

                fputcsv($file, [
                    '',
                    '',
                    '',
                    '   ' . $creditName,
                    $creditRef,
                    0,
                    $journal->amount,
                    'Reimburse karyawan - ' . strtolower($debitName)
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}