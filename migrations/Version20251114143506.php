<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114143506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        // 1. Ajouter la colonne comme nullable d'abord
        $this->addSql('ALTER TABLE type_fenetre_porte ADD ouverture_id INT DEFAULT NULL');
        
        // 2. Mettre à jour tous les enregistrements existants avec ouverture_id = 1
        $this->addSql('UPDATE type_fenetre_porte SET ouverture_id = 1 WHERE ouverture_id IS NULL');
        
        // 3. Rendre la colonne NOT NULL et ajouter la contrainte de clé étrangère
        $this->addSql('ALTER TABLE type_fenetre_porte MODIFY ouverture_id INT NOT NULL');
        $this->addSql('ALTER TABLE type_fenetre_porte ADD CONSTRAINT FK_8817D2F3F892AC88 FOREIGN KEY (ouverture_id) REFERENCES ouverture (id)');
        $this->addSql('CREATE INDEX IDX_8817D2F3F892AC88 ON type_fenetre_porte (ouverture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type_fenetre_porte DROP FOREIGN KEY FK_8817D2F3F892AC88');
        $this->addSql('DROP INDEX IDX_8817D2F3F892AC88 ON type_fenetre_porte');
        $this->addSql('ALTER TABLE type_fenetre_porte DROP ouverture_id');
    }
}
