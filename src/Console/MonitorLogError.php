<?php

namespace Ecomac\EchoLog\Console;

use Illuminate\Console\Command;
use Ecomac\EchoLog\Services\ErrorNotificationCacheService;
use Ecomac\EchoLog\Services\LogReaderService;
use Ecomac\EchoLog\Services\ErrorNotifierService;

/**
 * Console command that monitors the Laravel log
 * and notifies about frequently recurring errors.
 *
 * This command analyzes the latest Laravel log entries and groups
 * errors by severity level and message. If an error appears more
 * times than the configured threshold within the scan window, it sends
 * a notification (e.g., email or external alert).
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
     * Creates a new command instance.
     *
     * @param LogReaderService $logReaderService Service to read recent log entries
     * @param ErrorNotifierService $errorNotifier Service to send error notifications
     * @param ErrorNotificationCacheService $cache Cache service to avoid duplicate notifications
     */
    public function __construct(
        private LogReaderService $logReaderService,
        private ErrorNotifierService $errorNotifier,
        private ErrorNotificationCacheService $cache
    ) {
        parent::__construct();
    }

    /**
     * Executes the command: scans the Laravel log, groups repeated errors
     * by level and message, and sends notifications if needed.
     *
     * The number of occurrences required for notification is defined
     * per level in the `config/echolog.php` file.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $this->info("Starting to monitor errors in the Laravel log...");

            $cooldown = config('echo-log.cooldown_minutes');
            $scanWindow = config('echo-log.scan_window_minutes');

            if (!$this->validateConfig($cooldown, $scanWindow)) {
                return;
            }

            $levels = config('echo-log.levels');
            $recentErrorsRaw = $this->logReaderService->getRecentErrors($scanWindow);

            // Mapear errores con nivel, mensaje y fecha
            $parsedErrors = collect($recentErrorsRaw)->map(function ($error) {
                $rawLine = $error[0]; // Formato completo del log
                $timestamp = $error[1];
                $message = $error[2];  // Solo el mensaje de error

                // Extraer el nivel del formato local.ERROR:
                preg_match('/\.([A-Z]+):/', $rawLine, $matches);
                $level = $matches[1] ?? 'UNKNOWN';

                return [
                    'raw_line' => $rawLine,  // Mantenemos la línea completa para notificación
                    'level' => $level,      // Nivel extraído (ERROR, WARNING, etc.)
                    'message' => $message,   // Solo el mensaje
                    'timestamp' => $timestamp,
                ];
            });

            // Agrupar por nivel
            $groupedByLevel = $parsedErrors->groupBy('level');

            foreach ($groupedByLevel as $level => $errorsByLevel) {
                $countRequired = $levels[$level]['count'] ?? 3;

                $groupedByMessage = collect($errorsByLevel)->groupBy('message');

                foreach ($groupedByMessage as $message => $instances) {
                    if (count($instances) < $countRequired) {
                        continue;
                    }

                    $hash = md5($level . $message);

                    if ($this->cache->shouldNotify($hash, $cooldown)) {
                        // Enviamos la línea completa del primer error del grupo
                        $this->errorNotifier->send(
                            $instances->first()['raw_line'],
                            count($instances),
                            $scanWindow
                        );
                        $this->cache->markAsNotified($hash);
                    } else {
                        $this->warn("Ya fue notificado: [$level] \"$message\" con " . count($instances) . " repeticiones.");
                    }
                }
            }

            $this->cache->clean();
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
        }
    }

    /**
     * Valid--- Expected
     * are within acceptable boundaries (1 to 60 minutes).
     *
     * @param int $cooldown Time in minutes before notifying the same error again
     * @param int $scanWindow Time in minutes to scan past log entries
     * @return bool True if valid, false otherwise
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
