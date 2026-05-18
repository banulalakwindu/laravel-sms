<?php

declare(strict_types=1);

namespace Banulakwin\Sms\Providers\TextLk;

use Banulakwin\Sms\Contracts\SmsProviderInterface;
use Banulakwin\Sms\DTOs\SendSmsRequest;
use Banulakwin\Sms\DTOs\SendSmsResponse;
use Banulakwin\Sms\Enums\SmsProvider;
use Banulakwin\Sms\Enums\SmsStatus;
use Banulakwin\Sms\Exceptions\SmsException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TextLkSmsProvider implements SmsProviderInterface
{
    private const API_URL = 'https://app.text.lk/api/v3/sms/send';

    /**
     * Normalise a raw phone number to the Sri Lankan format 94XXXXXXXXX.
     *
     * Rules:
     *  - Correct input:  94712345678  → returned as-is
     *  - Strip leading zero: 0712345678  → 94712345678
     *  - Strip + prefix:     +94712345678 → 94712345678
     *  - Bare 9-digit:       712345678   → 94712345678
     *  - Non-94 numbers after normalisation → null (skip)
     */
    private function normalizeSriLankanNumber(string $raw): ?string
    {
        // Strip all whitespace and dashes
        $cleaned = preg_replace('/[\s\-]/', '', $raw) ?? '';

        // Strip leading '+'
        $cleaned = mb_ltrim($cleaned, '+');

        // Strip single leading zero → add 94 prefix
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '94' . mb_substr($cleaned, 1);
        }

        // Bare 9-digit local number (no country code, no leading zero)
        if (mb_strlen($cleaned) === 9 && ! str_starts_with($cleaned, '94')) {
            $cleaned = '94' . $cleaned;
        }

        // After normalisation must be 11 digits starting with 94
        if (! preg_match('/^94\d{9}$/', $cleaned)) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Filter and normalise recipients to valid Sri Lankan numbers (94XXXXXXXXX).
     * Numbers that cannot be normalised are skipped and logged.
     *
     * @param  array<int, string>  $recipients
     * @return array<int, string>
     */
    private function filterRecipients(array $recipients): array
    {
        $valid = [];
        $skipped = [];

        foreach ($recipients as $raw) {
            $normalized = $this->normalizeSriLankanNumber($raw);

            if ($normalized === null) {
                $skipped[] = $raw;
            } else {
                $valid[] = $normalized;
            }
        }

        if ($skipped !== []) {
            Log::info('TextLK SMS: skipped non-Sri-Lankan numbers', ['skipped' => $skipped]);
        }

        return $valid;
    }

    private function handleError(int $httpCode, array $response): string
    {
        $errors = [
            400 => 'Bad Request - Check your parameters',
            401 => 'Unauthorized - Invalid API key',
            403 => 'Forbidden - Insufficient balance or permissions',
            404 => 'Not Found - Invalid endpoint',
            429 => 'Rate Limited - Too many requests',
            500 => 'Server Error - Try again later',
        ];

        $defaultError = $errors[$httpCode] ?? 'Unknown error occurred';

        if (isset($response['message']) && is_string($response['message'])) {
            return $response['message'];
        }

        return $defaultError;
    }

    private function executeRequest(SendSmsRequest $request, ?string $scheduleTime = null): SendSmsResponse
    {
        $validRecipients = $this->filterRecipients($request->recipients);

        if ($validRecipients === []) {
            Log::info('TextLK SMS: no valid Sri Lankan recipients — send skipped.');

            return new SendSmsResponse(
                success: true,
                status: SmsStatus::Sent,
                provider: SmsProvider::TextLk,
                message: 'No valid Sri Lankan recipients — send skipped.',
                raw: [],
            );
        }

        $recipientString = implode(',', $validRecipients);
        $senderId = $request->senderId ?? (string) config('sms.textlk.sender_id');
        $apiKey = (string) config('sms.textlk.api_key');

        $logContext = [
            'recipients' => $recipientString,
            'message_length' => mb_strlen($request->message),
            'sender_id' => $senderId,
        ];

        if ($scheduleTime !== null) {
            $logContext['schedule_time'] = $scheduleTime;
        }

        Log::info('TextLK SMS request started', $logContext);

        try {
            $payload = [
                'recipient' => $recipientString,
                'sender_id' => $senderId,
                'type' => $request->type,
                'message' => $request->message,
            ];

            if ($scheduleTime !== null) {
                $payload['schedule_time'] = $scheduleTime;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ])
                ->asJson()
                ->post(self::API_URL, $payload);

            /** @var array<string, mixed> $body */
            $body = $response->json() ?? [];
            $httpCode = $response->status();

            if ($response->successful() && ($body['status'] ?? '') === 'success') {
                $cost = isset($body['data']['cost'])
                    ? number_format((float) $body['data']['cost'], 2)
                    : null;

                $logContext['cost'] = $cost;
                Log::info('TextLK SMS processed successfully', $logContext);

                return new SendSmsResponse(
                    success: true,
                    status: $scheduleTime ? SmsStatus::Scheduled : SmsStatus::Sent,
                    provider: SmsProvider::TextLk,
                    message: (string) ($body['message'] ?? 'SMS processed successfully'),
                    cost: $cost,
                    raw: $body,
                );
            }

            $errorMessage = $this->handleError($httpCode, $body);

            $logContext['http_status'] = $httpCode;
            $logContext['provider_message'] = $errorMessage;
            Log::error('TextLK SMS request failed', $logContext);

            return new SendSmsResponse(
                success: false,
                status: SmsStatus::Failed,
                provider: SmsProvider::TextLk,
                message: $errorMessage,
                raw: $body,
            );
        } catch (Throwable $e) {
            $logContext['error'] = $e->getMessage();
            Log::error('TextLK SMS request failed with exception', $logContext);

            throw new SmsException(
                message: 'TextLK SMS request failed: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function send(SendSmsRequest $request): SendSmsResponse
    {
        return $this->executeRequest($request);
    }

    public function schedule(SendSmsRequest $request, string $scheduleTime): SendSmsResponse
    {
        return $this->executeRequest($request, $scheduleTime);
    }
}
