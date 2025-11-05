<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to remove price fields from conf_pf table
 */
final class Version20251105143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove prix_unitaire and prix_total fields from conf_pf table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_pf DROP prix_unitaire, DROP prix_total');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_pf ADD prix_unitaire DECIMAL(10,2) DEFAULT NULL, ADD prix_total DECIMAL(10,2) DEFAULT NULL');
    }
}