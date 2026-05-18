<?php

declare(strict_types=1);

namespace Banulakwin\Sms\Contracts;

use Banulakwin\Sms\DTOs\SendSmsRequest;
use Banulakwin\Sms\DTOs\SendSmsResponse;

interface SmsProviderInterface
{
    /**
     * Send an SMS to one or more recipients.
     */
    public function send(SendSmsRequest $request): SendSmsResponse;

    /**
     * Schedule an SMS for future delivery.
     *
     * @param  string  $scheduleTime  Format: Y-m-d H:i
     */
    public function schedule(SendSmsRequest $request, string $scheduleTime): SendSmsResponse;
}
