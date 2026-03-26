<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create table conf_teinte_tablier with FK to Teinte_global and Tablier_faible_émissivite binary column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE conf_teinte_tablier (
                id INT AUTO_INCREMENT NOT NULL,
                teinte_global_id INT DEFAULT NULL,
                Tablier_faible_emissivite BINARY(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                INDEX IDX_teinte_global (teinte_global_id),
                CONSTRAINT FK_conf_teinte_tablier_teinte_global
                    FOREIGN KEY (teinte_global_id)
                    REFERENCES Teinte_global (id)
                    ON DELETE SET NULL
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE conf_teinte_tablier');
    }
}
