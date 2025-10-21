<?php

declare(strict_types=1);

use App\Jobs\SendSmsNotificationJob;
use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('performs a card to card transfer', function (): void {
    Queue::fake();

    $sourceUser = User::factory()->create();
    $destinationUser = User::factory()->create();

    $sourceAccount = Account::factory()->for($sourceUser)->create();
    $destinationAccount = Account::factory()->for($destinationUser)->create();

    $sourceCard = Card::factory()->for($sourceAccount)->create(['balance' => 500_000]);
    $destinationCard = Card::factory()->for($destinationAccount)->create(['balance' => 100_000]);

    $response = $this->postJson('/api/transactions/transfer', [
        'source_card' => $sourceCard->number,
        'destination_card' => $destinationCard->number,
        'amount' => 150_000,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'reference_number',
        ])
        ->assertJson(['success' => true]);

    expect($sourceCard->refresh()->balance)->toBe(350_000)
        ->and($destinationCard->refresh()->balance)->toBe(250_000);

    Queue::assertPushed(SendSmsNotificationJob::class, 2);
});
