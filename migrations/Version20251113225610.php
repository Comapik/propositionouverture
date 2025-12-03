<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113225610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE systeme_type_fenetre_porte (systeme_id INT NOT NULL, type_fenetre_porte_id INT NOT NULL, INDEX IDX_ABFB1291346F772E (systeme_id), INDEX IDX_ABFB1291E11E2A6B (type_fenetre_porte_id), PRIMARY KEY(systeme_id, type_fenetre_porte_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_fenetre_porte (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD CONSTRAINT FK_ABFB1291346F772E FOREIGN KEY (systeme_id) REFERENCES systeme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD CONSTRAINT FK_ABFB1291E11E2A6B FOREIGN KEY (type_fenetre_porte_id) REFERENCES type_fenetre_porte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conf_pf ADD type_fenetre_porte_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CE11E2A6B FOREIGN KEY (type_fenetre_porte_id) REFERENCES type_fenetre_porte (id)');
        $this->addSql('CREATE INDEX IDX_AD1816CE11E2A6B ON conf_pf (type_fenetre_porte_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CE11E2A6B');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291346F772E');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291E11E2A6B');
        $this->addSql('DROP TABLE systeme_type_fenetre_porte');
        $this->addSql('DROP TABLE type_fenetre_porte');
        $this->addSql('DROP INDEX IDX_AD1816CE11E2A6B ON conf_pf');
        $this->addSql('ALTER TABLE conf_pf DROP type_fenetre_porte_id');
    }
}
