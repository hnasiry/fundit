<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use App\Services\Transactions\TransferService;
use Illuminate\Support\Facades\Queue;

it('returns the top users with their latest transactions', function (): void {
    Queue::fake();

    $users = User::factory()->count(4)->create();

    $cards = $users->map(fn (User $user) => Card::factory()
        ->for(Account::factory()->for($user))
        ->create(['balance' => 1_000_000]));

    /** @var TransferService $service */
    $service = app(TransferService::class);

    // User 1 participates in 4 transactions.
    $service->transfer($cards[0]->number, $cards[1]->number, 50_000);
    $service->transfer($cards[1]->number, $cards[0]->number, 25_000);
    $service->transfer($cards[0]->number, $cards[2]->number, 70_000);
    $service->transfer($cards[3]->number, $cards[0]->number, 20_000);

    // User 2 participates in 3 transactions.
    $service->transfer($cards[1]->number, $cards[2]->number, 10_000);
    $service->transfer($cards[1]->number, $cards[3]->number, 15_000);

    // User 3 participates in 2 transactions.
    $service->transfer($cards[2]->number, $cards[3]->number, 5_000);

    $response = $this->getJson('/api/reports/top-users');

    $response->assertOk()
        ->assertJsonStructure([
            'top_users' => [
                '*'=> [
                    'user_id',
                    'name',
                    'total_transactions',
                    'latest_transactions' => [
                        '*' => [
                            'reference_number',
                            'source_card',
                            'destination_card',
                            'amount',
                            'created_at',
                        ],
                    ],
                ],
            ],
        ]);

    $topUsers = $response->json('top_users');

    expect($topUsers)->toHaveCount(3)
        ->and($topUsers[0]['total_transactions'])->toBeGreaterThanOrEqual($topUsers[1]['total_transactions']);

    expect(collect($topUsers)->pluck('user_id'))->toContain((string) $users[0]->id);
});
