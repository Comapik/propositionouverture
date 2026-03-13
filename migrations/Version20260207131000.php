<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create aeration table
 */
final class Version20260207131000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create aeration table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE aeration (id INT AUTO_INCREMENT NOT NULL, position VARCHAR(100) NOT NULL, modele VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE aeration');
    }
}
