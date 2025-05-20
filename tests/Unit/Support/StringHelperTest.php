<?php

namespace Ecomac\EchoLog\Tests\Unit\Support;

use Ecomac\EchoLog\Support\StringHelper;
use Ecomac\EchoLog\Tests\TestCase;

/**
 * Class StringHelperTest
 *
 * Unit tests for the StringHelper class.
 *
 * @package Ecomac\EchoLog\Tests\Unit\Support
 */
class StringHelperTest extends TestCase
{
    /**
     * Tests that the limit method returns the original string
     * when it is shorter than the specified limit.
     *
     * @return void
     */
    public function test_limit_returns_unmodified_string_if_under_limit()
    {
        $helper = new StringHelper();
        $string = 'Short string';
        $limit = 50;

        $result = $helper->limit($string, $limit, ' (...)');

        $this->assertEquals($string, $result);
    }

    /**
     * Tests that the limit method truncates the string correctly
     * and appends the specified suffix if the string exceeds the limit.
     *
     * @return void
     */
    public function test_limit_truncates_string_and_appends_end()
    {
        $helper = new StringHelper();
        $string = 'This is a very long string that should be truncated';
        $limit = 20;
        $end = ' (...)';

        $result = $helper->limit($string, $limit, $end);

        // The result length is less than or equal to the limit,
        // including the suffix.
        $this->assertStringStartsWith('This is a very', $result);
        $this->assertStringEndsWith($end, $result);
        $this->assertLessThanOrEqual($limit, mb_strwidth($result));
    }
}
