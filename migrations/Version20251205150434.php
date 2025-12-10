<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205150434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pdf_schema (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(500) DEFAULT NULL, image_path VARCHAR(255) NOT NULL, preview_image VARCHAR(255) DEFAULT NULL, actif TINYINT(1) NOT NULL, ordre INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_pdf ADD pdf_schema_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE projet_pdf ADD CONSTRAINT FK_DB2F628D62B81754 FOREIGN KEY (pdf_schema_id) REFERENCES pdf_schema (id)');
        $this->addSql('CREATE INDEX IDX_DB2F628D62B81754 ON projet_pdf (pdf_schema_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet_pdf DROP FOREIGN KEY FK_DB2F628D62B81754');
        $this->addSql('DROP TABLE pdf_schema');
        $this->addSql('DROP INDEX IDX_DB2F628D62B81754 ON projet_pdf');
        $this->addSql('ALTER TABLE projet_pdf DROP pdf_schema_id');
    }
}
