<?php

declare(strict_types=1);

namespace App\Application\Banking\Services;

use App\Domain\Banking\DTO\TransferData;
use App\Domain\Banking\Services\FundsTransferService;
use App\Models\Transaction;

final class TransferFundsService
{
    public function __construct(
        private readonly FundsTransferService $fundsTransferService,
        private readonly TransactionNotificationService $notificationService,
    ) {
    }

    public function handle(TransferData $transferData): Transaction
    {
        $transaction = $this->fundsTransferService->execute($transferData);

        $this->notificationService->notifyParticipants($transaction);

        return $transaction;
    }
}
