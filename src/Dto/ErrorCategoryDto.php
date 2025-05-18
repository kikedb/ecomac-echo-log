<?php

namespace Ecomac\EchoLog\Dto;

/**
 * Data Transfer Object representing the category of an error,
 * including an emoji icon, type identifier, and a descriptive title.
 */
class ErrorCategoryDto
{
    /**
     * Constructor for the error category DTO.
     *
     * @param string $emoji An emoji symbol representing the error category.
     * @param string $type A short string identifying the error type.
     * @param string $title A descriptive title for the error category.
     */
    public function __construct(
        public readonly string $emoji,
        public readonly string $type,
        public readonly string $title,
    ) {}
}
