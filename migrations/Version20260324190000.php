<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add conf_volet_id FK to conf_teinte_tablier';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE conf_teinte_tablier
                ADD COLUMN conf_volet_id INT DEFAULT NULL,
                ADD CONSTRAINT FK_conf_teinte_tablier_conf_volet
                    FOREIGN KEY (conf_volet_id)
                    REFERENCES conf_volet (id)
                    ON DELETE CASCADE
        ');
        $this->addSql('CREATE INDEX IDX_conf_teinte_tablier_conf_volet ON conf_teinte_tablier (conf_volet_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_teinte_tablier DROP FOREIGN KEY FK_conf_teinte_tablier_conf_volet');
        $this->addSql('DROP INDEX IDX_conf_teinte_tablier_conf_volet ON conf_teinte_tablier');
        $this->addSql('ALTER TABLE conf_teinte_tablier DROP COLUMN conf_volet_id');
    }
}
