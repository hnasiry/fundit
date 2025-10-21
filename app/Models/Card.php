<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'number',
        'balance',
    ];

    protected $casts = [
        'balance' => 'int',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'source_card_id');
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'destination_card_id');
    }

    public function getMaskedNumberAttribute(): string
    {
        return substr($this->number, 0, 6) . '****' . substr($this->number, -4);
    }
}
