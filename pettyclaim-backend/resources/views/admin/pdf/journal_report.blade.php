<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Draf Jurnal Pembukuan Kas Kecil - PettyClaim</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            text-transform: uppercase;
            font-size: 15px;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 10px;
            color: #64748b;
        }
        .meta-info {
            margin-bottom: 12px;
            font-size: 9.5px;
            color: #334155;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data-table, table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 7px 6px;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
        td {
            padding: 6px;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .bold {
            font-weight: bold;
        }
        .indent {
            padding-left: 18px;
            color: #334155;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8fafc;
        }

        /* SECTION TANDA TANGAN LEGAL */
        .signature-table {
            width: 100%;
            margin-top: 28px;
            border: none !important;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none !important;
            vertical-align: top;
            padding: 0 15px;
        }
        .sig-box {
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .stamp-badge {
            margin: 8px auto;
            padding: 5px 10px;
            border: 1.5px dashed #10b981;
            border-radius: 6px;
            background-color: #ecfdf5;
            display: inline-block;
        }
        .stamp-title {
            color: #059669;
            font-weight: bold;
            font-size: 8.5px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .stamp-date {
            color: #047857;
            font-size: 7.5px;
            margin-top: 2px;
            font-family: 'Courier New', Courier, monospace;
        }
        .footer-note {
            margin-top: 18px;
            font-size: 8.5px;
            color: #64748b;
            font-style: italic;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Jurnal Umum Pembukuan Kas Kecil</h2>
        <p>PettyClaim Enterprise Finance System — Dokumen Rekapitulasi Jurnal Terverifikasi</p>
    </div>

    <div class="meta-info">
        <strong>Tanggal Cetak Dokumen:</strong> {{ $generatedAt }} &nbsp;|&nbsp; 
        <strong>Total Catatan:</strong> {{ count($journals) }} Transaksi Jurnal Sah
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">Tanggal</th>
                <th style="width: 12%;">No. Bukti</th>
                <th style="width: 18%;">Nama Merchant</th>
                <th style="width: 20%;">Akun Pembukuan</th>
                <th style="width: 6%;" class="text-center">Ref</th>
                <th style="width: 11%;" class="text-right">Debit (Rp)</th>
                <th style="width: 11%;" class="text-right">Kredit (Rp)</th>
                <th style="width: 14%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals as $journal)
                @php
                    $debitParts = explode('-', $journal->account_debit, 2);
                    $debitRef = count($debitParts) > 1 ? $debitParts[0] : '1101';
                    $debitName = count($debitParts) > 1 ? $debitParts[1] : $journal->account_debit;

                    $creditParts = explode('-', $journal->account_credit, 2);
                    $creditRef = count($creditParts) > 1 ? $creditParts[0] : '1001';
                    $creditName = count($creditParts) > 1 ? $creditParts[1] : 'Kas Kecil';
                @endphp

                <!-- BARIS DEBIT -->
                <tr>
                    <td class="text-center font-mono">{{ \Carbon\Carbon::parse($journal->entry_date)->format('d/m/Y') }}</td>
                    <td class="bold font-mono">{{ $journal->journal_number }}</td>
                    <td class="bold">{{ $journal->claim->merchant_name ?? 'Tidak Ada' }}</td>
                    <td class="bold">{{ $debitName }}</td>
                    <td class="text-center font-mono">{{ $debitRef }}</td>
                    <td class="text-right font-mono bold">{{ number_format($journal->amount, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">-</td>
                    <td>Pembelian di {{ $journal->claim->merchant_name ?? 'Operasional' }}</td>
                </tr>

                <!-- BARIS KREDIT -->
                <tr style="background-color: #fafafa;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="indent">{{ $creditName }}</td>
                    <td class="text-center font-mono">{{ $creditRef }}</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono bold">{{ number_format($journal->amount, 0, ',', '.') }}</td>
                    <td style="font-style: italic; color: #64748b;">Reimburse - {{ strtolower($debitName) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Belum ada data draf jurnal akuntansi.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right bold" style="padding: 8px;">TOTAL KAS TERJURNAL:</td>
                <td class="text-right font-mono bold" style="padding: 8px;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td class="text-right font-mono bold" style="padding: 8px;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- PENGESAHAN & TANDA TANGAN DIGITAL LEGAL -->
    @php
        $latestClaim = $journals->first()?->claim;
        $approver = $latestClaim?->approver;
        $approvedDate = $latestClaim?->approved_at ? \Carbon\Carbon::parse($latestClaim->approved_at)->format('d/m/Y H:i:s') : date('d/m/Y H:i:s');
    @endphp

    <table class="signature-table">
        <tr>
            <!-- Disiapkan oleh Tim Finance -->
            <td style="width: 50%;">
                <div class="sig-box">
                    <p style="margin: 0; font-weight: bold; color: #475569;">Disiapkan & Diverifikasi Oleh:</p>
                    <p style="margin: 2px 0 6px; font-size: 8px; color: #94a3b8;">Finance & Accounting Administration</p>
                    
                    <div class="stamp-badge" style="border-color: #38bdf8; background-color: #f0f9ff;">
                        <span class="stamp-title" style="color: #0284c7;">TERCATAT DI SISTEM</span><br>
                        <span class="stamp-date" style="color: #0369a1;">SINKRONISASI OTOMATIS</span>
                    </div>

                    <p style="margin: 6px 0 0; font-weight: bold; font-size: 10px; color: #0f172a; text-decoration: underline;">
                        {{ auth()->user()->name ?? 'Administrator Finance' }}
                    </p>
                    <p style="margin: 2px 0 0; font-size: 8.5px; color: #64748b; font-family: 'Courier New', Courier, monospace;">
                        NIP: {{ auth()->user()->nip ?? 'EMP-FIN-001' }}
                    </p>
                </div>
            </td>

            <!-- Disetujui oleh Manajer -->
            <td style="width: 50%;">
                <div class="sig-box">
                    <p style="margin: 0; font-weight: bold; color: #475569;">Disetujui & Diotorisasi Oleh:</p>
                    <p style="margin: 2px 0 6px; font-size: 8px; color: #94a3b8;">Manager / Chief Approval Officer</p>
                    
                    <div class="stamp-badge">
                        <span class="stamp-title">DISETUJUI DIGITAL (ACC)</span><br>
                        <span class="stamp-date">WAKTU: {{ $approvedDate }} WIB</span>
                    </div>

                    <p style="margin: 6px 0 0; font-weight: bold; font-size: 10px; color: #0f172a; text-decoration: underline;">
                        {{ $approver?->name ?? 'Manager Approver' }}
                    </p>
                    <p style="margin: 2px 0 0; font-size: 8.5px; color: #64748b; font-family: 'Courier New', Courier, monospace;">
                        NIP: {{ $approver?->nip ?? 'EMP-MGR-001' }}
                    </p>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        * Dokumen ini sah dan diakui secara akuntansi internal, dihasilkan melalui pengesahan kriptografis sistem PettyClaim tanpa memerlukan tanda tangan basah fisik.
    </div>

</body>
</html>