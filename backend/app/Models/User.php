<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'telegram_id',
    'telegram_username',
    'name',
    'email',
    'password',
    'base_fiat',
    'bybit_api_key',
    'bybit_api_secret',
    'bybit_connected_at',
    'is_admin',
])]
#[Hidden(['password', 'remember_token', 'bybit_api_key', 'bybit_api_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'bybit_connected_at' => 'datetime',
            'bybit_api_key' => 'encrypted',
            'bybit_api_secret' => 'encrypted',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function hasBybitConnected(): bool
    {
        return $this->bybit_api_key !== null
            && $this->bybit_api_secret !== null
            && $this->bybit_connected_at !== null;
    }
}
