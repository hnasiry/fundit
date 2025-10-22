<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Banking;

use App\Domain\Banking\Repositories\TransactionRepositoryInterface;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Transaction
    {
        return Transaction::create($attributes);
    }

    public function getTopUsersWithRecentTransactions(int $usersLimit, int $transactionsLimit): Collection
    {
        $rows = collect(DB::select($this->getRawQuery(), [
            'users_limit' => $usersLimit,
            'transactions_limit' => $transactionsLimit,
        ]));

        if ($rows->isEmpty()) {
            return collect();
        }

        return $rows
            ->groupBy('user_id')
            ->map(static function (Collection $userRows): User {
                $firstRow = $userRows->first();

                $user = new User()->forceFill([
                    'id'   => (int)$firstRow->user_id,
                    'name' => $firstRow->name,
                ]);
                $user->exists = true;
                $user->setAttribute('total_transactions', (int)$firstRow->total_transactions);

                $transactions = $userRows
                    ->filter(static fn($row): bool => $row->transaction_id !== null)
                    ->map(static function ($row): Transaction {
                        $transaction = new Transaction()->forceFill([
                            'id'                  => (int)$row->transaction_id,
                            'reference_number'    => $row->reference_number,
                            'source_card_id'      => $row->source_card_id !== null ? (int)$row->source_card_id : null,
                            'destination_card_id' => $row->destination_card_id !== null ? (int)$row->destination_card_id : null,
                            'amount'              => (int)$row->amount,
                        ]);
                        $transaction->exists = true;

                        if ($row->transaction_created_at !== null) {
                            $transaction->setAttribute('created_at', Carbon::parse($row->transaction_created_at));
                        }

                        if ($row->source_card_id !== null) {
                            $sourceCard = new Card();
                            $sourceCard->forceFill([
                                'id'     => (int)$row->source_card_id,
                                'number' => $row->source_card_number,
                            ]);
                            $sourceCard->exists = true;
                            $transaction->setRelation('sourceCard', $sourceCard);
                        } else {
                            $transaction->setRelation('sourceCard', null);
                        }

                        if ($row->destination_card_id !== null) {
                            $destinationCard = new Card();
                            $destinationCard->forceFill([
                                'id'     => (int)$row->destination_card_id,
                                'number' => $row->destination_card_number,
                            ]);
                            $destinationCard->exists = true;
                            $transaction->setRelation('destinationCard', $destinationCard);
                        } else {
                            $transaction->setRelation('destinationCard', null);
                        }

                        return $transaction;
                    })
                    ->values();

                $user->setRelation('recentTransactions', $transactions);

                return $user;
            })
            ->values();
    }

    /**
     * @return string
     */
    public function getRawQuery(): string
    {
        return <<<'SQL'
WITH user_transactions AS (
    SELECT
        t.id AS transaction_id,
        t.reference_number,
        t.source_card_id,
        t.destination_card_id,
        t.amount,
        t.created_at,
        t.source_user_id AS user_id
    FROM transactions t
    WHERE t.source_user_id IS NOT NULL
    UNION ALL
    SELECT
        t.id AS transaction_id,
        t.reference_number,
        t.source_card_id,
        t.destination_card_id,
        t.amount,
        t.created_at,
        t.destination_user_id AS user_id
    FROM transactions t
    WHERE t.destination_user_id IS NOT NULL
),
ranked_transactions AS (
    SELECT
        ut.user_id,
        ut.transaction_id,
        ut.reference_number,
        ut.source_card_id,
        ut.destination_card_id,
        ut.amount,
        ut.created_at,
        ROW_NUMBER() OVER (
            PARTITION BY ut.user_id
            ORDER BY ut.created_at DESC, ut.transaction_id DESC
        ) AS row_number
    FROM user_transactions ut
),
transaction_counts AS (
    SELECT
        ut.user_id,
        COUNT(*) AS total_transactions
    FROM user_transactions ut
    GROUP BY ut.user_id
),
top_users AS (
    SELECT
        u.id AS user_id,
        u.name,
        tc.total_transactions
    FROM transaction_counts tc
    INNER JOIN users u ON u.id = tc.user_id
    ORDER BY tc.total_transactions DESC, u.id ASC
    LIMIT :users_limit
)
SELECT
    tu.user_id,
    tu.name,
    tu.total_transactions,
    rt.transaction_id,
    rt.reference_number,
    rt.amount,
    rt.created_at AS transaction_created_at,
    rt.source_card_id,
    rt.destination_card_id,
    sc.number AS source_card_number,
    dc.number AS destination_card_number
FROM top_users tu
LEFT JOIN ranked_transactions rt
    ON tu.user_id = rt.user_id
    AND rt.row_number <= :transactions_limit
LEFT JOIN cards AS sc ON rt.source_card_id = sc.id
LEFT JOIN cards AS dc ON rt.destination_card_id = dc.id
ORDER BY
    tu.total_transactions DESC,
    tu.user_id ASC,
    rt.created_at DESC,
    rt.transaction_id DESC
SQL;
    }
}
