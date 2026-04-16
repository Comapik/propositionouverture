<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convertit les colonnes binaires de conf_volet en booléens';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_volet
            ADD face_exterieure_alu_tmp BOOLEAN DEFAULT NULL,
            ADD pht_n_tmp BOOLEAN DEFAULT NULL,
            ADD pht_r_tmp BOOLEAN DEFAULT NULL,
            ADD h4c_horloge_4_canaux_tmp BOOLEAN DEFAULT NULL,
            ADD dia_idiamant_tmp BOOLEAN DEFAULT NULL,
            ADD smu_support_mural_3_boutons_tmp BOOLEAN DEFAULT NULL,
            ADD inv_avec_inverseur_tmp BOOLEAN DEFAULT NULL');

        $this->addSql("UPDATE conf_volet SET
            face_exterieure_alu_tmp = CASE WHEN face_exterieure_alu IS NULL THEN NULL WHEN HEX(face_exterieure_alu) IN ('1', '01', '31') THEN 1 ELSE 0 END,
            pht_n_tmp = CASE WHEN pht_n IS NULL THEN NULL WHEN HEX(pht_n) IN ('1', '01', '31') THEN 1 ELSE 0 END,
            pht_r_tmp = CASE WHEN pht_r IS NULL THEN NULL WHEN HEX(pht_r) IN ('1', '01', '31') THEN 1 ELSE 0 END,
            h4c_horloge_4_canaux_tmp = CASE WHEN h4c_horloge_4_canaux IS NULL THEN NULL WHEN HEX(h4c_horloge_4_canaux) IN ('1', '01', '31') THEN 1 ELSE 0 END,
            dia_idiamant_tmp = CASE WHEN dia_idiamant IS NULL THEN NULL WHEN HEX(dia_idiamant) IN ('1', '01', '31') THEN 1 ELSE 0 END,
            smu_support_mural_3_boutons_tmp = CASE WHEN smu_support_mural_3_boutons IS NULL THEN NULL WHEN HEX(smu_support_mural_3_boutons) IN ('1', '01', '31') THEN 1 ELSE 0 END,
            inv_avec_inverseur_tmp = CASE WHEN inv_avec_inverseur IS NULL THEN NULL WHEN HEX(inv_avec_inverseur) IN ('1', '01', '31') THEN 1 ELSE 0 END");

        $this->addSql('ALTER TABLE conf_volet
            DROP COLUMN face_exterieure_alu,
            DROP COLUMN pht_n,
            DROP COLUMN pht_r,
            DROP COLUMN h4c_horloge_4_canaux,
            DROP COLUMN dia_idiamant,
            DROP COLUMN smu_support_mural_3_boutons,
            DROP COLUMN inv_avec_inverseur');

        $this->addSql('ALTER TABLE conf_volet
            CHANGE face_exterieure_alu_tmp face_exterieure_alu BOOLEAN DEFAULT NULL,
            CHANGE pht_n_tmp pht_n BOOLEAN DEFAULT NULL,
            CHANGE pht_r_tmp pht_r BOOLEAN DEFAULT NULL,
            CHANGE h4c_horloge_4_canaux_tmp h4c_horloge_4_canaux BOOLEAN DEFAULT NULL,
            CHANGE dia_idiamant_tmp dia_idiamant BOOLEAN DEFAULT NULL,
            CHANGE smu_support_mural_3_boutons_tmp smu_support_mural_3_boutons BOOLEAN DEFAULT NULL,
            CHANGE inv_avec_inverseur_tmp inv_avec_inverseur BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_volet
            MODIFY face_exterieure_alu BINARY(1) DEFAULT NULL,
            MODIFY pht_n BINARY(1) DEFAULT NULL,
            MODIFY pht_r BINARY(1) DEFAULT NULL,
            MODIFY h4c_horloge_4_canaux BINARY(1) DEFAULT NULL,
            MODIFY dia_idiamant BINARY(1) DEFAULT NULL,
            MODIFY smu_support_mural_3_boutons BINARY(1) DEFAULT NULL,
            MODIFY inv_avec_inverseur BINARY(1) DEFAULT NULL');
    }
}
