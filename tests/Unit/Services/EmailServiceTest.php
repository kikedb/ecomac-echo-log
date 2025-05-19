<?php

namespace Tests\Unit\Services;


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
}
