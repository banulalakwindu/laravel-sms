<?php

declare(strict_types=1);

use Banulakwin\Sms\Managers\SmsManager;

if (! function_exists('sms')) {
    function sms(): SmsManager
    {
        return app(SmsManager::class);
    }
}
