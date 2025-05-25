<?php

namespace Tests\Unit\Services;


use Mockery;
use Ecomac\EchoLog\Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Ecomac\EchoLog\Dto\ErrorDetailDto;
use Ecomac\EchoLog\Dto\ErrorContextDto;
use Ecomac\EchoLog\Dto\ErrorCategoryDto;
use Ecomac\EchoLog\Dto\RecurrentErrorDto;
use Ecomac\EchoLog\Services\EmailService;
use Ecomac\EchoLog\Mail\RecurrentErrorMail;

class EmailServiceTest extends TestCase
{
    public function test_email_service()
    {
        Mail::fake();
        $emailService = new EmailService();
        $dto = $this->makeDummyDto();
        $recipients = $this->app['config']->get('echo-log.email_recipients');
        $emailService->sendNotification($dto, $recipients);
        Mail::assertSent(RecurrentErrorMail::class, $recipients);
        Mail::assertSent(RecurrentErrorMail::class, count($recipients));
        Mail::assertSent(RecurrentErrorMail::class, function ($mail) use ($dto, $recipients) {
            return $mail->recurrentError === $dto;
        });
    }

    private function makeDummyDto(): RecurrentErrorDto
    {
        $errorDetail = new ErrorDetailDto(
            messageText: 'Test error',
            category : new ErrorCategoryDto(
                emoji : '❗',
                type : 'Critical',
                title : 'Critical Error',
            ),
        );
        $errorContext = new ErrorContextDto(
            sourceName : 'My System',
            scanWindow : 15,
            date : now()->toIso8601String(),
        );
        $count = 3;

        return new RecurrentErrorDto(
            details : $errorDetail,
            context : $errorContext,
            count : $count,
        );
    }

    public function test_email_not_sent_if_no_recipients()
    {
        Mail::fake();
        $emailService = new EmailService();
        $dto = $this->makeDummyDto();
        $emailService->sendNotification($dto, []);
        Mail::assertNothingSent();
    }

    public function test_send_notification_sends_emails_to_all_recipients()
    {
        Mail::fake();

        config(['echo-log.mailer' => 'mailgun']);

        $recipients = config('echo-log.email_recipients');

        $errorDto = new RecurrentErrorDto(
            details: new ErrorDetailDto(
                messageText: 'Test error',
                category: new ErrorCategoryDto(
                    emoji: '❗',
                    type: 'Critical',
                    title: 'Critical Error',
                ),
            ),
            context: new ErrorContextDto(
                sourceName: 'My System',
                scanWindow: 15,
                date: now()->toIso8601String(),
            ),
            count: 3,
        );

        $emailService = new EmailService();

        $emailService->sendNotification($errorDto, $recipients);

        foreach ($recipients as $recipient) {
            Mail::assertSent(RecurrentErrorMail::class, function ($mail) use ($recipient, $errorDto) {
                return $mail->hasTo($recipient) &&
                        $mail->recurrentError->details->category === $errorDto->details->category &&
                        $mail->recurrentError->details->category->emoji === $errorDto->details->category->emoji;
            });
        }

        Mail::assertSent(RecurrentErrorMail::class, count($recipients));
    }
    public function test_send_notification_uses_custom_mailer_from_config()
    {
        // Configuramos el mailer personalizado
        config(['echo-log.mailer' => 'mailgun']);

        config(['echo-log.email_recipients' => ['test@mail.com']]);

        $recipients = config('echo-log.email_recipients');

        $errorDto = Mockery::mock(RecurrentErrorDto::class);

        $mailerMock = Mockery::mock();
        $mailerMock->shouldReceive('to')
            ->with('test@mail.com')
            ->once()
            ->andReturnSelf();

        $mailerMock->shouldReceive('send')
            ->once()
            ->with(Mockery::type(RecurrentErrorMail::class));

        // // Mock para Facade Mail
        Mail::shouldReceive('mailer')
            ->once()
            ->with('mailgun')
            ->andReturn($mailerMock);

        $emailService = new EmailService();
        $emailService->sendNotification($errorDto, $recipients);
    }

    public function test_send_notification_uses_default_mailer_when_no_config()
    {
        config(['echo-log.mailer' => null]);
        config(['echo-log.email_recipients' => ['test@mail.com']]);
        $recipients = config('echo-log.email_recipients');

        $errorDto = Mockery::mock(RecurrentErrorDto::class);


        $mailerMock = Mockery::mock();
        $mailerMock->shouldReceive('to')
            ->once()
            ->with('test@mail.com')
            ->andReturnSelf();

        $mailerMock->shouldReceive('send')
            ->once()
            ->with(Mockery::type(RecurrentErrorMail::class));

        Mail::shouldReceive('mailer')
            ->once()
            ->with(null)
            ->andReturn($mailerMock);

        $emailService = new EmailService();
        $emailService->sendNotification($errorDto, $recipients);
    }
}
