<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop teintes_tablier_volet_id column from Conf_volet_BLOC_N_R_iD4';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Conf_volet_BLOC_N_R_iD4 DROP INDEX IDX_17144B1B50978115');
        $this->addSql('ALTER TABLE Conf_volet_BLOC_N_R_iD4 DROP COLUMN teintes_tablier_volet_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Conf_volet_BLOC_N_R_iD4 ADD teintes_tablier_volet_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_17144B1B50978115 ON Conf_volet_BLOC_N_R_iD4 (teintes_tablier_volet_id)');
    }
}
