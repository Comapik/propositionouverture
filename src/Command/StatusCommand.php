<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ApplicationServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to display application status following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Displays application status only
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on ApplicationServiceInterface
 * 
 * Following DRY principle: Reusable status display
 * Following KISS principle: Simple command structure
 */
#[AsCommand(
    name: 'app:status',
    description: 'Display application status and information',
    aliases: ['app:info']
)]
final class StatusCommand extends Command
{
    public function __construct(
        private readonly ApplicationServiceInterface $applicationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Application Status');

        $io->section('Basic Information');
        $io->definitionList(
            ['Name' => $this->applicationService->getApplicationName()],
            ['Version' => $this->applicationService->getVersion()],
            ['Environment' => $this->applicationService->getEnvironment()],
            ['PHP Version' => PHP_VERSION],
            ['Symfony Version' => \Symfony\Component\HttpKernel\Kernel::VERSION]
        );

        $io->section('Architecture Principles');
        $io->listing([
            'SOLID: Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion',
            'DRY: Don\'t Repeat Yourself - Code réutilisable',
            'KISS: Keep It Simple, Stupid - Simplicité avant tout'
        ]);

        $io->success('Application is running successfully!');

        return Command::SUCCESS;
    }
}