<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Card;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
final class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_number' => 'TRX-' . Str::upper(Str::random(10)),
            'amount' => fake()->numberBetween(10_000, 200_000),
        ];
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(function (Transaction $transaction): void {
                $this->ensureParticipants($transaction);
            })
            ->afterCreating(function (Transaction $transaction): void {
                $this->ensureParticipants($transaction, true);
            });
    }

    private function ensureParticipants(Transaction $transaction, bool $persisted = false): void
    {
        if (! $transaction->source_card_id) {
            $sourceCard = Card::factory()->create();
            $transaction->source_card_id = $sourceCard->id;
            $transaction->source_user_id = $sourceCard->account->user_id;
        } else {
            $sourceCard = $transaction->sourceCard ?? Card::find($transaction->source_card_id);
            if ($sourceCard !== null) {
                $transaction->source_user_id = $sourceCard->account->user_id;
                if ($persisted) {
                    $transaction->save();
                }
            }
        }

        if (! $transaction->destination_card_id) {
            $destinationCard = Card::factory()->create();
            $transaction->destination_card_id = $destinationCard->id;
            $transaction->destination_user_id = $destinationCard->account->user_id;
        } else {
            $destinationCard = $transaction->destinationCard ?? Card::find($transaction->destination_card_id);
            if ($destinationCard !== null) {
                $transaction->destination_user_id = $destinationCard->account->user_id;
                if ($persisted) {
                    $transaction->save();
                }
            }
        }
    }
}
