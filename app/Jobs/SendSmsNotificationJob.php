<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Notifications\Sms\SmsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SendSmsNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly string $phoneNumber, private readonly string $message)
    {
    }

    public function handle(SmsManager $smsManager): void
    {
        $smsManager->send($this->phoneNumber, $this->message);
    }
}
