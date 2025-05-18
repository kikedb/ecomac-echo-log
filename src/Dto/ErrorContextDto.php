<?php

namespace Ecomac\EchoLog\Dto;

/**
 * Data Transfer Object that holds context information
 * about a specific error occurrence.
 */
class ErrorContextDto
{
    /**
     * @param string $sourceName Name of the log source or application
     * @param int $scanWindow Time window (in minutes) used during the log scan
     * @param string $logViewerUrl URL to the external log viewer for this error
     * @param string $date Date and time of the error occurrence (formatted as string)
     */
    public function __construct(
        public readonly string $sourceName,
        public readonly int $scanWindow,
        public readonly string $date,
        public readonly string|null $logViewerUrl = null,
    ) {}
}
