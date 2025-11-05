<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105143758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fournisseurs (id INT AUTO_INCREMENT NOT NULL, produit_id INT DEFAULT NULL, marque VARCHAR(255) NOT NULL, INDEX IDX_D3EF0041F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fournisseurs ADD CONSTRAINT FK_D3EF0041F347EFB FOREIGN KEY (produit_id) REFERENCES produits (id)');
        $this->addSql('ALTER TABLE conf_pf ADD fournisseur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816C670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs (id)');
        $this->addSql('CREATE INDEX IDX_AD1816C670C757F ON conf_pf (fournisseur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C670C757F');
        $this->addSql('ALTER TABLE fournisseurs DROP FOREIGN KEY FK_D3EF0041F347EFB');
        $this->addSql('DROP TABLE fournisseurs');
        $this->addSql('DROP INDEX IDX_AD1816C670C757F ON conf_pf');
        $this->addSql('ALTER TABLE conf_pf DROP fournisseur_id');
    }
}
