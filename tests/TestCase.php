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
        $app['config']->set('echo-log.services.discord.webhook_url', 'https://discord.com/api/webhook-test');
        $app['config']->set('echo-log.email_recipients', 'example@mail.com,example2@mail.com');

    }
}
