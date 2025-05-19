<?php

use Ecomac\EchoLog\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Ecomac\EchoLog\Dto\ErrorDetailDto;
use Ecomac\EchoLog\Dto\ErrorContextDto;
use Ecomac\EchoLog\Dto\ErrorCategoryDto;
use Ecomac\EchoLog\Dto\RecurrentErrorDto;
use Ecomac\EchoLog\Services\DiscordService;

class DiscordServiceTest extends TestCase
{
    public function test_send_notification_to_discord()
    {
        Http::fake([
            'https://discord.com/api/*' => Http::response(['success' => true], 200),
        ]);

        $dto = $this->makeDummyDto();

        $service = new DiscordService();
        $service->sendNotification($dto, ['123456789']);

        Http::assertSent(function ($request) {
            return $request->url() ===  $this->app['config']->get('echo-log.services.discord.webhook_url')
                && $request['content'] === '<@123456789>'
                && isset($request['embeds'][0]['title'])
                && str_contains($request['embeds'][0]['title'], '❗') // emoji
                && str_contains($request['embeds'][0]['title'], 'Critical')
                && $request['embeds'][0]['fields'][0]['name'] === '📝 Mensaje'
                && $request['embeds'][0]['fields'][0]['value'] === '`Test error`'
                ;
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
}
