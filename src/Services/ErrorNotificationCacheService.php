<?php

namespace Ecomac\EchoLog\Services;

use Ecomac\EchoLog\Contracts\ClockProviderInterface;

/**
 * Class ErrorNotificationCacheService
 *
 * Manages a cache to control the frequency of error notifications.
 *
 * This service stores timestamps of sent notifications identified by a hash,
 * allowing to determine if enough cooldown time has passed before sending a new notification.
 *
 * It also supports cleaning old cached entries to keep the cache size manageable.
 */
class ErrorNotificationCacheService
{
    /**
     * @var string Path to the JSON file used for storing notification cache.
     */
    private string $path;

    /**
     * Constructor.
     *
     * @param ClockProviderInterface $clock A clock provider instance used for date/time operations.
     */
    public function __construct(private ClockProviderInterface $clock)
    {
        $this->path = storage_path('app/log_monitor_cache.json');
    }

    /**
     * Determines whether a notification should be sent for the given hash,
     * based on the cooldown period.
     *
     * @param string $hash A unique identifier for the error notification.
     * @param int $cooldown Cooldown time in minutes to wait before sending a new notification.
     *
     * @return bool True if notification should be sent, false otherwise.
     */
    public function shouldNotify(string $hash, int $cooldown): bool
    {
        $cache = $this->readCache();
        if (!isset($cache[$hash])) return true;

        $last = $this->clock->createFromFormat('Y-m-d H:i:s', $cache[$hash]);
        return $this->clock->diffInMinutes($last, $this->clock->now()) >= $cooldown;
    }

    /**
     * Marks the given hash as notified by storing the current timestamp.
     *
     * @param string $hash The unique identifier of the error notification to mark as notified.
     */
    public function markAsNotified(string $hash): void
    {
        $cache = $this->readCache();
        $cache[$hash] = $this->clock->now()->format('Y-m-d H:i:s');
        $this->writeCache($cache);
    }

    /**
     * Cleans the cache by removing entries older than one day.
     *
     * This helps maintain the cache file size and ensures outdated notifications
     * do not prevent new notifications.
     */
    public function clean(): void
    {
        $cache = $this->readCache();
        $errorsToKeep = [];

        foreach ($cache as $hash => $timestamp) {
            $time = $this->clock->createFromFormat('Y-m-d H:i:s', $timestamp);
            if ($this->clock->diffInDays($time, $this->clock->now()) <= 1) {
                $errorsToKeep[$hash] = $timestamp;
            }
        }

        $this->writeCache($errorsToKeep);
    }

    /**
     * Reads the notification cache from the JSON file.
     *
     * @return array Associative array of hash => timestamp entries.
     */
    private function readCache(): array
    {
        return file_exists($this->path) ? json_decode(file_get_contents($this->path), true) ?? [] : [];
    }

    /**
     * Writes the given data array to the JSON cache file.
     *
     * @param array $data Associative array of hash => timestamp entries to save.
     */
    private function writeCache(array $data): void
    {
        file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
    }
}
