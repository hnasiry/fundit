<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use App\Notifications\TransactionParticipantSmsNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

it('performs a card to card transfer', function (): void {
    Notification::fake();

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

    Notification::assertSentTo(
        $sourceUser,
        TransactionParticipantSmsNotification::class,
        function (TransactionParticipantSmsNotification $notification) use ($destinationCard, $response): bool {
            return $notification->message() === sprintf(
                'You sent %s to card %s. Ref: %s',
                number_format(150_000),
                $destinationCard->masked_number,
                $response->json('reference_number')
            );
        }
    );

    Notification::assertSentTo(
        $destinationUser,
        TransactionParticipantSmsNotification::class,
        function (TransactionParticipantSmsNotification $notification) use ($sourceCard, $response): bool {
            return $notification->message() === sprintf(
                'You received %s from card %s. Ref: %s',
                number_format(150_000),
                $sourceCard->masked_number,
                $response->json('reference_number')
            );
        }
    );
});

it('prevents transferring from a card owned by another user', function (): void {
    Notification::fake();

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

    Notification::assertNothingSent();
});
