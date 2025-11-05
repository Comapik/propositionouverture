<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

/**
 * Base exception class for application-specific exceptions.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles application exceptions
 * - Open/Closed: Can be extended for specific exception types
 * 
 * Following DRY principle: Base class for all custom exceptions
 * Following KISS principle: Simple exception hierarchy
 */
abstract class ApplicationException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}