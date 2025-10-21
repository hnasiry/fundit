<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function collect;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

final class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Horizon::auth(function (Request $request): bool {
            if (app()->environment('local', 'testing')) {
                return true;
            }

            $allowedViewers = collect(config('horizon.allowed_viewer_emails'))
                ->map(fn(string $email): string => Str::lower(trim($email)))
                ->filter();

            $user = $request->user();

            return $user !== null && $allowedViewers->contains(Str::lower($user->email));
        });
    }

}
