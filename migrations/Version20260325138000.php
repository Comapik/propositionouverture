<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325138000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprime la table Teintes_encadrement_volet (remplacee par teinte_encadrement)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS `Teintes_encadrement_volet`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `Teintes_encadrement_volet` (`id` INT AUTO_INCREMENT NOT NULL, `nom` VARCHAR(255) DEFAULT NULL, PRIMARY KEY(`id`)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }
}
