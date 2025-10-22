<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Notifications\Channels\SmsChannel;
use App\Notifications\Messages\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class TransactionParticipantSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $message,
        private readonly ?string $provider = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms(object $notifiable): SmsMessage
    {
        return new SmsMessage($this->message, $this->provider);
    }

    public function message(): string
    {
        return $this->message;
    }
}
