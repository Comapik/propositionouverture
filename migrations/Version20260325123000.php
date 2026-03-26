<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Repere, Angle, Elargisseur_coulisse, Câble_longueur_utile_5m, Panneau_PV_deporte columns to Lignes_de_commande_BLOC_N_R_iD4';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4`
            ADD COLUMN `Repere` VARCHAR(255) DEFAULT NULL,
            ADD COLUMN `Angle` INT DEFAULT NULL,
            ADD COLUMN `Elargisseur_coulisse` BINARY(1) DEFAULT NULL,
            ADD COLUMN `Câble_longueur_utile_5m` BINARY(1) DEFAULT NULL,
            ADD COLUMN `Panneau_PV_deporte` BINARY(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4`
            DROP COLUMN `Panneau_PV_deporte`,
            DROP COLUMN `Câble_longueur_utile_5m`,
            DROP COLUMN `Elargisseur_coulisse`,
            DROP COLUMN `Angle`,
            DROP COLUMN `Repere`');
    }
}
