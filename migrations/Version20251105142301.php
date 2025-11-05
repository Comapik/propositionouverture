<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105142301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C670C757F');
        $this->addSql('DROP TABLE fournisseurs');
        $this->addSql('DROP INDEX IDX_AD1816C670C757F ON conf_pf');
        $this->addSql('ALTER TABLE conf_pf CHANGE fournisseur_id ouverture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CF892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id)');
        $this->addSql('CREATE INDEX IDX_AD1816CF892AC88 ON conf_pf (ouverture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fournisseurs (id INT AUTO_INCREMENT NOT NULL, marque VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CF892AC88');
        $this->addSql('DROP INDEX IDX_AD1816CF892AC88 ON conf_pf');
        $this->addSql('ALTER TABLE conf_pf CHANGE ouverture_id fournisseur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816C670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AD1816C670C757F ON conf_pf (fournisseur_id)');
    }
}
