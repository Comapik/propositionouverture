<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112140455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert Systeme-Ouverture relationship from ManyToOne to ManyToMany';
    }

    public function up(Schema $schema): void
    {
        // Create the junction table for many-to-many relationship
        $this->addSql('CREATE TABLE systeme_ouverture (systeme_id INT NOT NULL, ouverture_id INT NOT NULL, INDEX IDX_71CCBC17346F772E (systeme_id), INDEX IDX_71CCBC17F892AC88 (ouverture_id), PRIMARY KEY(systeme_id, ouverture_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE systeme_ouverture ADD CONSTRAINT FK_71CCBC17346F772E FOREIGN KEY (systeme_id) REFERENCES systeme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE systeme_ouverture ADD CONSTRAINT FK_71CCBC17F892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id) ON DELETE CASCADE');
        
        // Migrate existing data from systeme.ouverture_id to systeme_ouverture table
        $this->addSql('INSERT INTO systeme_ouverture (systeme_id, ouverture_id) SELECT id, ouverture_id FROM systeme WHERE ouverture_id IS NOT NULL');
        
        // Remove old foreign key and column
        $this->addSql('ALTER TABLE systeme DROP FOREIGN KEY FK_95796DE3F892AC88');
        $this->addSql('DROP INDEX IDX_95796DE3F892AC88 ON systeme');
        $this->addSql('ALTER TABLE systeme DROP ouverture_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE systeme_ouverture DROP FOREIGN KEY FK_71CCBC17346F772E');
        $this->addSql('ALTER TABLE systeme_ouverture DROP FOREIGN KEY FK_71CCBC17F892AC88');
        $this->addSql('DROP TABLE systeme_ouverture');
        $this->addSql('ALTER TABLE systeme ADD ouverture_id INT NOT NULL');
        $this->addSql('ALTER TABLE systeme ADD CONSTRAINT FK_95796DE3F892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_95796DE3F892AC88 ON systeme (ouverture_id)');
    }
}
