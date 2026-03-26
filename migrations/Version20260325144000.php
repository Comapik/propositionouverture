<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325144000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les champs de configuration volet dans conf_volet et leurs FKs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `conf_volet`
            ADD COLUMN `caisson_pvc_id` INT DEFAULT NULL,
            ADD COLUMN `tablier_id` INT DEFAULT NULL,
            ADD COLUMN `teinte_encadrement_elargi_id` INT DEFAULT NULL,
            ADD COLUMN `teinte_encadrement_specifique_id` INT DEFAULT NULL,
            ADD COLUMN `nuancier_standard_encadrement_id` INT DEFAULT NULL,
            ADD COLUMN `option_pack_sav_id` INT DEFAULT NULL,
            ADD COLUMN `face_exterieure_alu` BINARY(1) DEFAULT NULL,
            ADD COLUMN `option_autre_teinte` VARCHAR(255) DEFAULT NULL,
            ADD COLUMN `pht_n` BINARY(1) DEFAULT NULL,
            ADD COLUMN `pht_r` BINARY(1) DEFAULT NULL,
            ADD COLUMN `cmg_groupe_climat_plus` INT DEFAULT NULL,
            ADD COLUMN `h4c_horloge_4_canaux` BINARY(1) DEFAULT NULL,
            ADD COLUMN `dia_idiamant` BINARY(1) DEFAULT NULL,
            ADD COLUMN `smu_support_mural_3_boutons` BINARY(1) DEFAULT NULL,
            ADD COLUMN `inv_avec_inverseur` BINARY(1) DEFAULT NULL');

        $this->addSql('CREATE INDEX IDX_conf_volet_caisson_pvc ON `conf_volet` (`caisson_pvc_id`)');
        $this->addSql('CREATE INDEX IDX_conf_volet_tablier ON `conf_volet` (`tablier_id`)');
        $this->addSql('CREATE INDEX IDX_conf_volet_teinte_elargi ON `conf_volet` (`teinte_encadrement_elargi_id`)');
        $this->addSql('CREATE INDEX IDX_conf_volet_teinte_specifique ON `conf_volet` (`teinte_encadrement_specifique_id`)');
        $this->addSql('CREATE INDEX IDX_conf_volet_nuancier_encadrement ON `conf_volet` (`nuancier_standard_encadrement_id`)');
        $this->addSql('CREATE INDEX IDX_conf_volet_pack_sav ON `conf_volet` (`option_pack_sav_id`)');

        $this->addSql('ALTER TABLE `conf_volet` ADD CONSTRAINT FK_conf_volet_caisson_pvc FOREIGN KEY (`caisson_pvc_id`) REFERENCES `Caisson_PVC` (`id`) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `conf_volet` ADD CONSTRAINT FK_conf_volet_tablier FOREIGN KEY (`tablier_id`) REFERENCES `Tablier` (`id`) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `conf_volet` ADD CONSTRAINT FK_conf_volet_teinte_elargi FOREIGN KEY (`teinte_encadrement_elargi_id`) REFERENCES `teinte_encadrement_elargi` (`id`) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `conf_volet` ADD CONSTRAINT FK_conf_volet_teinte_specifique FOREIGN KEY (`teinte_encadrement_specifique_id`) REFERENCES `teinte_encadrement_specifique` (`id`) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `conf_volet` ADD CONSTRAINT FK_conf_volet_nuancier_encadrement FOREIGN KEY (`nuancier_standard_encadrement_id`) REFERENCES `nuancier_standard` (`id`) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `conf_volet` ADD CONSTRAINT FK_conf_volet_pack_sav FOREIGN KEY (`option_pack_sav_id`) REFERENCES `Option_pack_SAV` (`id`) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `conf_volet` DROP FOREIGN KEY FK_conf_volet_caisson_pvc');
        $this->addSql('ALTER TABLE `conf_volet` DROP FOREIGN KEY FK_conf_volet_tablier');
        $this->addSql('ALTER TABLE `conf_volet` DROP FOREIGN KEY FK_conf_volet_teinte_elargi');
        $this->addSql('ALTER TABLE `conf_volet` DROP FOREIGN KEY FK_conf_volet_teinte_specifique');
        $this->addSql('ALTER TABLE `conf_volet` DROP FOREIGN KEY FK_conf_volet_nuancier_encadrement');
        $this->addSql('ALTER TABLE `conf_volet` DROP FOREIGN KEY FK_conf_volet_pack_sav');

        $this->addSql('DROP INDEX IDX_conf_volet_caisson_pvc ON `conf_volet`');
        $this->addSql('DROP INDEX IDX_conf_volet_tablier ON `conf_volet`');
        $this->addSql('DROP INDEX IDX_conf_volet_teinte_elargi ON `conf_volet`');
        $this->addSql('DROP INDEX IDX_conf_volet_teinte_specifique ON `conf_volet`');
        $this->addSql('DROP INDEX IDX_conf_volet_nuancier_encadrement ON `conf_volet`');
        $this->addSql('DROP INDEX IDX_conf_volet_pack_sav ON `conf_volet`');

        $this->addSql('ALTER TABLE `conf_volet`
            DROP COLUMN `caisson_pvc_id`,
            DROP COLUMN `tablier_id`,
            DROP COLUMN `teinte_encadrement_elargi_id`,
            DROP COLUMN `teinte_encadrement_specifique_id`,
            DROP COLUMN `nuancier_standard_encadrement_id`,
            DROP COLUMN `option_pack_sav_id`,
            DROP COLUMN `face_exterieure_alu`,
            DROP COLUMN `option_autre_teinte`,
            DROP COLUMN `pht_n`,
            DROP COLUMN `pht_r`,
            DROP COLUMN `cmg_groupe_climat_plus`,
            DROP COLUMN `h4c_horloge_4_canaux`,
            DROP COLUMN `dia_idiamant`,
            DROP COLUMN `smu_support_mural_3_boutons`,
            DROP COLUMN `inv_avec_inverseur`');
    }
}
