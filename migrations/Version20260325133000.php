<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325133000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne Extension_offre (BINARY) dans conf_volet';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `conf_volet` ADD COLUMN `Extension_offre` BINARY(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `conf_volet` DROP COLUMN `Extension_offre`');
    }
}
