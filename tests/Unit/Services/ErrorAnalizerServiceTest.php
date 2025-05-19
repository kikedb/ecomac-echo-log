<?php

namespace Tests\Unit\Services;

use Ecomac\EchoLog\Tests\TestCase;
use Ecomac\EchoLog\Dto\ErrorCategoryDto;
use Ecomac\EchoLog\Services\ErrorAnalizerService;

class ErrorAnalizerServiceTest extends TestCase
{
    public function test_categorizes_known_errors_correctly()
    {
        $categorizer = new ErrorAnalizerService();

        $cases = [
            ['smtp connection failed', 'Mail'],
            ['PDOException: could not connect to database', 'DB'],
            ['Unauthenticated access attempt', 'Auth'],
            ['Failed to open stream: permission denied', 'FS'],
            ['Redis connection timed out', 'Cache'],
            ['Curl error: timeout reached', 'Network'],
            ['Class not found: Foo\\Bar', 'App'],
        ];

        foreach ($cases as [$message, $expectedCode]) {
            $result = $categorizer->categorize($message);
            $this->assertInstanceOf(ErrorCategoryDto::class, $result);
            $this->assertEquals($expectedCode, $result->type, "Message: '$message' should match code '$expectedCode'");
        }
    }

    public function test_returns_unknown_for_unmatched_message()
    {
        $categorizer = new ErrorAnalizerService();

        $result = $categorizer->categorize('This is a completely unrelated message');
        $this->assertEquals('Unknown', $result->type);
        $this->assertEquals('❗', $result->emoji);
        $this->assertEquals('Error no categorizado', $result->title);
    }
}