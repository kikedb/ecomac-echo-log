<?php

namespace Ecomac\EchoLog\Services;

use Illuminate\Support\Collection;
use Ecomac\EchoLog\Contracts\ClockProvider;

/**
 * Class LogReaderService
 *
 * Service responsible for reading and filtering recent error logs
 * from Laravel's log files.
 *
 * This service parses the log file for the current date, extracts error entries,
 * and filters them based on a specified time window.
 *
 * Can be extended to support different log formats, multiple log files,
 * or additional log levels.
 */
class LogReaderService
{
    /**
     * Constructor.
     *
     * @param ClockProvider $clock Provides date/time utility methods.
     */
    public function __construct(private ClockProvider $clock) {}

    /**
     * Retrieves recent error log entries within a specified scan window (in minutes).
     *
     * Parses the current day's Laravel log file, extracts entries tagged as errors,
     * and returns only those which occurred within the last $scanWindow minutes.
     *
     * @param int $scanWindow Time window in minutes to look back for errors.
     * @return Collection Returns a collection of matched error log entries.
     */
    public function getRecentErrors(int $scanWindow): Collection
    {
        $logPath = storage_path('logs/laravel-' . $this->clock->now()->format('Y-m-d') . '.log');
        if (!file_exists($logPath)) return collect();

        $content = file_get_contents($logPath);
        $levels = array_keys(config('echo-log.levels'));
        $allMatches = collect();

                
        foreach ($levels as $level) {
                // Escapar correctamente el nivel para regex (por si acaso)
                $escapedLevel = preg_quote($level, '/');

                // Regex para capturar [fecha] LEVEL: mensaje
                preg_match_all(
                    "/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?$escapedLevel.*?: (.*)/",
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

            return $allMatches->values();
    }
}
