<?php

declare(strict_types=1);

use App\Jobs\SendSmsNotificationJob;
use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

it('performs a card to card transfer', function (): void {
    Queue::fake();

    $sourceUser = User::factory()->create();
    $destinationUser = User::factory()->create();

    $sourceAccount = Account::factory()->for($sourceUser)->create();
    $destinationAccount = Account::factory()->for($destinationUser)->create();

    $sourceCard = Card::factory()->for($sourceAccount)->create(['balance' => 500_000]);
    $destinationCard = Card::factory()->for($destinationAccount)->create(['balance' => 100_000]);

    Sanctum::actingAs($sourceUser);

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

it('prevents transferring from a card owned by another user', function (): void {
    Queue::fake();

    $authorizedUser = User::factory()->create();
    $otherUser = User::factory()->create();

    $authorizedAccount = Account::factory()->for($authorizedUser)->create();
    $otherAccount = Account::factory()->for($otherUser)->create();

    $authorizedCard = Card::factory()->for($authorizedAccount)->create(['balance' => 200_000]);
    $otherCard = Card::factory()->for($otherAccount)->create(['balance' => 100_000]);

    Sanctum::actingAs($authorizedUser);

    $response = $this->postJson('/api/transactions/transfer', [
        'source_card' => $otherCard->number,
        'destination_card' => $authorizedCard->number,
        'amount' => 50_000,
    ]);

    $response->assertForbidden();

    expect($authorizedCard->refresh()->balance)->toBe(200_000)
        ->and($otherCard->refresh()->balance)->toBe(100_000);

    Queue::assertNothingPushed();
});
