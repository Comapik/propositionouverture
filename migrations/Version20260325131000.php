<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325131000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne type_coulisse_id (FK vers type_coulisse) dans Lignes_de_commande_BLOC_N_R_iD4';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4`
            ADD COLUMN `type_coulisse_id` INT DEFAULT NULL,
            ADD CONSTRAINT FK_lignes_commande_type_coulisse
                FOREIGN KEY (`type_coulisse_id`)
                REFERENCES `type_coulisse` (`id`)
                ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_lignes_commande_type_coulisse ON `Lignes_de_commande_BLOC_N_R_iD4` (`type_coulisse_id`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4` DROP FOREIGN KEY FK_lignes_commande_type_coulisse');
        $this->addSql('DROP INDEX IDX_lignes_commande_type_coulisse ON `Lignes_de_commande_BLOC_N_R_iD4`');
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4` DROP COLUMN `type_coulisse_id`');
    }
}
