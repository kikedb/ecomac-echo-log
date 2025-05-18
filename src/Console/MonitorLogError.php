<?php

namespace Ecomac\EchoLog\Console;

use Illuminate\Console\Command;
use Ecomac\EchoLog\Services\ErrorNotificationCacheService;
use Ecomac\EchoLog\Contracts\ClockProvider;
use Ecomac\EchoLog\Services\LogReaderService;
use Ecomac\EchoLog\Services\ErrorNotifierService;

/**
 * Console command that monitors the Laravel log
 * and notifies about frequently recurring errors.
 */
class MonitorLogError extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecomac:monitor-log-error';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitors Laravel log and notifies repeated errors';

    /**
     * Command constructor.
     *
     * @param ClockProvider $clockProvider Service to get the current time
     * @param LogReaderService $logReaderService Service to read recent errors from the log
     * @param ErrorNotifierService $errorNotifier Service to send error notifications
     * @param ErrorNotificationCacheService $cache Service to avoid duplicate notifications
     */
    public function __construct(
        private ClockProvider $clockProvider,
        private LogReaderService $logReaderService,
        private ErrorNotifierService $errorNotifier,
        private ErrorNotificationCacheService $cache
    ) {
        parent::__construct();
    }

    /**
     * Executes the console command.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $this->info("Starting to monitor errors in the Laravel log...");

            $cooldown = config('echo-log.cooldown_minutes');
            $scanWindow = config('echo-log.scan_window_minutes');

            // Validate config values
            $configIsValid = $this->validateConfig($cooldown, $scanWindow);
            if (!$configIsValid) {
                return;
            }

            // Get recent errors within the scan window
            $recentErrors = $this->logReaderService->getRecentErrors($scanWindow);

            // Group errors by message (index 2 of the array)
            $grouped = $recentErrors->groupBy(fn($m) => $m[2]);

            // Process each group of repeated errors
            foreach ($grouped as $errorMessage => $instances) {
                if (count($instances) < 3) continue; // Only consider errors with 3+ occurrences

                $hash = md5($errorMessage);

                // Check if this error should be notified
                if ($this->cache->shouldNotify($hash, $cooldown)) {
                    $this->errorNotifier->send($errorMessage, count($instances), $scanWindow);
                    $this->cache->markAsNotified($hash);
                }
                else {
                    $this->warn("Repeated error already notified: \"$errorMessage\" with " . count($instances) . " occurrences.");
                }
            }

            // Clean old cache entries
            $this->cache->clean();
        }
        catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
        }
    }

    /**
     * Validates that the config values are within acceptable ranges.
     *
     * @param int $cooldown Minimum interval in minutes between notifications for the same error
     * @param int $scanWindow Time window in minutes to scan the log
     * @return bool Returns true if values are valid, false otherwise
     */
    protected function validateConfig(int $cooldown, int $scanWindow): bool
    {
        if ($cooldown < 1 || $cooldown > 60) {
            $this->error("Invalid cooldown value: $cooldown. It must be between 1 and 60.");
            return false;
        }

        if ($scanWindow < 1 || $scanWindow > 60) {
            $this->error("Invalid scan window value: $scanWindow. It must be between 1 and 60.");
            return false;
        }

        return true;
    }
}
