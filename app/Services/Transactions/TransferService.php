<?php

declare(strict_types=1);

namespace App\Services\Transactions;

use App\Exceptions\CardNotFoundException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransferException;
use App\Models\Transaction;
use App\Repositories\Contracts\CardRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TransferService
{
    public function __construct(
        private readonly CardRepositoryInterface $cardRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly TransactionNotificationService $notificationService,
    ) {
    }

    public function transfer(string $sourceCardNumber, string $destinationCardNumber, int $amount): Transaction
    {
        if ($sourceCardNumber === $destinationCardNumber) {
            throw new InvalidTransferException('Source and destination cards must be different.');
        }

        if ($amount <= 0) {
            throw new InvalidTransferException('Transfer amount must be greater than zero.');
        }

        $transaction = DB::transaction(function () use ($sourceCardNumber, $destinationCardNumber, $amount): Transaction {
            $sourceCard = $this->cardRepository->findByNumberForUpdate($sourceCardNumber);
            if ($sourceCard === null) {
                throw new CardNotFoundException();
            }

            $destinationCard = $this->cardRepository->findByNumberForUpdate($destinationCardNumber);
            if ($destinationCard === null) {
                throw new CardNotFoundException();
            }

            if ($sourceCard->balance < $amount) {
                throw new InsufficientFundsException();
            }

            $this->cardRepository->decrementBalance($sourceCard, $amount);
            $this->cardRepository->incrementBalance($destinationCard, $amount);

            $transaction = $this->transactionRepository->create([
                'reference_number' => $this->generateReferenceNumber(),
                'source_card_id' => $sourceCard->id,
                'destination_card_id' => $destinationCard->id,
                'source_user_id' => $sourceCard->account->user_id,
                'destination_user_id' => $destinationCard->account->user_id,
                'amount' => $amount,
            ]);

            return $transaction->load(['sourceCard', 'destinationCard']);
        });

        $this->notificationService->notifyParticipants($transaction);

        return $transaction;
    }

    private function generateReferenceNumber(): string
    {
        return sprintf('TRX-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(6)));
    }
}
