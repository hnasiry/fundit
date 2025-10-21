<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\CardRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Eloquent\CardRepository;
use App\Repositories\Eloquent\TransactionRepository;
use App\Services\Notifications\Sms\KavenegarSmsProvider;
use App\Services\Notifications\Sms\SmsIrProvider;
use App\Services\Notifications\Sms\SmsManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CardRepositoryInterface::class, CardRepository::class);
        $this->app->singleton(TransactionRepositoryInterface::class, TransactionRepository::class);

        $this->app->singleton(SmsManager::class, function (): SmsManager {
            return new SmsManager([
                new KavenegarSmsProvider(config('sms.providers.kavenegar.api_key')),
                new SmsIrProvider(config('sms.providers.sms_ir.api_token')),
            ]);
        });
    }

    public function boot(): void
    {
        $this->bootModelsDefaults();
    }

    private function bootModelsDefaults(): void
    {
        Model::unguard();
    }
}
