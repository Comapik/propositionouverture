<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename table Teintes_tablier_volet to Teinte_global';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE Teintes_tablier_volet TO Teinte_global');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('RENAME TABLE Teinte_global TO Teintes_tablier_volet');
    }
}
