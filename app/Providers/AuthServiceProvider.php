<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Card;
use App\Policies\CardPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Card::class => CardPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
