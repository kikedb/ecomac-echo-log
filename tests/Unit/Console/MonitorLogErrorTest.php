<?php

namespace Tests\Unit\Console;

use Ecomac\EchoLog\Tests\TestCase;
use Ecomac\EchoLog\Console\MonitorLogError;
use Ecomac\EchoLog\Services\LogReaderService;
use Ecomac\EchoLog\Services\ErrorNotifierService;
use Ecomac\EchoLog\Services\ErrorNotificationCacheService;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use  Illuminate\Console\OutputStyle;

/**
 * @covers \Ecomac\EchoLog\Console\MonitorLogError
 *
 * This test verifies that the MonitorLogError command behaves correctly
 * when there are repeated log errors and a notification should be sent.
 */
class MonitorLogErrorTest extends TestCase
{
    /** @test */
    public function it_sends_notification_when_error_exceeds_threshold()
    {
        // Simular configuración
        Config::set('echo-log.cooldown_minutes', 10);
        Config::set('echo-log.scan_window_minutes', 5);
        Config::set('echolog.levels', [
            'ERROR' => ['count' => 3],
        ]);

        // Datos simulados de errores
        $mockErrors = collect([
            ['[2025-05-19 14:58:02] local.ERROR: Database connection failed', '2025-05-19 14:58:02', 'Database connection failed'],
            ['[2025-05-19 14:58:03] local.ERROR: Database connection failed', '2025-05-19 14:58:03', 'Database connection failed'],
            ['[2025-05-19 14:58:04] local.ERROR: Database connection failed', '2025-05-19 14:58:04', 'Database connection failed'],
        ]);

        // Crear mocks
        $logReaderService = $this->createMock(LogReaderService::class);
        $logReaderService->method('getRecentErrors')->willReturn($mockErrors);

        $notifierService = $this->createMock(ErrorNotifierService::class);
        $notifierService->expects($this->once())
            ->method('send')
            ->with('[2025-05-19 14:58:02] local.ERROR: Database connection failed', 3, 5);

        $cacheService = $this->createMock(ErrorNotificationCacheService::class);
        $cacheService->method('shouldNotify')->willReturn(true);
        $cacheService->expects($this->once())->method('markAsNotified');
        $cacheService->expects($this->once())->method('clean');

        // Crear instancia del comando
        $command = new MonitorLogError(
            $logReaderService,
            $notifierService,
            $cacheService
        );

        // Ejecutar directamente el método handle
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
         $command->setOutput(new OutputStyle($input, $output));
        $command->handle();

        // No es necesario asserts adicionales porque usamos `expects()` en los mocks
        $this->assertTrue(true);
    }
}
