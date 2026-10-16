<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechargeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_reference',
        'proof_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
        'wallet_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    
public function walletTransaction()
{
    return $this->belongsTo(
        \App\Models\WalletTransaction::class,
        'wallet_transaction_id'
    );
}

}