<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration corrective pour Specificites_caisson
 */
final class Version20260324123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression de description et ajout des colonnes Face_exterieure_alu, Option_autre_teinte, PHT_N, PHT_R dans Specificites_caisson';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Specificites_caisson DROP description');
        $this->addSql('ALTER TABLE Specificites_caisson ADD Face_exterieure_alu BINARY(1) DEFAULT NULL, ADD Option_autre_teinte VARCHAR(255) DEFAULT NULL, ADD PHT_N BINARY(1) DEFAULT NULL, ADD PHT_R BINARY(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Specificites_caisson DROP Face_exterieure_alu, DROP Option_autre_teinte, DROP PHT_N, DROP PHT_R');
        $this->addSql('ALTER TABLE Specificites_caisson ADD description VARCHAR(255) DEFAULT NULL');
    }
}
