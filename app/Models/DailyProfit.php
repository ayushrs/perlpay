<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProfit extends Model
{
    protected $fillable = [
        'user_subscription_id',
        'user_id',
        'profit_date',
        'amount',
        'status',
        'wallet_transaction_id',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'profit_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            UserSubscription::class,
            'user_subscription_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}