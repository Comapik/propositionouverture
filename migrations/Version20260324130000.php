<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour compléter la table Options_Moteur_Radio_Bubendorff
 */
final class Version20260324130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des colonnes CMG_groupe_CLIMAT+, H4C_Horloge_4_canaux, DIA_iDiamant, SMU_Support_mural_émetteur_3_boutons dans Options_Moteur_Radio_Bubendorff';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Options_Moteur_Radio_Bubendorff
            ADD `CMG_groupe_CLIMAT+` INT DEFAULT NULL,
            ADD `H4C_Horloge_4_canaux` BINARY(1) DEFAULT NULL,
            ADD `DIA_iDiamant` BINARY(1) DEFAULT NULL,
            ADD `SMU_Support_mural_émetteur_3_boutons` BINARY(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Options_Moteur_Radio_Bubendorff
            DROP `CMG_groupe_CLIMAT+`,
            DROP `H4C_Horloge_4_canaux`,
            DROP `DIA_iDiamant`,
            DROP `SMU_Support_mural_émetteur_3_boutons`');
    }
}
