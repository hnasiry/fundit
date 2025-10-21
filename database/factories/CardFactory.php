<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
final class CardFactory extends Factory
{
    protected $model = Card::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'number' => (string) fake()->unique()->numerify('###############') . fake()->randomDigit(),
            'balance' => fake()->numberBetween(100_000, 1_000_000),
        ];
    }
}
