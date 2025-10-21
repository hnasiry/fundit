<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

final class CardPolicy
{
    public function transfer(User $user, Card $card): bool
    {
        return $card->account?->user_id === $user->id;
    }
}
