<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function create(array $attributes): Transaction;

    public function getTopUsersWithRecentTransactions(int $usersLimit, int $transactionsLimit): Collection;
}
