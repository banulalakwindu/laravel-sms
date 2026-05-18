<?php

declare(strict_types=1);

namespace Banulakwin\Sms\DTOs;

final class SendSmsRequest
{
    /**
     * @param  array<int, string>  $recipients  Phone numbers in 94XXXXXXXXX format.
     * @param  string  $message  SMS body content.
     * @param  string|null  $senderId  Override sender ID (uses config default if null).
     * @param  string  $type  Message type (default: plain).
     * @param  array<string, mixed>  $metadata  Arbitrary key-value bag for app-level tracking.
     */
    public function __construct(
        public array $recipients,
        public string $message,
        public ?string $senderId = null,
        public string $type = 'plain',
        public array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'recipients' => $this->recipients,
            'message' => $this->message,
            'sender_id' => $this->senderId,
            'type' => $this->type,
            'metadata' => $this->metadata,
        ];
    }
}
