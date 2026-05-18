<?php

declare(strict_types=1);

namespace Banulakwin\Sms;

use Banulakwin\Sms\Contracts\SmsProviderInterface;
use Banulakwin\Sms\Managers\SmsManager;
use Illuminate\Support\ServiceProvider;

final class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sms.php', 'sms');

        /** @var array<string, class-string<SmsProviderInterface>> $providers */
        $providers = (array) config('sms.providers', []);

        foreach ($providers as $providerClass) {
            $this->app->bind($providerClass);
        }

        $this->app->singleton(SmsManager::class, fn ($app): SmsManager => new SmsManager(
            container: $app,
            drivers: $providers,
            defaultDriver: (string) config('sms.default', 'textlk'),
        ));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/sms.php' => config_path('sms.php'),
        ], 'sms-config');
    }
}
