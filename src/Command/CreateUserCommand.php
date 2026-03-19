<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Command to create a new user.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Creates users only
 * - Dependency Inversion: Depends on abstractions (EntityManagerInterface, UserPasswordHasherInterface)
 * 
 * Following Symfony best practices:
 * - Uses password hasher for security
 * - Validates input data
 * - Provides clear feedback
 */
#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'User email')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'User first name')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'User last name')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Grant admin role')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get input or use defaults
        $email = $input->getArgument('email') ?? $io->ask('Email', 'propositiondouverture@gmail.com');
        $password = $input->getArgument('password') ?? $io->askHidden('Password', fn() => 'Prou123');
        $firstName = $input->getArgument('firstName') ?? $io->ask('First name', 'Admin');
        $lastName = $input->getArgument('lastName') ?? $io->ask('Last name', 'User');

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error("User with email '{$email}' already exists!");
            
            if ($io->confirm('Do you want to update the password?', false)) {
                $hashedPassword = $this->passwordHasher->hashPassword($existingUser, $password);
                $existingUser->setPassword($hashedPassword);
                
                if ($input->getOption('admin')) {
                    $existingUser->setRoles(['ROLE_ADMIN']);
                }
                
                $this->entityManager->flush();
                $io->success("Password updated for user '{$email}'");
                return Command::SUCCESS;
            }
            
            return Command::FAILURE;
        }

        // Create new user
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Set roles
        if ($input->getOption('admin')) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        // Persist user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("User '{$email}' created successfully!");
        $io->table(
            ['Field', 'Value'],
            [
                ['Email', $user->getEmail()],
                ['Name', $user->getFullName()],
                ['Roles', implode(', ', $user->getRoles())],
            ]
        );

        return Command::SUCCESS;
    }
}
