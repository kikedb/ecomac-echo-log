<?php

namespace Ecomac\EchoLog\Factories;

use Ecomac\EchoLog\Dto\LogEntryDto;

class LogEntryDtoFactory
{
    public function createFromLogEntry(array $entry): LogEntryDto
    {
        return new LogEntryDto(
            rawLine: $entry[0],
            timestamp: $entry[1],
            level: $entry[2],
            message: $entry[3]
        );
    }
}
