<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325132000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insertion des types de coulisse AF, AL, A2, P3, U4, U6 dans type_coulisse';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO type_coulisse (nom) VALUES ('AF'), ('AL'), ('A2'), ('P3'), ('U4'), ('U6')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM type_coulisse WHERE nom IN ('AF', 'AL', 'A2', 'P3', 'U4', 'U6')");
    }
}
