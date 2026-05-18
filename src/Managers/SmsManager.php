<?php

declare(strict_types=1);

namespace Banulakwin\Sms\Managers;

use Banulakwin\Sms\Contracts\SmsProviderInterface;
use Banulakwin\Sms\DTOs\SendSmsRequest;
use Banulakwin\Sms\DTOs\SendSmsResponse;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class SmsManager
{
    private ?string $driver = null;

    /**
     * @param  array<string, class-string<SmsProviderInterface>>  $drivers
     */
    public function __construct(
        private Container $container,
        private array $drivers,
        private string $defaultDriver,
    ) {}

    /**
     * Select a specific SMS driver (returns an immutable clone).
     */
    public function driver(?string $name = null): self
    {
        $clone = clone $this;
        $clone->driver = $name ?? $this->defaultDriver;

        return $clone;
    }

    /**
     * Resolve the SMS provider instance for the active driver.
     */
    public function provider(?string $name = null): SmsProviderInterface
    {
        $driver = $name ?? $this->driver ?? $this->defaultDriver;
        $providerClass = $this->drivers[$driver] ?? null;

        if ($providerClass === null) {
            Log::error('SMS provider resolution failed', [
                'driver' => $driver,
            ]);
            throw new InvalidArgumentException("SMS driver [{$driver}] is not configured.");
        }

        Log::info('SMS provider resolved', [
            'driver' => $driver,
            'provider_class' => $providerClass,
        ]);

        return $this->container->make($providerClass);
    }

    /**
     * Send an SMS using the active driver.
     */
    public function send(SendSmsRequest $request): SendSmsResponse
    {
        return $this->provider()->send($request);
    }

    /**
     * Schedule an SMS for future delivery using the active driver.
     *
     * @param  string  $scheduleTime  Format: Y-m-d H:i
     */
    public function schedule(SendSmsRequest $request, string $scheduleTime): SendSmsResponse
    {
        return $this->provider()->schedule($request, $scheduleTime);
    }
}
