<?php

declare(strict_types=1);

namespace App\Services\Notifications\Sms;

use InvalidArgumentException;

final class SmsManager
{
    /**
     * @param iterable<SmsProviderInterface> $providers
     */
    public function __construct(private readonly iterable $providers)
    {
    }

    public function send(string $phoneNumber, string $message, ?string $providerName = null): void
    {
        $provider = $this->resolve($providerName ?? config('sms.default'));
        $provider->send($phoneNumber, $message);
    }

    private function resolve(string $name): SmsProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->getName() === $name) {
                return $provider;
            }
        }

        throw new InvalidArgumentException(sprintf('SMS provider [%s] is not supported.', $name));
    }
}
