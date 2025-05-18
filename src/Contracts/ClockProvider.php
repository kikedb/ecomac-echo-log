<?php

namespace Ecomac\EchoLog\Contracts;

use DateTimeInterface;

/**
 * Interface ClockProvider
 *
 * Provides methods to get and manipulate date and time.
 */
interface ClockProvider
{
    /**
     * Get the current date and time.
     *
     * @return DateTimeInterface The current date and time.
     */
    public function now(): DateTimeInterface;

    /**
     * Create a DateTime object from a format and a date/time string.
     *
     * @param string $format The date format.
     * @param string $datetime The date/time string.
     * @return DateTimeInterface The created DateTime object.
     */
    public function createFromFormat(string $format, string $datetime): DateTimeInterface;

    /**
     * Calculate the difference in days between two dates.
     *
     * @param DateTimeInterface $start Start date.
     * @param DateTimeInterface $end End date.
     * @return float The difference in days between the two dates.
     */
    public function diffInDays(DateTimeInterface $start, DateTimeInterface $end): float;

    /**
     * Calculate the difference in minutes between two dates.
     *
     * @param DateTimeInterface $start Start date.
     * @param DateTimeInterface $end End date.
     * @return float The difference in minutes between the two dates.
     */
    public function diffInMinutes(DateTimeInterface $start, DateTimeInterface $end): float;

    /**
     * Subtract minutes from a given date and return the new date.
     *
     * @param DateTimeInterface $dateTime The original date.
     * @param int $minutes Minutes to subtract.
     * @return DateTimeInterface The new date with minutes subtracted.
     */
    public function subMinutes(DateTimeInterface $dateTime, int $minutes): DateTimeInterface;

    /**
     * Check if one date is greater than or equal to another.
     *
     * @param DateTimeInterface $a First date.
     * @param DateTimeInterface $b Second date.
     * @return bool True if $a is greater than or equal to $b, false otherwise.
     */
    public function greaterThanOrEqualTo(DateTimeInterface $a, DateTimeInterface $b): bool;
}
