<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne conf_volet_id (FK vers conf_volet) dans Lignes_de_commande_BLOC_N_R_iD4';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4`
            ADD COLUMN `conf_volet_id` INT DEFAULT NULL,
            ADD CONSTRAINT FK_lignes_commande_conf_volet
                FOREIGN KEY (`conf_volet_id`)
                REFERENCES `conf_volet` (`id`)
                ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_lignes_commande_conf_volet ON `Lignes_de_commande_BLOC_N_R_iD4` (`conf_volet_id`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4` DROP FOREIGN KEY FK_lignes_commande_conf_volet');
        $this->addSql('DROP INDEX IDX_lignes_commande_conf_volet ON `Lignes_de_commande_BLOC_N_R_iD4`');
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4` DROP COLUMN `conf_volet_id`');
    }
}
