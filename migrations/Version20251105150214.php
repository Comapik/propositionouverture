<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105150214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE systeme (id INT AUTO_INCREMENT NOT NULL, fournisseur_id INT NOT NULL, nom VARCHAR(100) NOT NULL, description VARCHAR(500) DEFAULT NULL, INDEX IDX_95796DE3670C757F (fournisseur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE systeme ADD CONSTRAINT FK_95796DE3670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs (id)');
        $this->addSql('ALTER TABLE conf_pf ADD systeme_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816C346F772E FOREIGN KEY (systeme_id) REFERENCES systeme (id)');
        $this->addSql('CREATE INDEX IDX_AD1816C346F772E ON conf_pf (systeme_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C346F772E');
        $this->addSql('ALTER TABLE systeme DROP FOREIGN KEY FK_95796DE3670C757F');
        $this->addSql('DROP TABLE systeme');
        $this->addSql('DROP INDEX IDX_AD1816C346F772E ON conf_pf');
        $this->addSql('ALTER TABLE conf_pf DROP systeme_id');
    }
}
