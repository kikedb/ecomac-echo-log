<?php

namespace Ecomac\EchoLog\Contracts;

interface StringHelperInterface
{
    public function limit(string $string, int $limit, string $end = ''): string;
}
