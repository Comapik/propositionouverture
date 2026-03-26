<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Nbre, Largeur_(LA), Hauteur_(HC), AT, B1, B2, S1, S2 columns to Lignes_de_commande_BLOC_N_R_iD4';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4`
            ADD COLUMN `Nbre` INT DEFAULT NULL,
            ADD COLUMN `Largeur_(LA)` INT DEFAULT NULL,
            ADD COLUMN `Hauteur_(HC)` INT DEFAULT NULL,
            ADD COLUMN `AT` INT DEFAULT NULL,
            ADD COLUMN `B1` INT DEFAULT NULL,
            ADD COLUMN `B2` INT DEFAULT NULL,
            ADD COLUMN `S1` INT DEFAULT NULL,
            ADD COLUMN `S2` INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Lignes_de_commande_BLOC_N_R_iD4`
            DROP COLUMN `S2`,
            DROP COLUMN `S1`,
            DROP COLUMN `B2`,
            DROP COLUMN `B1`,
            DROP COLUMN `AT`,
            DROP COLUMN `Hauteur_(HC)`,
            DROP COLUMN `Largeur_(LA)`,
            DROP COLUMN `Nbre`');
    }
}
