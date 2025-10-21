<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Card;
use App\Repositories\Contracts\CardRepositoryInterface;

final class CardRepository implements CardRepositoryInterface
{
    public function findByNumber(string $number): ?Card
    {
        return Card::with(['account.user'])->where('number', $number)->first();
    }

    public function findByNumberForUpdate(string $number): ?Card
    {
        return Card::with(['account.user'])
            ->where('number', $number)
            ->lockForUpdate()
            ->first();
    }

    public function decrementBalance(Card $card, int $amount): void
    {
        $card->balance -= $amount;
        $card->save();
        $card->refresh();
    }

    public function incrementBalance(Card $card, int $amount): void
    {
        $card->balance += $amount;
        $card->save();
        $card->refresh();
    }
}
