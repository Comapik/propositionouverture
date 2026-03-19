<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319134049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ON DELETE CASCADE to foreign keys referencing projets table';
    }

    public function up(Schema $schema): void
    {
        // Clean up orphaned records in conf_pf before adding CASCADE constraint
        $this->addSql('DELETE FROM conf_pf WHERE projet_id IS NULL OR projet_id NOT IN (SELECT id FROM projets)');
        
        // Add CASCADE constraint for conf_pf (if not exists)
        $this->addSql('ALTER TABLE conf_pf CHANGE projet_id projet_id INT NOT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CC18272 FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE');
        
        // Clean up orphaned records in projet_pdf before adding CASCADE constraint
        $this->addSql('DELETE FROM projet_pdf WHERE projet_id IS NULL OR projet_id NOT IN (SELECT id FROM projets)');
        
        // Check if constraint exists and drop it
        $this->addSql('SET @constraint_name = (SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = "projet_pdf" AND TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = "projets" LIMIT 1)');
        $this->addSql('SET @query = IF(@constraint_name IS NOT NULL, CONCAT("ALTER TABLE projet_pdf DROP FOREIGN KEY ", @constraint_name), "SELECT 1")');
        $this->addSql('PREPARE stmt FROM @query');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
        
        // Add CASCADE constraint for projet_pdf
        $this->addSql('ALTER TABLE projet_pdf ADD CONSTRAINT FK_DB2F628DC18272 FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Remove CASCADE constraint for conf_pf
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CC18272');
        $this->addSql('ALTER TABLE conf_pf CHANGE projet_id projet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CC18272 FOREIGN KEY (projet_id) REFERENCES projets (id)');
        
        // Remove CASCADE constraint for projet_pdf
        $this->addSql('ALTER TABLE projet_pdf DROP FOREIGN KEY FK_DB2F628DC18272');
        $this->addSql('ALTER TABLE projet_pdf ADD CONSTRAINT FK_DB2F628DC18272 FOREIGN KEY (projet_id) REFERENCES projets (id)');
    }
}
