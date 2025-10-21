<?php

declare(strict_types=1);

namespace App\Services\Notifications\Sms;

use Illuminate\Support\Facades\Log;

final class KavenegarSmsProvider implements SmsProviderInterface
{
    public function __construct(private readonly ?string $apiKey = null)
    {
    }

    public function send(string $phoneNumber, string $message): void
    {
        Log::info('Sending SMS via Kavenegar', [
            'phone' => $phoneNumber,
            'message' => $message,
            'api_key_present' => $this->apiKey !== null,
        ]);
    }

    public function getName(): string
    {
        return 'kavenegar';
    }
}
