<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203130301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_pdf (id INT AUTO_INCREMENT NOT NULL, projet_id INT NOT NULL, conf_pf_id INT DEFAULT NULL, file_name VARCHAR(255) NOT NULL, file_path VARCHAR(500) NOT NULL, custom_value DOUBLE PRECISION NOT NULL, file_size INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type VARCHAR(100) DEFAULT NULL, INDEX IDX_DB2F628DC18272 (projet_id), INDEX IDX_DB2F628DF4C0BC36 (conf_pf_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_pdf ADD CONSTRAINT FK_DB2F628DC18272 FOREIGN KEY (projet_id) REFERENCES projets (id)');
        $this->addSql('ALTER TABLE projet_pdf ADD CONSTRAINT FK_DB2F628DF4C0BC36 FOREIGN KEY (conf_pf_id) REFERENCES conf_pf (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet_pdf DROP FOREIGN KEY FK_DB2F628DC18272');
        $this->addSql('ALTER TABLE projet_pdf DROP FOREIGN KEY FK_DB2F628DF4C0BC36');
        $this->addSql('DROP TABLE projet_pdf');
    }
}
