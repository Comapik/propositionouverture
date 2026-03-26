<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325142000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomme Teinte_global en nuancier_standard et ses references';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `conf_teinte_tablier` DROP FOREIGN KEY `FK_conf_teinte_tablier_teinte_global`');
        $this->addSql('DROP INDEX `IDX_teinte_global` ON `conf_teinte_tablier`');
        $this->addSql('ALTER TABLE `conf_teinte_tablier` CHANGE `teinte_global_id` `nuancier_standard_id` INT DEFAULT NULL');
        $this->addSql('CREATE INDEX `IDX_nuancier_standard` ON `conf_teinte_tablier` (`nuancier_standard_id`)');

        $this->addSql('ALTER TABLE `teinte_encadrement` DROP FOREIGN KEY `FK_teinte_encadrement_teinte_global`');
        $this->addSql('DROP INDEX `IDX_teinte_encadrement_teinte_global` ON `teinte_encadrement`');
        $this->addSql('ALTER TABLE `teinte_encadrement` CHANGE `teinte_global_id` `nuancier_standard_id` INT DEFAULT NULL');
        $this->addSql('CREATE INDEX `IDX_teinte_encadrement_nuancier_standard` ON `teinte_encadrement` (`nuancier_standard_id`)');

        $this->addSql('RENAME TABLE `Teinte_global` TO `nuancier_standard`');

        $this->addSql('ALTER TABLE `conf_teinte_tablier` ADD CONSTRAINT `FK_conf_teinte_tablier_nuancier_standard` FOREIGN KEY (`nuancier_standard_id`) REFERENCES `nuancier_standard` (`id`) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `teinte_encadrement` ADD CONSTRAINT `FK_teinte_encadrement_nuancier_standard` FOREIGN KEY (`nuancier_standard_id`) REFERENCES `nuancier_standard` (`id`) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `teinte_encadrement` DROP FOREIGN KEY `FK_teinte_encadrement_nuancier_standard`');
        $this->addSql('DROP INDEX `IDX_teinte_encadrement_nuancier_standard` ON `teinte_encadrement`');
        $this->addSql('ALTER TABLE `teinte_encadrement` CHANGE `nuancier_standard_id` `teinte_global_id` INT DEFAULT NULL');
        $this->addSql('CREATE INDEX `IDX_teinte_encadrement_teinte_global` ON `teinte_encadrement` (`teinte_global_id`)');

        $this->addSql('ALTER TABLE `conf_teinte_tablier` DROP FOREIGN KEY `FK_conf_teinte_tablier_nuancier_standard`');
        $this->addSql('DROP INDEX `IDX_nuancier_standard` ON `conf_teinte_tablier`');
        $this->addSql('ALTER TABLE `conf_teinte_tablier` CHANGE `nuancier_standard_id` `teinte_global_id` INT DEFAULT NULL');
        $this->addSql('CREATE INDEX `IDX_teinte_global` ON `conf_teinte_tablier` (`teinte_global_id`)');

        $this->addSql('RENAME TABLE `nuancier_standard` TO `Teinte_global`');

        $this->addSql('ALTER TABLE `conf_teinte_tablier` ADD CONSTRAINT `FK_conf_teinte_tablier_teinte_global` FOREIGN KEY (`teinte_global_id`) REFERENCES `Teinte_global` (`id`) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `teinte_encadrement` ADD CONSTRAINT `FK_teinte_encadrement_teinte_global` FOREIGN KEY (`teinte_global_id`) REFERENCES `Teinte_global` (`id`) ON DELETE SET NULL');
    }
}
