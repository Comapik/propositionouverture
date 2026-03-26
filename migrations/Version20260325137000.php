<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325137000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute des cles etrangeres dans teinte_encadrement vers teinte_encadrement_elargi et teinte_encadrement_specifique';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `teinte_encadrement` ADD COLUMN `teinte_encadrement_elargi_id` INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `teinte_encadrement` ADD COLUMN `teinte_encadrement_specifique_id` INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `teinte_encadrement` ADD CONSTRAINT FK_teinte_encadrement_elargi FOREIGN KEY (`teinte_encadrement_elargi_id`) REFERENCES `teinte_encadrement_elargi` (`id`) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `teinte_encadrement` ADD CONSTRAINT FK_teinte_encadrement_specifique FOREIGN KEY (`teinte_encadrement_specifique_id`) REFERENCES `teinte_encadrement_specifique` (`id`) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_teinte_encadrement_elargi ON `teinte_encadrement` (`teinte_encadrement_elargi_id`)');
        $this->addSql('CREATE INDEX IDX_teinte_encadrement_specifique ON `teinte_encadrement` (`teinte_encadrement_specifique_id`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `teinte_encadrement` DROP FOREIGN KEY FK_teinte_encadrement_elargi');
        $this->addSql('ALTER TABLE `teinte_encadrement` DROP FOREIGN KEY FK_teinte_encadrement_specifique');
        $this->addSql('DROP INDEX IDX_teinte_encadrement_elargi ON `teinte_encadrement`');
        $this->addSql('DROP INDEX IDX_teinte_encadrement_specifique ON `teinte_encadrement`');
        $this->addSql('ALTER TABLE `teinte_encadrement` DROP COLUMN `teinte_encadrement_elargi_id`');
        $this->addSql('ALTER TABLE `teinte_encadrement` DROP COLUMN `teinte_encadrement_specifique_id`');
    }
}
