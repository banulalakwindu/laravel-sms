<?php

declare(strict_types=1);

namespace Banulakwin\Sms\Enums;

enum SmsStatus: string
{
    case Sent = 'sent';
    case Scheduled = 'scheduled';
    case Failed = 'failed';
}
