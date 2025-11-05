<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Exception thrown when a resource is not found.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles resource not found scenarios
 * - Liskov Substitution: Can replace parent exception
 */
final class ResourceNotFoundException extends ApplicationException
{
    public function __construct(string $resource = 'Resource', int $code = 404)
    {
        parent::__construct(
            sprintf('%s not found', $resource),
            $code
        );
    }
}