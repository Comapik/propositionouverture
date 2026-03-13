<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260218101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Conf_volet_BLOC_N_R_iD4 with foreign keys to volet reference tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS `Conf_volet_BLOC_N_R_iD4` (id INT AUTO_INCREMENT NOT NULL, caisson_pvc_id INT DEFAULT NULL, tablier_id INT DEFAULT NULL, teintes_tablier_volet_id INT DEFAULT NULL, teintes_encadrement_volet_id INT DEFAULT NULL, teintes_id INT DEFAULT NULL, specificites_caisson_id INT DEFAULT NULL, options_moteur_radio_bubendorff_id INT DEFAULT NULL, option_moteur_filaire_bubendorff_id INT DEFAULT NULL, option_pack_sav_id INT DEFAULT NULL, lignes_de_commande_bloc_n_r_id4_id INT DEFAULT NULL, INDEX IDX_35BA60837795A036 (caisson_pvc_id), INDEX IDX_35BA6083E560E380 (tablier_id), INDEX IDX_35BA608339DAB0D7 (teintes_tablier_volet_id), INDEX IDX_35BA6083B52AB24 (teintes_encadrement_volet_id), INDEX IDX_35BA60831D981EEA (teintes_id), INDEX IDX_35BA6083B063730A (specificites_caisson_id), INDEX IDX_35BA6083DAB53DEB (options_moteur_radio_bubendorff_id), INDEX IDX_35BA6083569B6CE (option_moteur_filaire_bubendorff_id), INDEX IDX_35BA60837665AE7A (option_pack_sav_id), INDEX IDX_35BA608327DB474B (lignes_de_commande_bloc_n_r_id4_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA60837795A036 FOREIGN KEY (caisson_pvc_id) REFERENCES `Caisson_PVC` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA6083E560E380 FOREIGN KEY (tablier_id) REFERENCES `Tablier` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA608339DAB0D7 FOREIGN KEY (teintes_tablier_volet_id) REFERENCES `Teintes_tablier_volet` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA6083B52AB24 FOREIGN KEY (teintes_encadrement_volet_id) REFERENCES `Teintes_encadrement_volet` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA60831D981EEA FOREIGN KEY (teintes_id) REFERENCES `Teintes` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA6083B063730A FOREIGN KEY (specificites_caisson_id) REFERENCES `Spécificités_caisson` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA6083DAB53DEB FOREIGN KEY (options_moteur_radio_bubendorff_id) REFERENCES `Options_Moteur_Radio_Bubendorff` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA6083569B6CE FOREIGN KEY (option_moteur_filaire_bubendorff_id) REFERENCES `Option Moteur-Filaire_Bubendorff` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA60837665AE7A FOREIGN KEY (option_pack_sav_id) REFERENCES `Option_pack_SAV` (id)');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` ADD CONSTRAINT FK_35BA608327DB474B FOREIGN KEY (lignes_de_commande_bloc_n_r_id4_id) REFERENCES `Lignes_de_commande_BLOC_N_R_iD4` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA60837795A036');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA6083E560E380');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA608339DAB0D7');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA6083B52AB24');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA60831D981EEA');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA6083B063730A');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA6083DAB53DEB');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA6083569B6CE');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA60837665AE7A');
        $this->addSql('ALTER TABLE `Conf_volet_BLOC_N_R_iD4` DROP FOREIGN KEY FK_35BA608327DB474B');
        $this->addSql('DROP TABLE IF EXISTS `Conf_volet_BLOC_N_R_iD4`');
    }
}
