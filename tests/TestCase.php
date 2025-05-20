<?php

namespace Ecomac\EchoLog\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Ecomac\EchoLog\EchoLogServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EchoLogServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('echo-log.email_recipients', ['example@mail.com', 'example2@mail.com']);

        $app['config']->set('echo-log.services.discord.webhook_url', 'https://discord.com/api/webhook-test');
        $app['config']->set('echo-log.services.discord.mention_user_ids', ['123456','7891011']);
        $app['config']->set('echo-log.services.discord.app_name', 'Mi App');

        $app['config']->set('echo-log.app_name', 'Mi App');
        $app['config']->set('echo-log.app_url', 'https://miapp.test');

        $app['config']->set('echo-log.cooldown_minutes', 10);
        $app['config']->set('echo-log.scan_window_minutes', 5);
        $app['config']->set('echolog.levels', [
            'ERROR' => ['count' => 3],
            'CRITICAL' => ['count' => 2],
            'EMERGENCY' => ['count' => 1],
        ]);

    }
}
