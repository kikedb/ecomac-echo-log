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
use Illuminate\Console\OutputStyle;

/**
 * @covers \Ecomac\EchoLog\Console\MonitorLogError
 *
 * This test verifies that the MonitorLogError command behaves correctly
 * when there are repeated log errors and a notification should be sent.
 */
class MonitorLogErrorTest extends TestCase
{

    public function test_it_sends_notification_when_error_exceeds_threshold()
    {
        // Mock error data
        $mockErrors = collect([
            ['[2025-05-19 14:58:02] local.ERROR: Database connection failed', '2025-05-19 14:58:02', 'Database connection failed'],
            ['[2025-05-19 14:58:03] local.ERROR: Database connection failed', '2025-05-19 14:58:03', 'Database connection failed'],
            ['[2025-05-19 14:58:04] local.ERROR: Database connection failed', '2025-05-19 14:58:04', 'Database connection failed'],
        ]);

        // Create mocks
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

        // Instantiate the command
        $command = new MonitorLogError(
            $logReaderService,
            $notifierService,
            $cacheService
        );

        // Execute the handle method directly
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $command->setOutput(new OutputStyle($input, $output));
        $command->handle();

        // No additional asserts needed as we use `expects()` in mocks
        $this->assertTrue(true);
    }

    public function tests_sends_notification_when_emergency_error_occurs_once()
    {
        // Mock data: only one occurrence of EMERGENCY
        $mockErrors = collect([
            ['[2025-05-20 08:00:00] local.EMERGENCY: System failure detected', '2025-05-20 08:00:00', 'System failure detected'],
        ]);

        // Mock services
        $logReaderService = $this->createMock(LogReaderService::class);
        $logReaderService->method('getRecentErrors')->willReturn($mockErrors);

        $notifierService = $this->createMock(ErrorNotifierService::class);
        $notifierService->expects($this->once())
            ->method('send')
            ->with('[2025-05-20 08:00:00] local.EMERGENCY: System failure detected', 1, 5);

        $cacheService = $this->createMock(ErrorNotificationCacheService::class);
        $cacheService->method('shouldNotify')->willReturn(true);
        $cacheService->expects($this->once())->method('markAsNotified');
        $cacheService->expects($this->once())->method('clean');

        // Instantiate and execute command
        $command = new MonitorLogError(
            $logReaderService,
            $notifierService,
            $cacheService
        );

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $command->setOutput(new OutputStyle($input, $output));
        $command->handle();

        $this->assertTrue(true);
    }

    public function tests_sends_notification_when_critical_error_occurs_twice()
    {
        // Mock data: two occurrences of CRITICAL error
        $mockErrors = collect([
            ['[2025-05-19 14:58:02] local.CRITICAL: A critical error found!', '2025-05-19 14:58:02', 'A critical error found!'],
            ['[2025-05-19 14:58:03] local.CRITICAL: A critical error found!', '2025-05-19 14:58:03', 'A critical error found!'],
        ]);

        // Mock services
        $logReaderService = $this->createMock(LogReaderService::class);
        $logReaderService->method('getRecentErrors')->willReturn($mockErrors);

        $notifierService = $this->createMock(ErrorNotifierService::class);
        $notifierService->expects($this->once())
            ->method('send')
            ->with('[2025-05-19 14:58:02] local.CRITICAL: A critical error found!', 2, 5);

        $cacheService = $this->createMock(ErrorNotificationCacheService::class);
        $cacheService->method('shouldNotify')->willReturn(true);
        $cacheService->expects($this->once())->method('markAsNotified');
        $cacheService->expects($this->once())->method('clean');

        // Instantiate and execute command
        $command = new MonitorLogError(
            $logReaderService,
            $notifierService,
            $cacheService
        );

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $command->setOutput(new OutputStyle($input, $output));
        $command->handle();

        $this->assertTrue(true);
    }

    public function test_it_does_not_send_notification_if_threshold_not_reached()
    {
        // Set threshold to 3
        Config::set('echo-log.levels', [
            'ERROR' => ['count' => 3],
        ]);
        // Simulate only 2 errors
        $mockErrors = collect([
            ['[2025-05-20 09:00:00] local.ERROR: DB error', '2025-05-20 09:00:00', 'DB error'],
            ['[2025-05-20 09:01:00] local.ERROR: DB error', '2025-05-20 09:01:00', 'DB error'],
        ]);

        // Mock services
        $logReaderService = $this->createMock(LogReaderService::class);
        $logReaderService->method('getRecentErrors')->willReturn($mockErrors);

        $notifierService = $this->createMock(ErrorNotifierService::class);
        $notifierService->expects($this->never())->method('send'); // Should not send

        $cacheService = $this->createMock(ErrorNotificationCacheService::class);
        $cacheService->expects($this->never())->method('markAsNotified');
        $cacheService->expects($this->once())->method('clean');

        // Execute command
        $command = new MonitorLogError(
            $logReaderService,
            $notifierService,
            $cacheService
        );

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $command->setOutput(new OutputStyle($input, $output));
        $command->handle();

        $this->assertTrue(true);
    }

}
