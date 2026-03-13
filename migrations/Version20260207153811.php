<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207153811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE position (id INT AUTO_INCREMENT NOT NULL, position VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aeration ADD position_id INT DEFAULT NULL, DROP position');
        $this->addSql('ALTER TABLE aeration ADD CONSTRAINT FK_A961126ADD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        $this->addSql('CREATE INDEX IDX_A961126ADD842E46 ON aeration (position_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aeration DROP FOREIGN KEY FK_A961126ADD842E46');
        $this->addSql('DROP TABLE position');
        $this->addSql('DROP INDEX IDX_A961126ADD842E46 ON aeration');
        $this->addSql('ALTER TABLE aeration ADD position VARCHAR(100) NOT NULL, DROP position_id');
    }
}
