<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Card;

interface CardRepositoryInterface
{
    public function findByNumber(string $number): ?Card;

    public function findByNumberForUpdate(string $number): ?Card;

    public function decrementBalance(Card $card, int $amount): void;

    public function incrementBalance(Card $card, int $amount): void;
}
