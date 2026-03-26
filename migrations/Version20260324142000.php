<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour compléter la table Teintes_tablier_volet
 */
final class Version20260324142000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des colonnes nom et image dans la table Teintes_tablier_volet';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Teintes_tablier_volet ADD nom VARCHAR(255) DEFAULT NULL, ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Teintes_tablier_volet DROP nom, DROP image');
    }
}
