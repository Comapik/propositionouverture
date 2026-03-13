<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add aeration_id foreign key to conf_pf table
 */
final class Version20260207131001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add aeration_id foreign key to conf_pf table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_pf ADD aeration_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CCC7324F2E FOREIGN KEY (aeration_id) REFERENCES aeration (id)');
        $this->addSql('CREATE INDEX IDX_AD1816CCC7324F2E ON conf_pf (aeration_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CCC7324F2E');
        $this->addSql('DROP INDEX IDX_AD1816CCC7324F2E ON conf_pf');
        $this->addSql('ALTER TABLE conf_pf DROP aeration_id');
    }
}
