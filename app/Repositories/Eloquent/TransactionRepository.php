<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Support\Collection;

final class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $attributes): Transaction
    {
        return Transaction::create($attributes);
    }

    public function getTopUsersWithRecentTransactions(int $usersLimit, int $transactionsLimit): Collection
    {
        $users = User::query()
            ->select('users.*')
            ->selectRaw('(
                SELECT COUNT(*) FROM transactions
                WHERE transactions.source_user_id = users.id
                   OR transactions.destination_user_id = users.id
            ) as total_transactions')
            ->orderByDesc('total_transactions')
            ->limit($usersLimit)
            ->get()
            ->filter(static fn (User $user): bool => (int) $user->total_transactions > 0)
            ->values();

        $users->each(function (User $user) use ($transactionsLimit): void {
            $user->setRelation(
                'recentTransactions',
                Transaction::query()
                    ->with(['sourceCard', 'destinationCard'])
                    ->where(static function ($query) use ($user): void {
                        $query->where('source_user_id', $user->id)
                            ->orWhere('destination_user_id', $user->id);
                    })
                    ->orderByDesc('created_at')
                    ->limit($transactionsLimit)
                    ->get()
            );
        });

        return $users;
    }
}
