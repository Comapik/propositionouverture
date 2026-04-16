<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convertit la colonne Extension_offre de conf_volet en booleen MySQL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_volet ADD `Extension_offre_tmp` BOOLEAN DEFAULT NULL');
        $this->addSql("UPDATE conf_volet SET `Extension_offre_tmp` = CASE WHEN `Extension_offre` IS NULL THEN NULL WHEN HEX(`Extension_offre`) = '01' THEN 1 ELSE 0 END");
        $this->addSql('ALTER TABLE conf_volet DROP COLUMN `Extension_offre`');
        $this->addSql('ALTER TABLE conf_volet CHANGE `Extension_offre_tmp` `Extension_offre` BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_volet ADD `Extension_offre_tmp` BINARY(1) DEFAULT NULL');
        $this->addSql("UPDATE conf_volet SET `Extension_offre_tmp` = CASE WHEN `Extension_offre` IS NULL THEN NULL WHEN `Extension_offre` = 1 THEN 0x01 ELSE 0x00 END");
        $this->addSql('ALTER TABLE conf_volet DROP COLUMN `Extension_offre`');
        $this->addSql('ALTER TABLE conf_volet CHANGE `Extension_offre_tmp` `Extension_offre` BINARY(1) DEFAULT NULL');
    }
}
