<?php

namespace Tests\Unit\Services;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Ecomac\EchoLog\Services\CarbonService;

class CarbonServiceTest extends TestCase
{
    protected CarbonService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CarbonService();
    }
    public function test_now_returns_current_date_time()
    {
        $now = $this->service->now();

        $this->assertInstanceOf(DateTimeInterface::class, $now);
        $this->assertLessThan(2, abs(time() - $now->getTimestamp())); // Tolerancia de 2 seg
    }
    public function test_create_from_format()
    {
        $date = $this->service->createFromFormat('Y-m-d H:i:s', '2025-05-18 14:00:00');

        $this->assertEquals('2025-05-18 14:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function test_diff_in_days()
    {
        $start  = new DateTimeImmutable('2025-05-15');
        $end    = new DateTimeImmutable('2025-05-18');

        $this->assertEquals(3.0, $this->service->diffInDays($start, $end));
    }

    public function test_diff_in_minutes()
    {
        $start  = new DateTimeImmutable('2025-05-18 12:00:00');
        $end    = new DateTimeImmutable('2025-05-18 14:30:00');

        $this->assertEquals(150.0, $this->service->diffInMinutes($start, $end));
    }

    public function test_sub_minutes()
    {
        $date = new DateTimeImmutable('2025-05-18 10:00:00');
        $result = $this->service->subMinutes($date, 30);

        $this->assertEquals('2025-05-18 09:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function testGreaterThanOrEqualTo()
    {
        $a = new DateTimeImmutable('2025-05-18 10:00:00');
        $b = new DateTimeImmutable('2025-05-18 09:59:59');

        $this->assertTrue($this->service->greaterThanOrEqualTo($a, $b));
    }
}
