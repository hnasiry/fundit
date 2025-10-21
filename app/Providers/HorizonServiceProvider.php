<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Str;
use function collect;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

final class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Horizon::auth(function (?User $user): bool {
            if (app()->environment('local', 'testing')) {
                return true;
            }

            $allowedViewers = collect(explode(',', (string) env('HORIZON_VIEWER_EMAILS', '')))
                ->map(fn (string $email): string => Str::lower(trim($email)))
                ->filter();

            return $user !== null && $allowedViewers->contains(Str::lower($user->email));
        });
    }

}
