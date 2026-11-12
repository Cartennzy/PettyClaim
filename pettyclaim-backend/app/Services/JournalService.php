<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Journal;
use Illuminate\Support\Str;

class JournalService
{
    /**
     * Menentukan Kode Akun Debit berdasarkan kata kunci Nama Merchant
     */
    public function determineAccountDebit(string $merchantName): string
    {
        $merchantLower = strtolower($merchantName);

        if (Str::contains($merchantLower, ['spbu', 'pertamina', 'bensin', 'shell', 'bp'])) {
            return '5002-Beban Transportasi & Bensin';
        }

        if (Str::contains($merchantLower, ['makan', 'sederhana', 'padang', 'resto', 'cafe', 'kopi'])) {
            return '5003-Beban Konsumsi Rapat';
        }

        if (Str::contains($merchantLower, ['gramedia', 'atk', 'kertas', 'fotocopy', 'print'])) {
            return '5004-Beban ATK & Cetak';
        }

        return '1101-Beban Operasional';
    }

    /**
     * Membuat Draf Jurnal Akuntansi otomatis jika klaim APPROVED
     * Format Penomoran Rapi: JRN-0001, JRN-0002, dst.
     */
    public function createJournalForClaim(Claim $claim): ?Journal
    {
        // Cegah pembuatan jurnal ganda jika klaim di-approve ulang
        $existingJournal = Journal::where('claim_id', $claim->id)->first();
        if ($existingJournal) {
            return $existingJournal;
        }

        $accountDebit = $this->determineAccountDebit($claim->merchant_name);

        // Cari nomor urut terakhir di database untuk generate penomoran urut rapi
        $lastJournal = Journal::latest('created_at')->first();
        $nextNumber = 1;

        if ($lastJournal && $lastJournal->journal_number) {
            $numberOnly = preg_replace('/[^0-9]/', '', $lastJournal->journal_number);
            if (!empty($numberOnly)) {
                $nextNumber = ((int) $numberOnly) + 1;
            }
        }

        // Format Penomoran: JRN-0001, JRN-0002, dst.
        $journalNumber = 'JRN-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return Journal::create([
            'claim_id'       => $claim->id,
            'journal_number' => $journalNumber,
            'account_debit'  => $accountDebit,
            'account_credit' => '1001-Kas Kecil',
            'amount'         => $claim->amount,
            'entry_date'     => now()->toDateString(),
            'is_exported'   => false,
        ]);
    }
}