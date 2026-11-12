<?php

namespace App\Repositories;

use App\Models\Claim;
use App\Models\JournalEntry;
use App\Repositories\Interfaces\ClaimRepositoryInterface;
use Illuminate\Support\Collection;

class ClaimRepository implements ClaimRepositoryInterface
{
    public function create(array $data): Claim
    {
        return Claim::create($data);
    }

    public function getPendingClaims(): Collection
    {
        return Claim::with('user')->where('status', 'pending')->latest()->get();
    }

    public function updateStatus(string $id, string $status, ?string $note = null): Claim
    {
        $claim = Claim::findOrFail($id);
        $claim->update([
            'status' => $status,
            'rejection_note' => $note
        ]);

        // Auto-Journaling Trigger on Approval
        if ($status === 'approved') {
            JournalEntry::create([
                'claim_id' => $claim->id,
                'account_debit' => '5101-Beban Operasional',
                'account_credit' => '1101-Kas Kecil',
                'amount' => $claim->total_amount,
                'entry_date' => now()->toDateString(),
            ]);
        }

        return $claim;
    }
}