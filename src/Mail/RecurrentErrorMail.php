<?php

namespace Ecomac\EchoLog\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Ecomac\EchoLog\Dto\RecurrentErrorDto;

/**
 * Class RecurrentErrorMail
 *
 * Mailable class to send emails related to recurrent errors.
 */
class RecurrentErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The DTO containing recurrent error information.
     *
     * @var RecurrentErrorDto
     */
    public RecurrentErrorDto $recurrentError;

    /**
     * Constructor.
     *
     * @param RecurrentErrorDto $recurrentError DTO with details about the recurrent error.
     */
    public function __construct(RecurrentErrorDto $recurrentError)
    {
        $this->recurrentError = $recurrentError;
    }

    /**
     * Build the email message.
     *
     * Sets the subject based on the error category and context,
     * and specifies the view to be used for the email body.
     *
     * @return $this
     */
    public function build()
    {
        $errorCategory = $this->recurrentError->details->category;
        $errorContext = $this->recurrentError->context;

        return $this
            ->subject("{$errorCategory->emoji} [{$errorContext->sourceName} → {$errorCategory->type}] {$errorCategory->title}")
            ->view('echo-log::mails.recurrent-error');
    }
}
