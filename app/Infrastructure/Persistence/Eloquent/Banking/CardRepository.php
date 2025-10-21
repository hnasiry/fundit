<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Banking;

use App\Domain\Banking\Repositories\CardRepositoryInterface;
use App\Models\Card;

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
