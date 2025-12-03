<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114190136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_fenetre_porte_ouverture (type_fenetre_porte_id INT NOT NULL, ouverture_id INT NOT NULL, INDEX IDX_2D77D34E11E2A6B (type_fenetre_porte_id), INDEX IDX_2D77D34F892AC88 (ouverture_id), PRIMARY KEY(type_fenetre_porte_id, ouverture_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE type_fenetre_porte_ouverture ADD CONSTRAINT FK_2D77D34E11E2A6B FOREIGN KEY (type_fenetre_porte_id) REFERENCES type_fenetre_porte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_fenetre_porte_ouverture ADD CONSTRAINT FK_2D77D34F892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_fenetre_porte DROP FOREIGN KEY FK_8817D2F3F892AC88');
        $this->addSql('DROP INDEX IDX_8817D2F3F892AC88 ON type_fenetre_porte');
        $this->addSql('ALTER TABLE type_fenetre_porte DROP ouverture_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type_fenetre_porte_ouverture DROP FOREIGN KEY FK_2D77D34E11E2A6B');
        $this->addSql('ALTER TABLE type_fenetre_porte_ouverture DROP FOREIGN KEY FK_2D77D34F892AC88');
        $this->addSql('DROP TABLE type_fenetre_porte_ouverture');
        $this->addSql('ALTER TABLE type_fenetre_porte ADD ouverture_id INT NOT NULL');
        $this->addSql('ALTER TABLE type_fenetre_porte ADD CONSTRAINT FK_8817D2F3F892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8817D2F3F892AC88 ON type_fenetre_porte (ouverture_id)');
    }
}
