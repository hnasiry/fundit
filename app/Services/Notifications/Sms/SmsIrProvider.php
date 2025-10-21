<?php

declare(strict_types=1);

namespace App\Services\Notifications\Sms;

use Illuminate\Support\Facades\Log;

final class SmsIrProvider implements SmsProviderInterface
{
    public function __construct(private readonly ?string $apiToken = null)
    {
    }

    public function send(string $phoneNumber, string $message): void
    {
        Log::info('Sending SMS via SMS.ir', [
            'phone' => $phoneNumber,
            'message' => $message,
            'api_token_present' => $this->apiToken !== null,
        ]);
    }

    public function getName(): string
    {
        return 'sms_ir';
    }
}
