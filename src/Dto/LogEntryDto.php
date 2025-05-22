<?php

namespace Ecomac\EchoLog\Dto;

class LogEntryDto
{
    public function __construct(
        public readonly string $rawLine,
        public readonly string $timestamp,
        public readonly string $level,
        public readonly string $message
    ) {}
}
