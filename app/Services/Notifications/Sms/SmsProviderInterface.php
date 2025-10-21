<?php

declare(strict_types=1);

namespace App\Services\Notifications\Sms;

interface SmsProviderInterface
{
    public function send(string $phoneNumber, string $message): void;

    public function getName(): string;
}
