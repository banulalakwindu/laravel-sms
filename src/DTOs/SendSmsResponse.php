<?php

declare(strict_types=1);

namespace Banulakwin\Sms\DTOs;

use Banulakwin\Sms\Enums\SmsProvider;
use Banulakwin\Sms\Enums\SmsStatus;

final class SendSmsResponse
{
    /**
     * @param  bool  $success  Whether the SMS was accepted by the provider.
     * @param  SmsStatus  $status  Delivery status.
     * @param  SmsProvider  $provider  Which provider handled the request.
     * @param  string  $message  Human-readable status message.
     * @param  string|null  $cost  Cost of the SMS if returned by the provider.
     * @param  array<string, mixed>  $raw  Full raw provider response for debugging.
     */
    public function __construct(
        public bool $success,
        public SmsStatus $status,
        public SmsProvider $provider,
        public string $message = '',
        public ?string $cost = null,
        public array $raw = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status->value,
            'provider' => $this->provider->value,
            'message' => $this->message,
            'cost' => $this->cost,
            'raw' => $this->raw,
        ];
    }
}
