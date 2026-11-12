<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WalletMutation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'claim_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'title',
        'description',
        'channel',
        'account_number',
        'account_name',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }
}