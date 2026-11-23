<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'bybit_tx_id',
    'type',
    'status',
    'amount',
    'currency',
    'fiat_amount',
    'fiat_currency',
    'merchant',
    'description',
    'category_id',
    'occurred_at',
    'raw_payload',
    'telegram_message_id',
    'categorized_at',
])]
class Transaction extends Model
{
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:8',
            'fiat_amount' => 'decimal:2',
            'occurred_at' => 'datetime',
            'categorized_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}