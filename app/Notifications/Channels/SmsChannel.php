<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Notifications\Messages\SmsMessage;
use App\Services\Notifications\Sms\SmsManager;
use Illuminate\Notifications\Notification;

final class SmsChannel
{
    public function __construct(private readonly SmsManager $smsManager)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        $phoneNumber = $notifiable->routeNotificationFor('sms', $notification);

        if (! is_string($phoneNumber) || $phoneNumber === '') {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (is_string($message)) {
            $message = new SmsMessage($message);
        }

        if (! $message instanceof SmsMessage) {
            return;
        }

        $this->smsManager->send($phoneNumber, $message->content(), $message->provider());
    }
}
