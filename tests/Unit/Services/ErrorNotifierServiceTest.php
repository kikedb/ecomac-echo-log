<?php

namespace Ecomac\EchoLog\Tests\Unit;

use Ecomac\EchoLog\Tests\TestCase;
use Ecomac\EchoLog\Services\ErrorNotifierService;
use Ecomac\EchoLog\Services\DiscordService;
use Ecomac\EchoLog\Services\EmailService;
use Ecomac\EchoLog\Contracts\ClockProvider;
use Ecomac\EchoLog\Services\ErrorAnalizerService;
use Ecomac\EchoLog\Dto\ErrorCategoryDto;


class ErrorNotifierServiceTest extends TestCase
{
    public ClockProvider $clock;

     protected function setUp(): void
    {
        parent::setUp();

        $this->clock = app()->make(ClockProvider::class);
    }

    public function test_sends_discord_and_email_notifications()
    {
        $message = "Error de prueba";
        $count = 5;
        $scanWindow = 10;

        $now = $this->clock->createFromFormat('Y-m-d H:i:s', '2024-05-19 15:00:00');
                
        $clockMock = $this->createMock(ClockProvider::class);
        $clockMock->method('now')->willReturn($now);

        $discordServiceMock = $this->createMock(DiscordService::class);
        $discordServiceMock->expects($this->once())
            ->method('sendNotification');

        $emailServiceMock = $this->createMock(EmailService::class);
        $emailServiceMock->expects($this->once())
            ->method('sendNotification');

        $analizerMock = $this->createMock(ErrorAnalizerService::class);
        $analizerMock->method('categorize')
            ->willReturn(new ErrorCategoryDto('🛢️', 'DB', 'Este es un error crítico.'));

        $notifier = new ErrorNotifierService(
            $clockMock,
            $discordServiceMock,
            $emailServiceMock,
            $analizerMock
        );

        $notifier->send($message, $count, $scanWindow);
    }
}
