<?php

namespace Ecomac\EchoLog\Services;

use Ecomac\EchoLog\Contracts\StringHelperInterface;
use Illuminate\Support\Facades\Http;
use Ecomac\EchoLog\Dto\RecurrentErrorDto;

/**
 * Class DiscordService
 *
 * Service to send notifications about recurrent errors to Discord via webhook.
 */
class DiscordService
{

    public function __construct(
        private StringHelperInterface $stringHelper,
    ) {
    }
    /**
     * Sends a notification message to Discord mentioning specific users.
     *
     * The message includes details about the recurrent error such as message text,
     * error category, source name, number of occurrences, and time window.
     *
     * @param RecurrentErrorDto $recurrentError DTO containing information about the recurrent error.
     * @param array<int, string> $userIds Array of Discord user IDs to mention in the notification.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Client\RequestException If the HTTP request fails.
     */
    public function sendNotification(RecurrentErrorDto $recurrentError, array $userIds): void
    {
        $mentions = collect($userIds)
            ->map(fn($id) => '<@' . trim($id) . '>')
            ->implode(' ');

        $message       = $recurrentError->details->messageText;
        $message       = $this->stringHelper->limit($message, 300, ' (...)');
        $errorCategory = $recurrentError->details->category;
        $sourceName    = $recurrentError->context->sourceName;
        $scanWindow    = $recurrentError->context->scanWindow;
        $count         = $recurrentError->count;
        $timestamp     = $recurrentError->context->date;

        $payload = [
            'embeds' => [[
                'title' => "{$errorCategory->emoji} [{$sourceName} → {$errorCategory->type}] {$errorCategory->title}",
                'color' => 16711680,
                'fields' => [
                    [
                        'name' => '📝 Mensaje',
                        'value' => "`$message`",
                        'inline' => false,
                    ],
                    [
                        'name' => '🔁 Repeticiones',
                        'value' => "$count veces en los últimos $scanWindow minutos",
                        'inline' => true,
                    ],
                    [
                        'name' => '👥 Notificado a',
                        'value' => $mentions,
                        'inline' => false,
                    ],
                ],
                'timestamp' => $timestamp,
            ]],
            'content' => $mentions,
            'allowed_mentions' => [
                'users' => array_map('trim', $userIds),
            ],
        ];

        $webhookUrl = config('echo-log.services.discord.webhook_url');

        Http::post($webhookUrl, $payload)->throw();
    }
}
