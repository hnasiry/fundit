<?php

declare(strict_types=1);

namespace App\Domain\Banking\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Transaction;

    public function getTopUsersWithRecentTransactions(int $usersLimit, int $transactionsLimit): Collection;
}
