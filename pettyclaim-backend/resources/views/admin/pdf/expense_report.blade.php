<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran Kas Kecil - PettyClaim</title>
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
        .meta {
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
            padding: 8px 6px;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
        td {
            padding: 6px 8px;
            vertical-align: middle;
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
        <h2>Laporan Pengeluaran Kas Kecil</h2>
        <p>PettyClaim Enterprise System — Laporan Rekapitulasi Realisasi Biaya Operasional</p>
    </div>

    <div class="meta">
        <strong>Tanggal Cetak:</strong> {{ $generatedAt }} &nbsp;|&nbsp;
        <strong>Status Data:</strong> APPROVED (Telah Disetujui & Diotorisasi Manajer)
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;" class="text-center">No</th>
                <th style="width: 14%;" class="text-center">Tanggal</th>
                <th style="width: 20%;">Pemohon & Divisi</th>
                <th style="width: 35%;">Nama Merchant & Keterangan</th>
                <th style="width: 25%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($approvedClaims as $index => $claim)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="text-center font-mono">{{ \Carbon\Carbon::parse($claim->transaction_date)->format('d/m/Y') }}</td>
                    <td>
                        <span class="bold">{{ $claim->user->name ?? 'Karyawan' }}</span><br>
                        <span style="font-size: 8.5px; color: #64748b;">{{ $claim->user->department ?? 'Umum' }} ({{ $claim->user->nip ?? '-' }})</span>
                    </td>
                    <td>
                        <span class="bold">{{ $claim->merchant_name }}</span><br>
                        <span style="font-size: 8.5px; color: #64748b;">{{ $claim->notes ?? 'Pengeluaran kas kecil' }}</span>
                    </td>
                    <td class="text-right font-mono bold">{{ number_format($claim->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">Tidak ada data pengeluaran kas kecil yang disetujui.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right bold" style="padding: 8px;">TOTAL REALISASI PENGELUARAN:</td>
                <td class="text-right font-mono bold" style="padding: 8px;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- PENGESAHAN & TANDA TANGAN DIGITAL LEGAL -->
    @php
        $latestApproved = $approvedClaims->first();
        $approver = $latestApproved?->approver;
        $approvedDate = $latestApproved?->approved_at ? \Carbon\Carbon::parse($latestApproved->approved_at)->format('d/m/Y H:i:s') : date('d/m/Y H:i:s');
    @endphp

    <table class="signature-table">
        <tr>
            <!-- Disiapkan oleh Tim Finance -->
            <td style="width: 50%;">
                <div class="sig-box">
                    <p style="margin: 0; font-weight: bold; color: #475569;">Disiapkan Oleh (Finance):</p>
                    <p style="margin: 2px 0 6px; font-size: 8px; color: #94a3b8;">Finance & Accounting Administration</p>
                    
                    <div class="stamp-badge" style="border-color: #38bdf8; background-color: #f0f9ff;">
                        <span class="stamp-title" style="color: #0284c7;">AUDIT FINANCE VALID</span><br>
                        <span class="stamp-date" style="color: #0369a1;">REKONSILIASI LENGKAP</span>
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
                    <p style="margin: 0; font-weight: bold; color: #475569;">Disetujui Oleh (Manager):</p>
                    <p style="margin: 2px 0 6px; font-size: 8px; color: #94a3b8;">Head of Department / Approver</p>
                    
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
        * Dokumen ini dibuat otomatis oleh PettyClaim Enterprise System dan sah digunakan sebagai bukti pembukuan reimbursement internal perusahaan.
    </div>

</body>
</html>