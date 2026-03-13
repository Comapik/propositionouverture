<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260217101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Gamme_ID4 table and add Gamme foreign key to conf_volet';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS `Gamme_ID4` (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conf_volet ADD Gamme INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_91A702BD2686FCB2 ON conf_volet (Gamme)');
        $this->addSql('ALTER TABLE conf_volet ADD CONSTRAINT FK_91A702BD2686FCB2 FOREIGN KEY (Gamme) REFERENCES `Gamme_ID4` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_volet DROP FOREIGN KEY FK_91A702BD2686FCB2');
        $this->addSql('DROP INDEX IDX_91A702BD2686FCB2 ON conf_volet');
        $this->addSql('ALTER TABLE conf_volet DROP Gamme');
        $this->addSql('DROP TABLE IF EXISTS `Gamme_ID4`');
    }
}
