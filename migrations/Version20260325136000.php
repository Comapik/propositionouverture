<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325136000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cree les tables teinte_encadrement_elargi et teinte_encadrement_specifique avec colonne nom';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `teinte_encadrement_elargi` (`id` INT AUTO_INCREMENT NOT NULL, `nom` VARCHAR(255) NOT NULL, PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `teinte_encadrement_specifique` (`id` INT AUTO_INCREMENT NOT NULL, `nom` VARCHAR(255) NOT NULL, PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `teinte_encadrement_specifique`');
        $this->addSql('DROP TABLE `teinte_encadrement_elargi`');
    }
}
