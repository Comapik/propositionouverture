<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer les tables manquantes du système de volet
 */
final class Version20260320130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables Teintes_encadrement_volet et Specificites_caisson';
    }

    public function up(Schema $schema): void
    {
        // Création de la table Teintes_encadrement_volet
        $this->addSql('CREATE TABLE Teintes_encadrement_volet (
            id INT AUTO_INCREMENT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Création de la table Specificites_caisson
        $this->addSql('CREATE TABLE Specificites_caisson (
            id INT AUTO_INCREMENT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Suppression des tables
        $this->addSql('DROP TABLE IF EXISTS Teintes_encadrement_volet');
        $this->addSql('DROP TABLE IF EXISTS Specificites_caisson');
    }
}
