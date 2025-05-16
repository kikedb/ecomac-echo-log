<?php

namespace Ecomac\EchoLog\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Ecomac\EchoLog\Mail\RecurrentErrorMail;

class  MonitorLogError extends Command
{
    protected $signature = 'ecomac:monitor-log-error';
    // $this->info($this->laravel['config']->get('echo-log.app_name'));
    protected $description = 'Monitorea el log de Laravel y notifica errores repetitivos';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $this->info("Iniciando el monitoreo de errores en el log de Laravel...");
        $cooldown = $this->laravel['config']->get('echo-log.cooldown_minutes');
        $scanWindow = $this->laravel['config']->get('echo-log.scan_window_minutes');

        $logPath = storage_path('logs/laravel-' . Carbon::now()->format('Y-m-d') . '.log');
        if ($cooldown < 1 || $cooldown > 60) {
            $this->error("El valor de cooldown_minutes ($cooldown) debe estar entre 1 y 60.");
            return;
        }

        if ($scanWindow < 1 || $scanWindow > 60) {
            $this->error("El valor de scan_window_minutes ($scanWindow) debe estar entre 1 y 60.");
            return;
        }

        if (!file_exists($logPath)) {
            $this->warn("El archivo de log de hoy no existe: $logPath");
            return;
        }

        $content = file_get_contents($logPath);
        $now = Carbon::now();

        preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?ERROR.*?: (.*)/', $content, $matches, PREG_SET_ORDER);

        $recentErrors = collect($matches)->filter(function ($match) use ($now, $scanWindow) {
            $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $match[1]);
            return $timestamp->greaterThanOrEqualTo($now->copy()->subMinutes($scanWindow));
        });

        $grouped = $recentErrors->groupBy(fn($m) => $m[2]);

        foreach ($grouped as $errorMessage => $instances) {
            if (count($instances) >= 3) {
                $hash = md5($errorMessage);

                if ($this->shouldNotify($hash, $cooldown)) {
                    $this->sendAlert($errorMessage, count($instances), $scanWindow);
                    $this->updateNotificationTime($hash);
                } else {
                    $this->warn("Error repetido ya notificado: \"$errorMessage\" con " . count($instances) . " ocurrencias.");
                    // No se actualiza la marca temporal aquí
                }
            }
        }

        $this->cleanCacheFile();
    }

    protected function shouldNotify(string $hash, int $cooldown): bool
    {
        $cache = $this->getCache();

        if (!isset($cache[$hash])) {
            return true;
        }

        $lastNotified = Carbon::parse($cache[$hash]);

        return $lastNotified->diffInMinutes(Carbon::now()) >= $cooldown;
    }

    protected function updateNotificationTime(string $hash): void
    {
        $cache = $this->getCache();
        $cache[$hash] = Carbon::now()->toDateTimeString();
        $this->saveCache($cache);
    }

    protected function saveCache(array $cache): void
    {
        $path = storage_path('app/log_monitor_cache.json');
        file_put_contents($path, json_encode($cache, JSON_PRETTY_PRINT));
    }

    protected function getCache(): array
    {
        $path = storage_path('app/log_monitor_cache.json');
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        return json_decode($content, true) ?? [];
    }

    protected function cleanCacheFile(): void
    {
        $cache = $this->getCache();
        $cleaned = [];

        foreach ($cache as $hash => $timestamp) {
            if (Carbon::parse($timestamp)->diffInDays(Carbon::now()) <= 1) {
                $cleaned[$hash] = $timestamp;
            }
        }

        $this->saveCache($cleaned);
    }

    protected function sendAlert($message, $count, $scanWindow)
    {
        $this->sendDiscordNotification($message, $count, $scanWindow);
        $this->sendEmailNotification($message, $count, $scanWindow);
    }

    protected function sendDiscordNotification($message, $count, $scanWindow)
    {
        $webhookUrl = $this->laravel['config']->get('echo-log.services.discord.webhook_url');
        $userIds = $this->laravel['config']->get('echo-log.services.discord.mention_user_ids');
        $sourceName = $this->laravel['config']->get('echo-log.services.discord.app_name');

        [$emoji, $type, $title] = $this->categorizeError($message, $sourceName);

        if (!$webhookUrl || empty($userIds)) {
            $this->warn("Webhook de Discord o usuarios no configurados.");
            return;
        }

        $mentions = collect($userIds)
            ->map(fn($id) => '<@' . trim($id) . '>')
            ->implode(' ');

        $payload = [
            'embeds' => [[
                'title' => "$emoji [$sourceName → $type] $title",
                'color' => 16711680,
                'fields' => [
                    [
                        'name' => '📝 Mensaje',
                        'value' => "`$message`",
                        'inline' => false
                    ],
                    [
                        'name' => '🔁 Repeticiones',
                        'value' => "$count veces en los últimos $scanWindow minutos",
                        'inline' => true
                    ],
                    [
                        'name' => '👥 Notificando a',
                        'value' => $mentions,
                        'inline' => false
                    ],
                ],
                'timestamp' => Carbon::now()->toIso8601String()
            ]],
            'content' => $mentions,
            'allowed_mentions' => [
                'users' => array_map('trim', $userIds),
            ],
        ];

        Http::post($webhookUrl, $payload);

        $this->info("🔔 Alerta enviada a Discord con menciones a usuarios desde [$sourceName → $type].");
    }

    protected function sendEmailNotification($message, $count, $scanWindow)
    {
        $recipients = $this->laravel['config']->get('echo-log.email_recipients');
        $sourceName = $this->laravel['config']->get('services.discord.app_name', 'Laravel');
        $logViewerUrl = $this->laravel['config']->get('echo-log.app_url') . '/log-viewer/';
        [$emoji, $type, $title] = $this->categorizeError($message, $sourceName);
        if (empty($recipients)) {
            $this->warn("No hay destinatarios de correo configurados.");
            return;
        }

        foreach ($recipients as $recipient) {
            Mail::to(trim($recipient))->send(
                new RecurrentErrorMail($message, $count, $emoji, $title, $type, $sourceName, $scanWindow, $logViewerUrl)
            );
            $this->info("📧 Alerta HTML enviada por correo a $recipient");
        }
    }


    protected function categorizeError(string $message, string $sourceName): array
    {
        $lower = strtolower($message);

        return match (true) {
            str_contains($lower, 'smtp') || str_contains($lower, 'mail') || str_contains($lower, 'connection refused') =>
                ['📧', "Mail", "Fallo en envío de correos"],
            str_contains($lower, 'sql') || str_contains($lower, 'pdo') || str_contains($lower, 'database') =>
                ['🛢️', "DB", "Error de base de datos"],
            str_contains($lower, 'unauthorized') || str_contains($lower, 'unauthenticated') || str_contains($lower, 'token') =>
                ['🔐', "Auth", "Error de autenticación"],
            str_contains($lower, 'file') || str_contains($lower, 'filesystem') || str_contains($lower, 'permission') =>
                ['📁', "FS", "Error de archivos o permisos"],
            str_contains($lower, 'redis') || str_contains($lower, 'cache') =>
                ['🧠', "Cache", "Fallo en Redis/cache"],
            str_contains($lower, 'curl') || str_contains($lower, 'timeout') || str_contains($lower, 'http') || str_contains($lower, 'request') =>
                ['🌐', "Network", "Fallo de red o HTTP"],
            default =>
                ['❗', "Unknown", "Error no categorizado"]
        };
    }
}
