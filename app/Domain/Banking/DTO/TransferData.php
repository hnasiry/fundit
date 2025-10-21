<?php

declare(strict_types=1);

namespace App\Domain\Banking\DTO;

final class TransferData
{
    public function __construct(
        private readonly string $sourceCardNumber,
        private readonly string $destinationCardNumber,
        private readonly int $amount,
    ) {
    }

    public function sourceCardNumber(): string
    {
        return $this->sourceCardNumber;
    }

    public function destinationCardNumber(): string
    {
        return $this->destinationCardNumber;
    }

    public function amount(): int
    {
        return $this->amount;
    }
}
