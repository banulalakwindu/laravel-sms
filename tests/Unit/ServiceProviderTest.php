<?php

declare(strict_types=1);

namespace Banulakwin\Sms\Tests\Unit;

use Banulakwin\Sms\DTOs\SendSmsRequest;
use Banulakwin\Sms\DTOs\SendSmsResponse;
use Banulakwin\Sms\Enums\SmsProvider;
use Banulakwin\Sms\Enums\SmsStatus;
use Banulakwin\Sms\Managers\SmsManager;
use Banulakwin\Sms\SmsServiceProvider;
use Banulakwin\Sms\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_has_service_provider(): void
    {
        $this->assertTrue(class_exists(SmsServiceProvider::class));
    }

    #[Test]
    public function it_has_sms_manager(): void
    {
        $this->assertTrue(class_exists(SmsManager::class));
    }

    #[Test]
    public function it_has_helper_function(): void
    {
        $this->assertTrue(function_exists('sms'));
    }

    #[Test]
    public function it_can_create_request_dto(): void
    {
        $request = new SendSmsRequest(
            recipients: ['94712345678'],
            message: 'Test message',
        );

        $this->assertSame(['94712345678'], $request->recipients);
        $this->assertSame('Test message', $request->message);
        $this->assertSame('plain', $request->type);
    }

    #[Test]
    public function it_can_create_response_dto(): void
    {
        $response = new SendSmsResponse(
            success: true,
            status: SmsStatus::Sent,
            provider: SmsProvider::TextLk,
            message: 'Sent successfully',
            cost: '1.50',
        );

        $this->assertTrue($response->success);
        $this->assertSame(SmsStatus::Sent, $response->status);
        $this->assertSame(SmsProvider::TextLk, $response->provider);
        $this->assertSame('1.50', $response->cost);
    }
}
