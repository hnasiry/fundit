<?php

declare(strict_types=1);

use App\Application\Banking\Services\TransferFundsService;
use App\Domain\Banking\DTO\TransferData;
use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

it('returns the top users with their latest transactions', function (): void {
    Notification::fake();

    $users = User::factory()->count(4)->create();

    $cards = $users->map(fn (User $user) => Card::factory()
        ->for(Account::factory()->for($user))
        ->create(['balance' => 1_000_000]));

    /** @var TransferFundsService $service */
    $service = app(TransferFundsService::class);

    // User 1 participates in 4 transactions.
    $service->handle(new TransferData($cards[0]->number, $cards[1]->number, 50_000));
    $service->handle(new TransferData($cards[1]->number, $cards[0]->number, 25_000));
    $service->handle(new TransferData($cards[0]->number, $cards[2]->number, 70_000));
    $service->handle(new TransferData($cards[3]->number, $cards[0]->number, 20_000));

    // User 2 participates in 3 transactions.
    $service->handle(new TransferData($cards[1]->number, $cards[2]->number, 10_000));
    $service->handle(new TransferData($cards[1]->number, $cards[3]->number, 15_000));

    // User 3 participates in 2 transactions.
    $service->handle(new TransferData($cards[2]->number, $cards[3]->number, 5_000));

    Sanctum::actingAs($users->first());

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
