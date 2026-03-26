<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table gamme_volet
 */
final class Version20260320100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table gamme_volet pour stocker les gammes de volets roulants iD4';
    }

    public function up(Schema $schema): void
    {
        // Création de la table gamme_volet
        $this->addSql('CREATE TABLE gamme_volet (
            id INT AUTO_INCREMENT NOT NULL, 
            nom VARCHAR(255) NOT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Insertion des gammes iD4 existantes
        $this->addSql("INSERT INTO gamme_volet (nom) VALUES 
            ('BLOC N / R iD4'),
            ('BLOC Intégré iD4'),
            ('Coffre Tunnel iD4'),
            ('Coffre Linteau iD4'),
            ('Rénovation iD4')
        ");
    }

    public function down(Schema $schema): void
    {
        // Suppression de la table gamme_volet
        $this->addSql('DROP TABLE gamme_volet');
    }
}
