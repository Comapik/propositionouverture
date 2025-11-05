<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Application service providing basic application information.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Manages application metadata only
 * - Open/Closed: Can be extended without modification
 * - Liskov Substitution: Can be substituted by any implementation of the interface
 * - Interface Segregation: Implements focused interface
 * - Dependency Inversion: Depends on kernel.environment parameter injection
 * 
 * Following DRY principle: Centralized application information
 * Following KISS principle: Simple, straightforward implementation
 */
final readonly class ApplicationService implements ApplicationServiceInterface
{
    public function __construct(
        #[Autowire('%kernel.environment%')] 
        private string $environment
    ) {
    }

    public function getApplicationName(): string
    {
        return 'Proposition Ouverture';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }
}