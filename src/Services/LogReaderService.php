<?php

namespace Ecomac\EchoLog\Services;

use Ecomac\EchoLog\Factories\LogEntryDtoFactory;
use Illuminate\Support\Collection;
use Ecomac\EchoLog\Contracts\ClockProviderInterface;

/**
 * Class LogReaderService
 *
 * This service is responsible for reading the current day's log file
 * and extracting recent error entries based on the levels defined
 * in the package configuration.
 *
 * @package Ecomac\EchoLog\Services
 */
class LogReaderService
{
    /**
     * LogReaderService constructor.
     *
     * @param ClockProviderInterface $clock Clock provider used to get the current time and compare timestamps.
     */
    public function __construct(private ClockProviderInterface $clock) {}

    /**
     * Retrieves recent error log entries from the current day's log file.
     *
     * This method scans the log file named `laravel-YYYY-MM-DD.log` and filters
     * log entries based on the log levels defined in `config('echo-log.levels')`.
     * Only entries that occurred within the specified time window are returned.
     *
     * @param int $scanWindow Number of minutes to look back from the current time.
     * @return Collection A collection of LogEntryDto objects representing recent errors.
     */
    public function getRecentErrors(int $scanWindow): Collection
    {
        $logPath = storage_path('logs/laravel-' . $this->clock->now()->format('Y-m-d') . '.log');
        if (!file_exists($logPath)) return collect();

        $content = file_get_contents($logPath);
        $levels = array_keys(config('echo-log.levels'));
        $allMatches = collect();

        foreach ($levels as $level) {
            $escapedLevel = preg_quote($level, '/');

            preg_match_all(
                "/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?({$escapedLevel}).*?: (.*)/",
                $content,
                $matches,
                PREG_SET_ORDER
            );

            $filtered = collect($matches)->filter(function ($match) use ($scanWindow) {
                $timestamp = $this->clock->createFromFormat('Y-m-d H:i:s', $match[1]);
                return $this->clock->greaterThanOrEqualTo(
                    $timestamp,
                    $this->clock->subMinutes($this->clock->now(), $scanWindow)
                );
            });

            $allMatches = $allMatches->merge($filtered);
        }

        $logEntries = $allMatches->map(fn($entry) => (new LogEntryDtoFactory())->createFromLogEntry($entry));
        return $logEntries;
    }
}
