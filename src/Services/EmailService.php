<?php

namespace Ecomac\EchoLog\Services;

use Illuminate\Support\Facades\Mail;
use Ecomac\EchoLog\Dto\RecurrentErrorDto;
use Ecomac\EchoLog\Mail\RecurrentErrorMail;

/**
 * Class EmailService
 *
 * Service to send email notifications about recurrent errors.
 */
class EmailService
{
    /**
     * Sends an email notification to a list of recipients.
     *
     * Each recipient will receive an email containing details of the recurrent error.
     *
     * @param RecurrentErrorDto $recurrentError Data transfer object with recurrent error details.
     * @param string[] $recipients Array of email addresses to send the notification to.
     *
     * @return void
     */
    public function sendNotification(RecurrentErrorDto $recurrentError, array $recipients): void
    {
        foreach ($recipients as $recipient) {
            Mail::to(trim($recipient))->send(
                new RecurrentErrorMail($recurrentError)
            );
            echo "📧 Alerta envia por correo a $recipient" . PHP_EOL;
        }
    }
}
