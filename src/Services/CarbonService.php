<?php

namespace Ecomac\EchoLog\Services;

use DateTimeImmutable;
use DateTimeInterface;
use Carbon\CarbonImmutable;
use Ecomac\EchoLog\Contracts\ClockProvider;

/**
 * Class CarbonService
 *
 * Implementation of ClockProvider using CarbonImmutable for date and time operations.
 */
class CarbonService implements ClockProvider
{
    /**
     * Get the current date and time.
     *
     * @return DateTimeInterface The current date and time as a CarbonImmutable instance.
     */
    public function now(): DateTimeInterface
    {
        return CarbonImmutable::now();
    }

    /**
     * Create a DateTimeImmutable instance from a specific format and datetime string.
     *
     * @param string $format The date/time format.
     * @param string $datetime The date/time string.
     * @return DateTimeImmutable The created DateTimeImmutable instance.
     */
    public function createFromFormat(string $format, string $datetime): DateTimeImmutable
    {
        return CarbonImmutable::createFromFormat($format, $datetime);
    }

    /**
     * Calculate the difference in days between two dates.
     *
     * @param DateTimeInterface $start The start date.
     * @param DateTimeInterface $end The end date.
     * @return float The absolute difference in days.
     */
    public function diffInDays(DateTimeInterface $start, DateTimeInterface $end): float
    {
        $seconds = $end->getTimestamp() - $start->getTimestamp();
        return abs($seconds / (60 * 60 * 24));
    }

    /**
     * Calculate the difference in minutes between two dates.
     *
     * @param DateTimeInterface $start The start date.
     * @param DateTimeInterface $end The end date.
     * @return float The absolute difference in minutes.
     */
    public function diffInMinutes(DateTimeInterface $start, DateTimeInterface $end): float
    {
        $seconds = $end->getTimestamp() - $start->getTimestamp();
        return abs($seconds / 60);
    }

    /**
     * Subtract a given number of minutes from a DateTimeInterface instance.
     *
     * @param DateTimeInterface $dateTime The original date and time.
     * @param int $minutes The number of minutes to subtract.
     * @return DateTimeInterface A new DateTimeInterface instance with minutes subtracted.
     */
    public function subMinutes(DateTimeInterface $dateTime, int $minutes): DateTimeInterface
    {
        return CarbonImmutable::instance($dateTime)->subMinutes($minutes);
    }

    /**
     * Determine if one DateTimeInterface instance is greater than or equal to another.
     *
     * @param DateTimeInterface $a The first date.
     * @param DateTimeInterface $b The second date.
     * @return bool True if $a is greater than or equal to $b, false otherwise.
     */
    public function greaterThanOrEqualTo(DateTimeInterface $a, DateTimeInterface $b): bool
    {
        return CarbonImmutable::instance($a)->greaterThanOrEqualTo($b);
    }
}
