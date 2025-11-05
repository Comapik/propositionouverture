<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add position field to conf_pf table
 */
final class Version20251105142000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add position field to conf_pf table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_pf ADD position VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_pf DROP position');
    }
}