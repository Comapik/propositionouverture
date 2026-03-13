<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Insert default positions
 */
final class Version20260207153812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default positions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO position (position) VALUES ('Gauche'), ('Centre'), ('Droit')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM position WHERE position IN ('Gauche', 'Centre', 'Droit')");
    }
}
