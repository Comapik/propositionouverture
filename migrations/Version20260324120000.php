<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter la description des specificites de caisson
 */
final class Version20260324120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne description dans la table Specificites_caisson';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Specificites_caisson ADD description VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Specificites_caisson DROP description');
    }
}
