<?php

namespace Ecomac\EchoLog\Dto;

use Ecomac\EchoLog\Dto\ErrorDetailDto;
use Ecomac\EchoLog\Dto\ErrorContextDto;

/**
 * Data Transfer Object representing a recurrent error,
 * including its details, context, and number of occurrences.
 */
class RecurrentErrorDto
{
    /**
     * @param ErrorDetailDto $details Details of the error (message, source, etc.)
     * @param ErrorContextDto $context Context of the error (timestamp, environment, etc.)
     * @param int $count Number of times this error has occurred
     */
    public function __construct(
        public readonly ErrorDetailDto $details,
        public readonly ErrorContextDto $context,
        public readonly int $count,
    ) {}
}
