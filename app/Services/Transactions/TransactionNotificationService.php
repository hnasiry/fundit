<?php

declare(strict_types=1);

namespace App\Services\Transactions;

use App\Jobs\SendSmsNotificationJob;
use App\Models\Transaction;

final class TransactionNotificationService
{
    public function notifyParticipants(Transaction $transaction): void
    {
        $transaction->loadMissing([
            'sourceCard.account.user',
            'destinationCard.account.user',
        ]);

        $senderPhone = $transaction->sourceCard->account->user->phone ?? null;
        $receiverPhone = $transaction->destinationCard->account->user->phone ?? null;

        if ($senderPhone) {
            SendSmsNotificationJob::dispatch(
                $senderPhone,
                sprintf(
                    'You sent %s to card %s. Ref: %s',
                    number_format($transaction->amount),
                    $transaction->destinationCard->masked_number,
                    $transaction->reference_number
                )
            );
        }

        if ($receiverPhone) {
            SendSmsNotificationJob::dispatch(
                $receiverPhone,
                sprintf(
                    'You received %s from card %s. Ref: %s',
                    number_format($transaction->amount),
                    $transaction->sourceCard->masked_number,
                    $transaction->reference_number
                )
            );
        }
    }
}
