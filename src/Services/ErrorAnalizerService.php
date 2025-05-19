<?php

namespace Ecomac\EchoLog\Services;

use Ecomac\EchoLog\Dto\ErrorCategoryDto;

/**
 * Class ErrorAnalizerService
 *
 * Responsible for analyzing error messages.
 *
 */
class ErrorAnalizerService
{
    /**
     * Analyzes and categorizes an error message by inspecting its content.
     *
     * Looks for specific keywords in the message to determine the error category.
     *
     * @param string $message The error message to analyze.
     *
     * @return ErrorCategoryDto Returns a data transfer object representing the error category.
     */
    public function categorize(string $message): ErrorCategoryDto
    {
        $lower = strtolower($message);
        $categories = config('error-categories');

        foreach ($categories as $category) {
            foreach ($category['keywords'] as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return new ErrorCategoryDto(
                        $category['icon'],
                        $category['code'],
                        $category['description']
                    );
                }
            }
        }

        return new ErrorCategoryDto('❗', 'Unknown', 'Error no categorizado');
    }
}
