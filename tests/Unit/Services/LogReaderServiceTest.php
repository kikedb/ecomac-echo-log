<?php

namespace Ecomac\EchoLog\Tests\Services;

use Ecomac\EchoLog\Services\LogReaderService;
use Ecomac\EchoLog\Contracts\ClockProvider;
use Illuminate\Support\Collection;
use Ecomac\EchoLog\Tests\TestCase;
use Carbon\Carbon;

class LogReaderServiceTest extends TestCase
{
    private string $logFile;
    private ClockProvider $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = app()->make(ClockProvider::class);
        $now = $this->clock->createFromFormat('Y-m-d','2024-05-19');
        $this->logFile = storage_path('logs/laravel-' . $now->format('Y-m-d') . '.log');
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
        
        // Crea contenido simulado
        file_put_contents($this->logFile, <<<LOG
        [2024-05-19 14:55:00] local.ERROR: Example error 1
        [2024-05-19 13:30:00] local.ERROR: Example error 2
        [2024-05-19 15:05:00] local.INFO: This is an info message
        [2024-05-19 14:58:00] local.ERROR: Example error 3
        [2024-05-19 14:58:01] local.CRITICAL: Example critical 1
        [2024-05-19 14:58:02] local.EMERGENCY: Example emergency 1
        [2024-05-19 14:58:03] local.ALERT: Example alert 1
        [2024-05-19 14:58:05] local.CRITICAL: Example critical 3
        [2024-05-19 14:58:06] local.CRITICAL: Example critical 2
        [2024-05-19 14:48:00] local.CRITICAL: Example critical 1
        LOG);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function test_it_returns_recent_errors_within_window()
    {
        $mockClock = $this->createMock(ClockProvider::class);
        $now = $this->clock->createFromFormat('Y-m-d H:i:s', '2024-05-19 15:00:00');
        
        $mockClock->method('now')->willReturn($now);
        $mockClock->method('createFromFormat')->willReturnCallback(
            fn($format, $value) => Carbon::createFromFormat($format, $value)
        );
        $mockClock->method('subMinutes')->willReturnCallback(
            fn($time, $minutes) => $time->copy()->subMinutes($minutes)
        );
        $mockClock->method('greaterThanOrEqualTo')->willReturnCallback(
            fn($a, $b) => $a->greaterThanOrEqualTo($b)
        );
        
        $service = new LogReaderService($mockClock);
        $errors = $service->getRecentErrors(10);
        $this->assertInstanceOf(Collection::class, $errors);
        $this->assertCount(7, $errors);
        $this->assertStringContainsString('Example error 1', $errors[5][0]);
        $this->assertStringContainsString('Example error 3', $errors[6][0]);
        $this->assertStringContainsString('Example emergency 1', $errors[0][0]);
        $this->assertStringContainsString('Example critical 2', $errors[4][0]);
        $this->assertStringContainsString('Example alert 1', $errors[1][0]);
    }
}