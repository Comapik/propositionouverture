<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table Tablier
 */
final class Version20260324141000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creation de la table Tablier avec la colonne type et insertion de la valeur DP368';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Tablier (id INT AUTO_INCREMENT NOT NULL, `type` VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO Tablier (`type`) VALUES ('DP368')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS Tablier');
    }
}
