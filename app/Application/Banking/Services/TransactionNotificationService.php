<?php

declare(strict_types=1);

namespace App\Application\Banking\Services;

use App\Models\Transaction;
use App\Notifications\TransactionParticipantSmsNotification;

final class TransactionNotificationService
{
    public function notifyParticipants(Transaction $transaction): void
    {
        $transaction->loadMissing([
            'sourceCard.account.user',
            'destinationCard.account.user',
        ]);

        $sender = $transaction->sourceCard->account->user;
        $receiver = $transaction->destinationCard->account->user;

        if ($sender !== null) {
            $sender->notify(new TransactionParticipantSmsNotification(
                sprintf(
                    'You sent %s to card %s. Ref: %s',
                    number_format($transaction->amount),
                    $transaction->destinationCard->masked_number,
                    $transaction->reference_number
                )
            ));
        }

        if ($receiver !== null) {
            $receiver->notify(new TransactionParticipantSmsNotification(
                sprintf(
                    'You received %s from card %s. Ref: %s',
                    number_format($transaction->amount),
                    $transaction->sourceCard->masked_number,
                    $transaction->reference_number
                )
            ));
        }
    }
}
