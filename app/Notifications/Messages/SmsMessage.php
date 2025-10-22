<?php

declare(strict_types=1);

namespace App\Notifications\Messages;

final class SmsMessage
{
    public function __construct(
        private readonly string $content,
        private readonly ?string $provider = null,
    ) {
    }

    public function content(): string
    {
        return $this->content;
    }

    public function provider(): ?string
    {
        return $this->provider;
    }

    public function via(string $provider): self
    {
        return new self($this->content, $provider);
    }
}
