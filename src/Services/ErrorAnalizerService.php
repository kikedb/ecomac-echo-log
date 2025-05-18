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

        return match (true) {
            str_contains($lower, 'smtp') || str_contains($lower, 'mail') || str_contains($lower, 'connection refused') =>
                new ErrorCategoryDto('📧', "Mail", "Fallo en envío de correos"),
            str_contains($lower, 'sql') || str_contains($lower, 'pdo') || str_contains($lower, 'database') =>
                new ErrorCategoryDto('🛢️', "DB", "Error de base de datos"),
            str_contains($lower, 'unauthorized') || str_contains($lower, 'unauthenticated') || str_contains($lower, 'token') =>
                new ErrorCategoryDto('🔐', "Auth", "Error de autenticación"),
            str_contains($lower, 'file') || str_contains($lower, 'filesystem') || str_contains($lower, 'permission') =>
                new ErrorCategoryDto('📁', "FS", "Error de archivos o permisos"),
            str_contains($lower, 'redis') || str_contains($lower, 'cache') =>
                new ErrorCategoryDto('🧠', "Cache", "Fallo en Redis/cache"),
            str_contains($lower, 'curl') || str_contains($lower, 'timeout') || str_contains($lower, 'http') || str_contains($lower, 'request') =>
                new ErrorCategoryDto('🌐', "Network", "Fallo de red o HTTP"),
            default =>
                new ErrorCategoryDto('❗', "Unknown", "Error no categorizado"),
        };
    }
}
