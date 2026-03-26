<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table type_coulisse';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE type_coulisse (
                id  INT AUTO_INCREMENT NOT NULL,
                nom VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE type_coulisse');
    }
}
