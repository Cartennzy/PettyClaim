<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Journal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'claim_id',
        'journal_number',
        'account_debit',
        'account_credit',
        'amount',
        'entry_date',
        'is_exported',
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }
}