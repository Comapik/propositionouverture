<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260327093530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Cette migration auto-générée supprimait des structures métier encore
        // présentes dans les environnements historiques. Pour éviter les erreurs
        // de DROP FOREIGN KEY en production, elle est désormais volontairement neutre.
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Option Moteur-Filaire_Bubendorff (id INT AUTO_INCREMENT NOT NULL, INV_avec_inverseur BINARY(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE Option_pack_SAV (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE Options_Moteur_Radio_Bubendorff (id INT AUTO_INCREMENT NOT NULL, CMG_groupe_CLIMAT+ INT DEFAULT NULL, H4C_Horloge_4_canaux BINARY(1) DEFAULT NULL, DIA_iDiamant BINARY(1) DEFAULT NULL, SMU_Support_mural_émetteur_3_boutons BINARY(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE Specificites_caisson (id INT AUTO_INCREMENT NOT NULL, Face_exterieure_alu BINARY(1) DEFAULT NULL, Option_autre_teinte VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PHT_N BINARY(1) DEFAULT NULL, PHT_R BINARY(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE Tablier (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE teinte_encadrement (id INT AUTO_INCREMENT NOT NULL, teinte_encadrement_elargi_id INT DEFAULT NULL, teinte_encadrement_specifique_id INT DEFAULT NULL, nuancier_standard_id INT DEFAULT NULL, INDEX IDX_teinte_encadrement_nuancier_standard (nuancier_standard_id), INDEX IDX_teinte_encadrement_specifique (teinte_encadrement_specifique_id), INDEX IDX_teinte_encadrement_elargi (teinte_encadrement_elargi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE teinte_encadrement_elargi (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE teinte_encadrement_specifique (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE type_coulisse (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE Type_DEV (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CF347EFB');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CBCF5E72D');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C365BF48');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CF892AC88');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C670C757F');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C346F772E');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CE11E2A6B');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CC6B2988F');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CA0555154');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CE31C125D');
        $this->addSql('ALTER TABLE conf_pf ADD conf_aeration_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_AD1816C47FCF597 ON conf_pf (conf_aeration_id)');
        $this->addSql('ALTER TABLE conf_volet ADD Extension_offre BINARY(1) DEFAULT NULL, ADD caisson_pvc_id INT DEFAULT NULL, ADD tablier_id INT DEFAULT NULL, ADD teinte_encadrement_elargi_id INT DEFAULT NULL, ADD teinte_encadrement_specifique_id INT DEFAULT NULL, ADD nuancier_standard_encadrement_id INT DEFAULT NULL, ADD option_pack_sav_id INT DEFAULT NULL, ADD face_exterieure_alu BINARY(1) DEFAULT NULL, ADD option_autre_teinte VARCHAR(255) DEFAULT NULL, ADD pht_n BINARY(1) DEFAULT NULL, ADD pht_r BINARY(1) DEFAULT NULL, ADD cmg_groupe_climat_plus INT DEFAULT NULL, ADD h4c_horloge_4_canaux BINARY(1) DEFAULT NULL, ADD dia_idiamant BINARY(1) DEFAULT NULL, ADD smu_support_mural_3_boutons BINARY(1) DEFAULT NULL, ADD inv_avec_inverseur BINARY(1) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_conf_volet_caisson_pvc ON conf_volet (caisson_pvc_id)');
        $this->addSql('CREATE INDEX IDX_conf_volet_tablier ON conf_volet (tablier_id)');
        $this->addSql('CREATE INDEX IDX_conf_volet_teinte_elargi ON conf_volet (teinte_encadrement_elargi_id)');
        $this->addSql('CREATE INDEX IDX_conf_volet_teinte_specifique ON conf_volet (teinte_encadrement_specifique_id)');
        $this->addSql('CREATE INDEX IDX_conf_volet_nuancier_encadrement ON conf_volet (nuancier_standard_encadrement_id)');
        $this->addSql('CREATE INDEX IDX_conf_volet_pack_sav ON conf_volet (option_pack_sav_id)');
        $this->addSql('ALTER TABLE fournisseurs DROP FOREIGN KEY FK_D3EF0041F347EFB');
        $this->addSql('ALTER TABLE ouverture DROP FOREIGN KEY FK_43461EAB365BF48');
        $this->addSql('ALTER TABLE projet_pdf DROP FOREIGN KEY FK_DB2F628DF4C0BC36');
        $this->addSql('ALTER TABLE projet_pdf DROP FOREIGN KEY FK_DB2F628D62B81754');
        $this->addSql('ALTER TABLE projets DROP FOREIGN KEY FK_B454C1DB19EB6921');
        $this->addSql('ALTER TABLE sous_categories DROP FOREIGN KEY FK_DC8B1382F347EFB');
        $this->addSql('ALTER TABLE sous_categories DROP FOREIGN KEY FK_DC8B1382BCF5E72D');
        $this->addSql('ALTER TABLE systeme DROP FOREIGN KEY FK_95796DE3670C757F');
        $this->addSql('ALTER TABLE systeme_ouverture DROP FOREIGN KEY FK_71CCBC17346F772E');
        $this->addSql('ALTER TABLE systeme_ouverture DROP FOREIGN KEY FK_71CCBC17F892AC88');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291346F772E');
        $this->addSql('ALTER TABLE systeme_type_fenetre_porte DROP FOREIGN KEY FK_ABFB1291E11E2A6B');
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite DROP FOREIGN KEY FK_A1FE3AFDE11E2A6B');
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite DROP FOREIGN KEY FK_A1FE3AFDF892AC88');
        $this->addSql('ALTER TABLE type_fenetre_porte_compatibilite DROP FOREIGN KEY FK_A1FE3AFD346F772E');
        $this->addSql('ALTER TABLE type_fenetre_porte_ouverture DROP FOREIGN KEY FK_2D77D34E11E2A6B');
        $this->addSql('ALTER TABLE type_fenetre_porte_ouverture DROP FOREIGN KEY FK_2D77D34F892AC88');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
