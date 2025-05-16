<?php

namespace Ecomac\EchoLog\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecurrentErrorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $messageText;
    public int $count;
    public string $emoji;
    public string $title;
    public string $type;
    public string $sourceName;
    public int $scanWindow;
    public string $logViewerUrl;

    public function __construct(string $messageText, int $count, string $emoji, string $title, string $type, string $sourceName, int $scanWindow, string $logViewerUrl)
    {
        $this->messageText = $messageText;
        $this->count = $count;
        $this->emoji = $emoji;
        $this->title = $title;
        $this->type = $type;
        $this->sourceName = $sourceName;
        $this->scanWindow = $scanWindow;
        $this->logViewerUrl = $logViewerUrl;
    }

    public function build()
    {
        return $this
            ->subject("{$this->emoji} [{$this->sourceName} → Mail] {$this->title}")
            ->view('echo-log::mails.recurrent-error');
    }
}
