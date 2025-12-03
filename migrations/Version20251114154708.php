<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114154708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291346F772E');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291E11E2A6B');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD id INT AUTO_INCREMENT NOT NULL, ADD ouverture_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD CONSTRAINT FK_ABFB1291F892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id)');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD CONSTRAINT FK_ABFB1291346F772E FOREIGN KEY (systeme_id) REFERENCES systeme (id)');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD CONSTRAINT FK_ABFB1291E11E2A6B FOREIGN KEY (type_fenetre_porte_id) REFERENCES type_fenetre_porte (id)');
        $this->addSql('CREATE INDEX IDX_ABFB1291F892AC88 ON systeme_type_fenetre_porte (ouverture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291F892AC88');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291346F772E');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291E11E2A6B');
        $this->addSql('DROP INDEX IDX_ABFB1291F892AC88 ON systeme_type_fenetre_porte');
        $this->addSql('DROP INDEX `PRIMARY` ON systeme_type_fenetre_porte');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP id, DROP ouverture_id');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD CONSTRAINT FK_ABFB1291346F772E FOREIGN KEY (systeme_id) REFERENCES systeme (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD CONSTRAINT FK_ABFB1291E11E2A6B FOREIGN KEY (type_fenetre_porte_id) REFERENCES type_fenetre_porte (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte ADD PRIMARY KEY (systeme_id, type_fenetre_porte_id)');
    }
}
