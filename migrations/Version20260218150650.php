<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218150650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE extension_offre ADD exo TINYINT(1) NOT NULL, DROP libelle, DROP description, DROP montant, DROP actif');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE extension_offre ADD libelle VARCHAR(255) NOT NULL, ADD description LONGTEXT DEFAULT NULL, ADD montant NUMERIC(10, 2) DEFAULT NULL, ADD actif TINYINT(1) DEFAULT 1 NOT NULL, DROP exo');
    }
}
