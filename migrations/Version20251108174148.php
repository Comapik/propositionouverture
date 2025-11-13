<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251108174148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression des colonnes description et actif de la table couleur';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE couleur DROP description, DROP actif');
        $this->addSql('ALTER TABLE systeme DROP FOREIGN KEY FK_95796DE3F892AC88');
        $this->addSql('DROP INDEX IDX_95796DE3F892AC88 ON systeme');
        $this->addSql('ALTER TABLE systeme DROP ouverture_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE systeme ADD ouverture_id INT NOT NULL');
        $this->addSql('ALTER TABLE systeme ADD CONSTRAINT FK_95796DE3F892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_95796DE3F892AC88 ON systeme (ouverture_id)');
        $this->addSql('ALTER TABLE couleur ADD description LONGTEXT DEFAULT NULL, ADD actif TINYINT(1) NOT NULL');
    }
}
