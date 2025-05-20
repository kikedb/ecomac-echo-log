<?php

namespace Ecomac\EchoLog\Services;

use Ecomac\EchoLog\Dto\ErrorDetailDto;
use Ecomac\EchoLog\Dto\ErrorContextDto;
use Ecomac\EchoLog\Dto\RecurrentErrorDto;
use Ecomac\EchoLog\Services\EmailService;
use Ecomac\EchoLog\Contracts\ClockProviderInterface;
use Ecomac\EchoLog\Services\DiscordService;
use Ecomac\EchoLog\Services\ErrorAnalizerService;

/**
 * Class ErrorNotifierService
 *
 * Responsible for sending error notifications through multiple channels,
 * currently Discord and Email.
 *
 * It analyzes the error message to categorize it and composes
 * contextual information before sending alerts.
 *
 * This service can be extended to support additional notification channels or
 * enhanced message formatting.
 */
class ErrorNotifierService
{
    /**
     * Constructor.
     *
     * @param ClockProviderInterface $clock Clock provider to handle time operations.
     * @param DiscordService $discordService Service to send notifications to Discord.
     * @param EmailService $emailService Service to send email notifications.
     * @param ErrorAnalizerService $errorAnalizer Service to categorize and analyze errors.
     */
    public function __construct(
        private ClockProviderInterface $clock,
        private DiscordService $discordService,
        private EmailService $emailService,
        private ErrorAnalizerService $errorAnalizer,
    ) {}

    /**
     * Sends notifications about a recurrent error through all configured channels.
     *
     * @param string $message The error message text.
     * @param int $count Number of times the error has been detected.
     * @param int $scanWindow The time window (in minutes) over which the error count was accumulated.
     */
    public function send(string $message, int $count, int $scanWindow): void
    {
        $this->sendDiscord($message, $count, $scanWindow);
        $this->sendEmail($message, $count, $scanWindow);
    }

    /**
     * Sends an error notification to Discord with user mentions and context.
     *
     * @param string $message The error message.
     * @param int $count The number of occurrences.
     * @param int $scanWindow The scanning time window in minutes.
     *
     * @return void
     */
    protected function sendDiscord(string $message, int $count, int $scanWindow): void
    {
        $webhookUrl = config('echo-log.services.discord.webhook_url');
        $userIds = config('echo-log.services.discord.mention_user_ids');
        $sourceName = config('echo-log.services.discord.app_name');

        if (!$webhookUrl || empty($userIds)) {
            echo "⚠️ Webhook de Discord o usuarios no configurados.";
            return;
        }

        $errorCategory = $this->errorAnalizer->categorize($message);

        $errorContext = new ErrorContextDto(
            sourceName: $sourceName,
            scanWindow: $scanWindow,
            date: $this->clock->now()->format('Y-m-d H:i:s'),
        );

        $errorDetail = new ErrorDetailDto(
            $message,
            $errorCategory,
        );

        $recurrentError = new RecurrentErrorDto(
            $errorDetail,
            $errorContext,
            $count,
        );

        $this->discordService->sendNotification($recurrentError, $userIds);

        echo "🔔 Alerta enviada a Discord con menciones a usuarios desde [$sourceName → $errorCategory->type].";
    }

    /**
     * Sends an error notification email to configured recipients.
     *
     * @param string $message The error message.
     * @param int $count The number of occurrences.
     * @param int $scanWindow The scanning time window in minutes.
     *
     * @return void
     */
    protected function sendEmail(string $message, int $count, int $scanWindow): void
    {
        $recipients = config('echo-log.email_recipients');

        if (empty($recipients)) {
            echo "⚠️ No hay destinatarios de correo configurados." . PHP_EOL;
            return;
        }

        $sourceName = config('echo-log.app_name');
        $logViewerUrl = config('echo-log.app_url') . '/log-viewer/';
        $errorCategory = $this->errorAnalizer->categorize($message);

        $errorContext = new ErrorContextDto(
            $sourceName,
            $scanWindow,
            $this->clock->now()->format('Y-m-d H:i:s'),
            $logViewerUrl,
        );

        $errorDetail = new ErrorDetailDto(
            $message,
            $errorCategory,
        );

        $recurrentError = new RecurrentErrorDto(
            $errorDetail,
            $errorContext,
            $count,
        );

        $this->emailService->sendNotification($recurrentError, $recipients);

        echo "📧 Alertas enviadas por correo.";
    }
}
