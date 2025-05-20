<?php

namespace Ecomac\EchoLog\Support;

use Ecomac\EchoLog\Contracts\StringHelperInterface;

class StringHelper implements StringHelperInterface
{
    /**
     * Limit the number of characters in a string.
     *
     * @param string $string The string to limit.
     * @param int $limit The maximum number of characters.
     * @param string $end The string to append if the string is truncated.
     * @return string The limited string.
     */
    public function limit(string $string, int $limit, string $end = ''): string
    {
        return mb_strimwidth($string, 0, $limit, $end);
    }
}
