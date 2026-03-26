<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325139000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute une cle etrangere vers teinte_global dans teinte_encadrement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `teinte_encadrement` ADD COLUMN `teinte_global_id` INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `teinte_encadrement` ADD CONSTRAINT FK_teinte_encadrement_teinte_global FOREIGN KEY (`teinte_global_id`) REFERENCES `Teinte_global` (`id`) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_teinte_encadrement_teinte_global ON `teinte_encadrement` (`teinte_global_id`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `teinte_encadrement` DROP FOREIGN KEY FK_teinte_encadrement_teinte_global');
        $this->addSql('DROP INDEX IDX_teinte_encadrement_teinte_global ON `teinte_encadrement`');
        $this->addSql('ALTER TABLE `teinte_encadrement` DROP COLUMN `teinte_global_id`');
    }
}
