<?php

declare(strict_types=1);

use App\Exceptions\CardNotFoundException;
use App\Exceptions\InsufficientFundsException;
use App\Jobs\SendSmsNotificationJob;
use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use App\Services\Transactions\TransferService;
use Illuminate\Support\Facades\Queue;

it('transfers funds between cards and updates balances', function (): void {
    Queue::fake();

    $sourceUser = User::factory()->create();
    $destinationUser = User::factory()->create();

    $sourceAccount = Account::factory()->for($sourceUser)->create();
    $destinationAccount = Account::factory()->for($destinationUser)->create();

    $sourceCard = Card::factory()->for($sourceAccount)->create(['balance' => 200_000]);
    $destinationCard = Card::factory()->for($destinationAccount)->create(['balance' => 50_000]);

    $service = app(TransferService::class);
    $transaction = $service->transfer($sourceCard->number, $destinationCard->number, 75_000);

    expect($transaction->amount)->toBe(75_000)
        ->and($transaction->source_card_id)->toBe($sourceCard->id)
        ->and($transaction->destination_card_id)->toBe($destinationCard->id);

    expect($sourceCard->refresh()->balance)->toBe(125_000)
        ->and($destinationCard->refresh()->balance)->toBe(125_000);

    Queue::assertPushed(SendSmsNotificationJob::class, 2);
});

it('throws an exception when source card has insufficient funds', function (): void {
    Queue::fake();

    $sourceCard = Card::factory()
        ->for(Account::factory()->for(User::factory()))
        ->create(['balance' => 10_000]);

    $destinationCard = Card::factory()
        ->for(Account::factory()->for(User::factory()))
        ->create(['balance' => 10_000]);

    $service = app(TransferService::class);

    $service->transfer($sourceCard->number, $destinationCard->number, 50_000);
})->throws(InsufficientFundsException::class);

it('throws an exception when a card cannot be found', function (): void {
    Queue::fake();

    $destinationCard = Card::factory()
        ->for(Account::factory()->for(User::factory()))
        ->create(['balance' => 10_000]);

    $service = app(TransferService::class);

    $service->transfer('1234567890123456', $destinationCard->number, 5_000);
})->throws(CardNotFoundException::class);
