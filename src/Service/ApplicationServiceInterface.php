<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Interface for application service following SOLID principles.
 * 
 * Interface Segregation Principle: Small, focused interface
 * Dependency Inversion Principle: Depend on abstractions, not concretions
 */
interface ApplicationServiceInterface
{
    public function getApplicationName(): string;
    public function getVersion(): string;
    public function getEnvironment(): string;
}