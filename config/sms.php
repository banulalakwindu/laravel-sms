<?php

declare(strict_types=1);

use Banulakwin\Sms\Providers\TextLk\TextLkSmsProvider;

return [
    'default' => env('SMS_DRIVER', 'textlk'),

    'providers' => [
        'textlk' => TextLkSmsProvider::class,
        // Add more providers here later as needed.
    ],

    'textlk' => [
        'api_key' => env('TEXTLK_SMS_API_KEY'),
        'sender_id' => env('TEXTLK_SMS_SENDER_ID'),
    ],
];
