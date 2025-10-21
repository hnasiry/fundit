<?php

declare(strict_types=1);

namespace App\Domain\Banking\Services;

use App\Domain\Banking\DTO\TransferData;
use App\Domain\Banking\Repositories\CardRepositoryInterface;
use App\Domain\Banking\Repositories\TransactionRepositoryInterface;
use App\Exceptions\CardNotFoundException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransferException;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class FundsTransferService
{
    public function __construct(
        private readonly CardRepositoryInterface $cardRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function execute(TransferData $transferData): Transaction
    {
        if ($transferData->sourceCardNumber() === $transferData->destinationCardNumber()) {
            throw new InvalidTransferException('Source and destination cards must be different.');
        }

        if ($transferData->amount() <= 0) {
            throw new InvalidTransferException('Transfer amount must be greater than zero.');
        }

        return DB::transaction(function () use ($transferData): Transaction {
            $sourceCard = $this->cardRepository->findByNumberForUpdate($transferData->sourceCardNumber());
            if ($sourceCard === null) {
                throw new CardNotFoundException();
            }

            $destinationCard = $this->cardRepository->findByNumberForUpdate($transferData->destinationCardNumber());
            if ($destinationCard === null) {
                throw new CardNotFoundException();
            }

            if ($sourceCard->balance < $transferData->amount()) {
                throw new InsufficientFundsException();
            }

            $this->cardRepository->decrementBalance($sourceCard, $transferData->amount());
            $this->cardRepository->incrementBalance($destinationCard, $transferData->amount());

            $transaction = $this->transactionRepository->create([
                'reference_number' => $this->generateReferenceNumber(),
                'source_card_id' => $sourceCard->id,
                'destination_card_id' => $destinationCard->id,
                'source_user_id' => $sourceCard->account->user_id,
                'destination_user_id' => $destinationCard->account->user_id,
                'amount' => $transferData->amount(),
            ]);

            return $transaction->load(['sourceCard', 'destinationCard']);
        });
    }

    private function generateReferenceNumber(): string
    {
        return sprintf('TRX-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(6)));
    }
}
