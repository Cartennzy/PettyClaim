<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Claim extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'merchant_name',
        'amount',
        'transaction_date',
        'receipt_image_path',
        'receipt_s3_url',
        'phash_value',
        'is_flagged_duplicate',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_flagged_duplicate' => 'boolean',
        'approved_at' => 'datetime',
        'transaction_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function journal()
    {
        return $this->hasOne(Journal::class, 'claim_id');
    }
}