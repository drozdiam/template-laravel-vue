<?php

namespace App\Enums;

enum TransactionType: string
{
    case Debit = 'debit';
    case Credit = 'credit';
    case Refund = 'refund';

    public function requiresCategory(): bool
    {
        return $this === self::Debit;
    }
}