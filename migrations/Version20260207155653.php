<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migrate existing aeration data to conf_aeration
 */
final class Version20260207155653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate existing aeration-position combinations to conf_aeration';
    }

    public function up(Schema $schema): void
    {
        // Insérer les combinaisons aération-position dans conf_aeration
        // basé sur les données existantes dans la table aeration
        $this->addSql("
            INSERT INTO conf_aeration (aeration_id, position_id)
            SELECT a.id, 1 FROM aeration a WHERE a.id IN (8, 9, 10)
            UNION ALL
            SELECT a.id, 2 FROM aeration a WHERE a.id IN (11, 12)
            UNION ALL
            SELECT a.id, 3 FROM aeration a WHERE a.id IN (13, 14)
        ");
        
        // Ajouter maintenant la contrainte de clé étrangère et l'index à conf_pf
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816C47FCF597 FOREIGN KEY (conf_aeration_id) REFERENCES conf_aeration (id)');
        $this->addSql('CREATE INDEX IDX_AD1816C47FCF597 ON conf_pf (conf_aeration_id)');
    }

    public function down(Schema $schema): void
    {
        // Pas besoin de rollback car les données seront supprimées avec la table
        $this->addSql('DELETE FROM conf_aeration');
    }
}
