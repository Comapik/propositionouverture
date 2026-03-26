<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour corriger le schéma conf_volet et ajouter l'index unique sur projets.conf_volet_id
 */
final class Version20260320120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correction du type datetime et ajout de l\'index unique pour la relation OneToOne';
    }

    public function up(Schema $schema): void
    {
        // Correction des types de colonnes created_at et updated_at (enlever le commentaire datetime_immutable)
        $this->addSql('ALTER TABLE conf_volet CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE conf_volet CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        
        // Pour ajouter un index unique, on doit d'abord supprimer la contrainte FK
        $this->addSql('ALTER TABLE projets DROP FOREIGN KEY FK_B454C1DB_CONFVOLET');
        
        // Supprimer l'index existant
        $this->addSql('DROP INDEX FK_B454C1DB_CONFVOLET ON projets');
        
        // Créer l'index unique
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B454C1DBD143FC2B ON projets (conf_volet_id)');
        
        // Recréer la contrainte FK
        $this->addSql('ALTER TABLE projets ADD CONSTRAINT FK_B454C1DB_CONFVOLET FOREIGN KEY (conf_volet_id) REFERENCES conf_volet (id) ON DELETE SET NULL');
        
        // Renommage des index pour suivre les conventions Doctrine
        $this->addSql('ALTER TABLE conf_volet RENAME INDEX idx_confvolet_projet TO IDX_91A702BDC18272');
        $this->addSql('ALTER TABLE conf_volet RENAME INDEX idx_confvolet_gamme TO IDX_91A702BD182E1051');
    }

    public function down(Schema $schema): void
    {
        // Restauration des index originaux
        $this->addSql('ALTER TABLE conf_volet RENAME INDEX IDX_91A702BDC18272 TO idx_confvolet_projet');
        $this->addSql('ALTER TABLE conf_volet RENAME INDEX IDX_91A702BD182E1051 TO idx_confvolet_gamme');
        
        // Suppression de la contrainte FK
        $this->addSql('ALTER TABLE projets DROP FOREIGN KEY FK_B454C1DB_CONFVOLET');
        
        // Suppression de l'index unique
        $this->addSql('DROP INDEX UNIQ_B454C1DBD143FC2B ON projets');
        
        // Restauration de l'index classique
        $this->addSql('CREATE INDEX FK_B454C1DB_CONFVOLET ON projets (conf_volet_id)');
        
        // Recréation de la contrainte FK
        $this->addSql('ALTER TABLE projets ADD CONSTRAINT FK_B454C1DB_CONFVOLET FOREIGN KEY (conf_volet_id) REFERENCES conf_volet (id) ON DELETE SET NULL');
        
        // Restauration du commentaire datetime_immutable (si nécessaire)
        $this->addSql('ALTER TABLE conf_volet CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE conf_volet CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
