<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table conf_volet avec relations bidirectionnelles
 */
final class Version20260320110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table conf_volet avec relations bidirectionnelles vers projets';
    }

    public function up(Schema $schema): void
    {
        // Création de la table conf_volet
        $this->addSql('CREATE TABLE conf_volet (
            id INT AUTO_INCREMENT NOT NULL,
            projet_id INT NOT NULL,
            gamme_volet_id INT DEFAULT NULL,
            nom VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_CONFVOLET_PROJET (projet_id),
            INDEX IDX_CONFVOLET_GAMME (gamme_volet_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Ajout de la contrainte de clé étrangère vers projets
        $this->addSql('ALTER TABLE conf_volet 
            ADD CONSTRAINT FK_CONFVOLET_PROJET 
            FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE');
        
        // Ajout de la contrainte de clé étrangère vers gamme_volet
        $this->addSql('ALTER TABLE conf_volet 
            ADD CONSTRAINT FK_CONFVOLET_GAMME 
            FOREIGN KEY (gamme_volet_id) REFERENCES gamme_volet (id) ON DELETE SET NULL');
        
        // Modification de la table projets pour ajouter la contrainte de clé étrangère vers conf_volet
        // Note: conf_volet_id existe déjà dans projets, on ajoute juste la contrainte
        $this->addSql('ALTER TABLE projets 
            ADD CONSTRAINT FK_B454C1DB_CONFVOLET 
            FOREIGN KEY (conf_volet_id) REFERENCES conf_volet (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // Suppression des contraintes de clés étrangères
        $this->addSql('ALTER TABLE projets DROP FOREIGN KEY FK_B454C1DB_CONFVOLET');
        $this->addSql('ALTER TABLE conf_volet DROP FOREIGN KEY FK_CONFVOLET_PROJET');
        $this->addSql('ALTER TABLE conf_volet DROP FOREIGN KEY FK_CONFVOLET_GAMME');
        
        // Suppression de la table conf_volet
        $this->addSql('DROP TABLE conf_volet');
    }
}
