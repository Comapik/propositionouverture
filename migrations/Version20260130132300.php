<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130132300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vitrage (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(100) NOT NULL, rw VARCHAR(50) DEFAULT NULL, epaisseur VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conf_pf ADD vitrage_id INT DEFAULT NULL, DROP vitrage');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CC6B2988F FOREIGN KEY (vitrage_id) REFERENCES vitrage (id)');
        $this->addSql('CREATE INDEX IDX_AD1816CC6B2988F ON conf_pf (vitrage_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CC6B2988F');
        $this->addSql('DROP TABLE vitrage');
        $this->addSql('DROP INDEX IDX_AD1816CC6B2988F ON conf_pf');
        $this->addSql('ALTER TABLE conf_pf ADD vitrage VARCHAR(100) DEFAULT NULL, DROP vitrage_id');
    }
}
