<?php

namespace Ecomac\EchoLog\Dto;

/**
 * Data Transfer Object representing the details of an error,
 * including the error message text and its associated category.
 */
class ErrorDetailDto
{
    /**
     * Constructor for the error detail DTO.
     *
     * @param string $messageText The full error message text.
     * @param ErrorCategoryDto $category The category of the error (type, emoji, description).
     */
    public function __construct(
        public readonly string $messageText,
        public readonly ErrorCategoryDto $category,
    ) {}
}
