<?php

namespace Tests\Unit;

use Ecomac\EchoLog\Contracts\ClockProvider;
use Ecomac\EchoLog\Services\ErrorNotificationCacheService;
use Ecomac\EchoLog\Tests\TestCase;
use Mockery;
class ErrorNotificationCacheServiceTest extends TestCase
{
    private string $logFile;
    private ClockProvider $clock;
    private ErrorNotificationCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un mock de ClockProvider
        $this->clock = Mockery::mock(ClockProvider::class);

        // Crear archivo temporal para el cache
        $this->logFile = storage_path('app/test_log_monitor_cache.json');

        // Instanciar el servicio con el mock
        $this->service = new ErrorNotificationCacheService($this->clock);

        $ref = new \ReflectionClass($this->service);
        $prop = $ref->getProperty('path');
        $prop->setAccessible(true);
        $prop->setValue($this->service, $this->logFile);
    }

     protected function tearDown(): void
    {
        Mockery::close();
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function test_should_notify_when_no_previous_notification()
    {
        $hash = 'abc123';

        $this->assertTrue($this->service->shouldNotify($hash, 10));
    }

    public function test_should_notify_when_cooldown_has_passed()
    {
        $hash = 'abc123';
        $pastTime = '2024-05-19 10:00:00';
        $nowTime = '2024-05-19 10:30:00';

        // Guardamos en el archivo un registro previo
        file_put_contents($this->logFile, json_encode([
            $hash => $pastTime,
        ]));

        // Usamos objetos reales de DateTimeImmutable
        $past = new \DateTimeImmutable($pastTime);
        $now = new \DateTimeImmutable($nowTime);

        // Mockeamos ClockProvider para devolver estos valores
        $this->clock->shouldReceive('createFromFormat')
            ->with('Y-m-d H:i:s', $pastTime)
            ->andReturn($past);

        $this->clock->shouldReceive('now')
            ->andReturn($now);

        $this->clock->shouldReceive('diffInMinutes')
            ->with($past, $now)
            ->andReturn(30); // simula que pasaron 30 minutos

        $this->assertTrue($this->service->shouldNotify($hash, 10));
    }


    public function test_should_not_notify_when_cooldown_not_passed()
    {
        $hash = 'abc123';
        $pastTime = '2024-05-19 10:00:00';
        $nowTime = '2024-05-19 10:30:00';

        file_put_contents($this->logFile, json_encode([
            $hash => $pastTime,
        ]));

        $past = new \DateTimeImmutable($pastTime);
        $now = new \DateTimeImmutable($nowTime);


        $this->clock->shouldReceive('createFromFormat')
            ->with('Y-m-d H:i:s', $pastTime)
            ->andReturn($past);

        $this->clock->shouldReceive('now')
            ->andReturn($now);

        $this->clock->shouldReceive('diffInMinutes')
            ->with($past, $now)
            ->andReturn(5);

        $this->assertFalse($this->service->shouldNotify($hash, 10));
    }

    public function test_mark_as_notified_writes_current_time()
    {
        $hash = 'xyz789';
        $nowTime = '2024-05-19 12:00:00';
        // Creamos un mock del DateTimeImmutable
        $now = Mockery::mock(\DateTimeImmutable::class);
        $now->shouldReceive('format')
        ->with('Y-m-d H:i:s')
        ->andReturn($nowTime);

        $this->clock->shouldReceive('now')
            ->andReturn($now);

        $this->service->markAsNotified($hash);

        $data = json_decode(file_get_contents($this->logFile), true);

        $this->assertEquals('2024-05-19 12:00:00', $data[$hash]);
    }

    public function test_clean_removes_old_entries()
    {
        $now = Mockery::mock(\DateTimeImmutable::class);
        $newDate = Mockery::mock(\DateTimeImmutable::class);
        $oldDate = Mockery::mock(\DateTimeImmutable::class);

        $hash1 = 'new_hash';
        $hash2 = 'old_hash';

        $timestamps = [
            $hash1 => '2024-05-18 12:00:00',
            $hash2 => '2024-05-15 09:00:00',
        ];

        file_put_contents($this->logFile, json_encode($timestamps));

        $this->clock->shouldReceive('now')->andReturn($now)->times(2);

        $this->clock->shouldReceive('createFromFormat')
            ->with('Y-m-d H:i:s', '2024-05-18 12:00:00')
            ->andReturn($newDate);

        $this->clock->shouldReceive('createFromFormat')
            ->with('Y-m-d H:i:s', '2024-05-15 09:00:00')
            ->andReturn($oldDate);

        $this->clock->shouldReceive('diffInDays')
            ->with($newDate, $now)
            ->andReturn(1);

        $this->clock->shouldReceive('diffInDays')
            ->with($oldDate, $now)
            ->andReturn(4);

        $this->service->clean();

        $data = json_decode(file_get_contents($this->logFile), true);

        $this->assertArrayHasKey('new_hash', $data);
        $this->assertArrayNotHasKey('old_hash', $data);
    }
}