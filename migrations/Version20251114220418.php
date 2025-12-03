<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114220418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_fenetre_porte_compatibilite (id INT AUTO_INCREMENT NOT NULL, type_fenetre_porte_id INT NOT NULL, ouverture_id INT NOT NULL, systeme_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A1FE3AFDE11E2A6B (type_fenetre_porte_id), INDEX IDX_A1FE3AFDF892AC88 (ouverture_id), INDEX IDX_A1FE3AFD346F772E (systeme_id), UNIQUE INDEX type_ouverture_systeme_unique (type_fenetre_porte_id, ouverture_id, systeme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite ADD CONSTRAINT FK_A1FE3AFDE11E2A6B FOREIGN KEY (type_fenetre_porte_id) REFERENCES type_fenetre_porte (id)');
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite ADD CONSTRAINT FK_A1FE3AFDF892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id)');
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite ADD CONSTRAINT FK_A1FE3AFD346F772E FOREIGN KEY (systeme_id) REFERENCES systeme (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite DROP FOREIGN KEY FK_A1FE3AFDE11E2A6B');
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite DROP FOREIGN KEY FK_A1FE3AFDF892AC88');
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite DROP FOREIGN KEY FK_A1FE3AFD346F772E');
        $this->addSql('DROP TABLE type_fenetre_porte_compatibilite');
    }
}
