<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Banking\Repositories\CardRepositoryInterface;
use App\Domain\Banking\Repositories\TransactionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Banking\CardRepository;
use App\Infrastructure\Persistence\Eloquent\Banking\TransactionRepository;
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
